<?php
/**
 * Test case for Springy\Mail\Mailer class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Mail\Mailer;

class MailerTest extends TestCase
{
    public function testSendsAMessageToNobody()
    {
        $mailer = new Mailer();
        $mailer->setFrom('from.email@springy-framework.com.br', 'From Person');
        $mailer->addBcc('bcc.email@springy-framework.com.br', 'Bcc Person');
        $mailer->addCc('cc.email@springy-framework.com.br', 'Cc Person');
        $mailer->addTo('to.email@springy-framework.com.br', 'To Person');
        $mailer->setSubject('Subject of the message');
        $mailer->addHeader('Priority', 'normal');
        $mailer->setBody('<strong>Hello!</strong>');
        $mailer->setAlternativeBody('Hello!');
        $this->assertFalse($mailer->send());
        $this->assertStringStartsWith('2: SMTP connect() failed', $mailer->getLastError());
    }
}
