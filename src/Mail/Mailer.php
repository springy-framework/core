<?php

/**
 * Mailer.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Lucas Cardozo <lucas.cardozo@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   4.0.0
 */

namespace Springy\Mail;

use Springy\Exceptions\SpringyException;

/**
 * Mailer class.
 */
class Mailer
{
    // Supported mailer drivers constans
    public const MAIL_ENGINE_PHPMAILER = 'phpmailer';
    public const MAIL_ENGINE_SENDGRID = 'sendgrid';
    public const MAIL_ENGINE_SWIFTMAILER = 'swiftmailer';

    protected $fakeTo;
    /** @var object the mailer driver object */
    protected $mailObj;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->startDriver();

        if (($errorsTo = config_get('mail.errors_go_to', '')) !== '') {
            $this->addHeader('Errors-To', $errorsTo);
        }

        $this->fakeTo = config_get('mail.fake_to', '');
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->mailObj);
    }

    protected function fakeTo()
    {
        $this->fakeTo = config_get('mail.fake_to');
    }

    /**
     * Build the mailer object driver.
     *
     * @throws SpringyException
     *
     * @return void
     */
    protected function startDriver()
    {
        $driver = config_get('mail.driver');
        if ($driver === null) {
            throw new SpringyException('Mail driver undefined');
        }

        $drivers = [
            self::MAIL_ENGINE_PHPMAILER   => 'PhpMailer',
            self::MAIL_ENGINE_SENDGRID    => 'SendGrid',
            self::MAIL_ENGINE_SWIFTMAILER => 'SwiftMailer',
        ];

        if (!isset($drivers[$driver])) {
            throw new SpringyException('Mail driver unknown or not supported');
        }

        $config = config_get('mail.settings');
        if ($config === null) {
            throw new SpringyException('Mail driver configuration settings not defined');
        }

        $driver = __NAMESPACE__ . '\\Drivers\\' . $drivers[$driver];

        $this->mailObj = new $driver($config);
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
        if (is_array($path)) {
            $name = $path['name'];
            $type = $path['type'];
            $path = $path['tmp_name'];
        }

        $this->mailObj->addAttachment($path, $name, $type);
    }

    public function addBcc(string $email, string $name = '')
    {
        if ($this->fakeTo) {
            return;
        }

        if (is_array($email)) {
            foreach ($email as $addr => $name) {
                $this->mailObj->addBcc($addr, $name);
            }

            return;
        }

        $this->mailObj->addBcc($email, $name);
    }

    /**
     * Adds a category to the e-mail.
     *
     * @param string $category
     *
     * @throws SpringyException
     *
     * @return void
     */
    public function addCategory(string $category)
    {
        if (!method_exists($this->mailObj, 'addCategory')) {
            throw new SpringyException('Current mailer driver has no support to categories');
        }

        $this->mailObj->addCategory($category);
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
        if ($this->fakeTo) {
            return;
        }

        if (is_array($email)) {
            foreach ($email as $addr => $name) {
                $this->mailObj->addCc($addr, $name);
            }

            return;
        }

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
        $this->mailObj->addHeader($header, $value);
    }

    /**
     * Adds value to a template variable.
     *
     * @param string $name  name of the template variable.
     * @param string $value the value.
     *
     * @return void
     */
    public function addTemplateVar(string $name, string $value)
    {
        if (!method_exists($this->mailObj, 'addTemplateVar')) {
            throw new SpringyException('Current mailer driver has no support to templates');
        }

        $this->mailObj->addTemplateVar($name, $value);
    }

    /**
     * Adds an address to the 'To' field.
     *
     * @param string|array $email the email address.
     * @param string       $name  the name of the person (optional)
     *
     * @return void
     */
    public function addTo(string $email, string $name = '')
    {
        if ($this->fakeTo) {
            return;
        }

        if (is_array($email)) {
            foreach ($email as $addr => $name) {
                $this->mailObj->addTo($addr, $name);
            }

            return;
        }

        $this->mailObj->addTo($email, $name);
    }

    /**
     * Gets the last send error message.
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->mailObj->getLastError();
    }

    /**
     * Sends the mail message.
     *
     * @return bool
     */
    public function send(): bool
    {
        if ($this->fakeTo) {
            $this->mailObj->addTo($this->fakeTo);
        }

        return $this->mailObj->send();
    }

    /**
     * Send message helper function.
     *
     * @param string $from
     * @param string $name
     * @param string $mailto
     * @param string $to_name
     * @param string $subject
     * @param string $htmlmessage
     * @param string $textmessage
     *
     * @return bool
     */
    public function sendMessage(
        string $from,
        string $fromName,
        string $mailto,
        string $toName,
        string $subject,
        string $htmlMessage,
        string $textMessage = ''
    ): bool {
        $this->setFrom($from, $fromName);
        $this->addTo($mailto, $toName);
        $this->setSubject($subject);
        $this->setBody($htmlMessage, true);
        $this->setAlternativeBody($textMessage);

        return $this->send();
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
        $this->mailObj->setAlternativeBody($text);
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
        $this->mailObj->setBody($body, $html);
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
        $this->mailObj->setFrom($email, $name);
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

    /**
     * Sets a transactional template for this email.
     *
     * @param string $tid the id of the template.
     *
     * @return void
     */
    public function setTemplateId($tid)
    {
        if (!method_exists($this->mailObj, 'setTemplateId')) {
            throw new SpringyException('Current mailer driver has no support to templates');
        }

        $this->mailObj->setTemplateId($tid);
    }
}
