<?php
/**
 * Driver class for use with Swift Mailer v6 class.
 *
 * @copyright 2016 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @see       https://swiftmailer.symfony.com/
 *
 * @version   3.0.0
 *
 * The Swift Mailer library is not a dependency of this project.
 * This driver is implemented only as a facility to the developers.
 *
 * You must add the Swift Mailer library as dependency of your project
 * by adding it in your project's composer.json file like this:
 *
 * {
 *   "require": {
 *     "swiftmailer/swiftmailer": "~6.0"
 *   }
 * }
 *
 * Or install it yourself with the following command line:
 *
 * $ composer require "swiftmailer/swiftmailer:~6.0"
 */

namespace Springy\Mail\Drivers;

use Swift_Mailer;
use Swift_MailTransport;
use Swift_Message;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Springy\Exceptions\SpringyException;

class SwiftMailer implements MailDriverInterface
{
    /** @var string last send error message */
    protected $lastError;
    /** @var Swift_Message the Swift Mailer object */
    protected $mailObj;
    /** @var Object the Swift Mailer protocol transport object */
    protected $transport;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!isset($config['protocol'])) {
            throw new SpringyException('Mail configuration "protocol" undefined');
        }

        $this->lastError = '';
        $this->mailObj = Swift_Message::newInstance();
        $this->mailObj->setCharset(config_get('main.charset', 'UTF-8'));

        $this->setProtocol($config);
    }

    /**
     * Sets the mail protocol.
     *
     * @param array $config
     *
     * @throws SpringyException
     *
     * @return void
     */
    protected function setProtocol(array $config)
    {
        if ($config['protocol'] == 'smtp') {
            if (!isset($config['host'])) {
                throw new SpringyException('Mail configuration "host" undefined');
            }

            $this->transport = Swift_SmtpTransport::newInstance(
                $config['host'],
                $config['port'] ?? 25
            );

            if (isset($config['cryptography'])) {
                $this->transport->setEncryption($config['cryptography']);
            }

            if (isset($config['username']) && $config['username']) {
                $this->transport->setUsername($config['username']);
                $this->transport->setPassword($config['password'] ?? '');
            }

            return;
        } elseif ($config['protocol'] == 'sendmail') {
            $this->transport = Swift_SendmailTransport::newInstance($config['sendmail_path'] ?? null);

            return;
        } elseif ($config['protocol'] == 'mail') {
            $this->transport = Swift_MailTransport::newInstance();

            return;
        }

        throw new SpringyException('Unsuported mail protocol');
    }

    /**
     * Adds a file to be attached to the e-mail.
     *
     * @param string $path full pathname to the attachment.
     * @param string $name override the attachment name.
     * @param string $type MIME type/file extension type.
     *
     * @return void
     */
    public function addAttachment(string $path, string $name = '', string $type = '')
    {
        $attachment = \Swift_Attachment::fromPath($path, $type);

        if ($name) {
            $attachment->setFilename($name);
        }

        $this->mailObj->attach($attachment);
    }

    /**
     * Adds an address to the 'BCC' field.
     *
     * @param string $email the email address.
     * @param string $name  the name of the person (optional).
     *
     * @return void
     */
    public function addBcc(string $email, string $name = '')
    {
        $this->mailObj->addBcc($email, $name);
    }

    /**
     * Adds an address to the 'CC' field.
     *
     * @param string $email the email address.
     * @param string $name  the name of the person (optional).
     *
     * @return void
     */
    public function addCc(string $email, string $name = '')
    {
        $this->mailObj->addCc($email, $name);
    }

    /**
     * Adds an email message header.
     *
     * @param string $header
     * @param string $value
     *
     * @return void
     */
    public function addHeader(string $header, string $value)
    {
        $this->mailObj->addTextHeader($header, $value);
    }

    /**
     * Adds an address to the 'To' field.
     *
     * @param string $email the email address.
     * @param string $name  the name of the person (optional)
     *
     * @return void
     */
    public function addTo(string $email, string $name = '')
    {
        $this->mailObj->addTo($email, $name);
    }

    /**
     * Gets the last send error message.
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * Sends the mail message.
     *
     * @return bool
     */
    public function send(): bool
    {
        $this->lastError = '';

        $mailer = Swift_Mailer::newInstance($this->transport);
        $mailer->send($this->mailObj, $this->lastError);

        return empty($this->lastError);
    }

    /**
     * Sets the alternative plain-text message body for old message readers.
     *
     * @param string $text
     *
     * @return void
     */
    public function setAlternativeBody(string $text)
    {
        $this->mailObj->addPart($text, 'text/plain');
    }

    /**
     * Adds message content body.
     *
     * @param string $body HTML ou text message body.
     * @param bool   $html set true if body is HTML ou false if plain text.
     *
     * @return void
     */
    public function setBody(string $body, bool $html = true)
    {
        $this->mailObj->setBody($body, $html ? 'text/html' : 'text/plain');
    }

    /**
     * Sets the 'From' field.
     *
     * @param string $email the email address.
     * @param string $name  the name of the person (optional).
     *
     * @return void
     */
    public function setFrom(string $email, string $name = '')
    {
        $this->mailObj->setFrom([$email => $name]);
    }

    /**
     * Sets the mail subject.
     *
     * @param string $subject the subject text.
     *
     * @return void
     */
    public function setSubject(string $subject)
    {
        $this->mailObj->setSubject($subject);
    }
}
