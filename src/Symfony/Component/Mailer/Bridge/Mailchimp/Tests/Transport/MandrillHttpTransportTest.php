<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Bridge\Mailchimp\Tests\Transport;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Bridge\Mailchimp\Transport\MandrillHttpTransport;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mime\Email;

class MandrillHttpTransportTest extends TestCase
{
    /**
     * @dataProvider getTransportData
     */
    public function testToString(MandrillHttpTransport $transport, string $expected)
    {
        $this->assertSame($expected, (string) $transport);
    }

    public function getTransportData()
    {
        return [
            [
                new MandrillHttpTransport('KEY'),
                'mandrill+https://mandrillapp.com',
            ],
            [
                (new MandrillHttpTransport('KEY'))->setHost('example.com'),
                'mandrill+https://example.com',
            ],
            [
                (new MandrillHttpTransport('KEY'))->setHost('example.com')->setPort(99),
                'mandrill+https://example.com:99',
            ],
        ];
    }

    public function testTagAndMetadataHeaders()
    {
        $email = new Email();
        $email->getHeaders()->addTextHeader('foo', 'bar');
        $email->getHeaders()->add(new TagHeader('password-reset,user'));
        $email->getHeaders()->add(new MetadataHeader('Color', 'blue'));
        $email->getHeaders()->add(new MetadataHeader('Client-ID', '12345'));

        $transport = new MandrillHttpTransport('key');
        $method = new \ReflectionMethod(MandrillHttpTransport::class, 'addMandrillHeaders');
        $method->setAccessible(true);
        $method->invoke($transport, $email);

        $this->assertCount(3, $email->getHeaders()->toArray());
        $this->assertSame('foo: bar', $email->getHeaders()->get('FOO')->toString());
        $this->assertSame('X-MC-Tags: password-reset,user', $email->getHeaders()->get('X-MC-Tags')->toString());
        $this->assertSame('X-MC-Metadata: '.json_encode(['Color' => 'blue', 'Client-ID' => '12345']), $email->getHeaders()->get('X-MC-Metadata')->toString());
    }
}