<?php
/**
 * Driver class for use with PHPMailer class.
 *
 * @copyright 2016 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @see       https://github.com/PHPMailer/PHPMailer
 *
 * @version   2.0.0
 *
 * The PHPMailer library is not a dependency of this project.
 * This driver is implemented only as a facility to the developers.
 *
 * You must add the PHPMailer library as dependency of your project
 * by adding it in your project's composer.json file like this:
 *
 * {
 *   "require": {
 *     "phpmailer/phpmailer": "~6.0"
 *   }
 * }
 *
 * Or install it yourself with the following command line:
 *
 * $ composer require "phpmailer/phpmailer:~6.0"
 */

namespace Springy\Mail\Drivers;

use PHPMailer\PHPMailer\PHPMailer as PHPMailerDriver;
use Springy\Exceptions\SpringyException;

class PhpMailer implements MailDriverInterface
{
    /** @var string last send error message */
    protected $lastError;
    /** @var PHPMailerDriver the PHPMailer object */
    protected $mailObj;

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
        $this->mailObj = new PHPMailerDriver(true);
        $this->mailObj->CharSet = config_get('main.charset', 'UTF-8');

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

            $this->mailObj->isSMTP();
            $this->mailObj->SMTPDebug = $config['debug'] ?? false;
            $this->mailObj->Debugoutput = $config['debugoutput'] ?? 'html';
            $this->mailObj->Host = $config['host'];
            $this->mailObj->Port = $config['port'] ?? 25;
            $this->mailObj->SMTPAuth = $config['authenticated'] ?? false;
            $this->mailObj->SMTPSecure = $config['cryptography'] ?? '';
            if ($this->mailObj->SMTPAuth) {
                $this->mailObj->Username = $config['username'] ?? '';
                $this->mailObj->Password = $config['password'] ?? '';
            }

            return;
        } elseif ($config['protocol'] == 'sendmail') {
            $this->mailObj->isSendmail();

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
        $this->mailObj->addAttachment($path, $name, 'base64', $type);
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
        $this->mailObj->addCustomHeader($header, $value);
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
        $this->mailObj->addAddress($email, $name);
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

        try {
            if (!$this->mailObj->send()) {
                $this->lastError = $this->mailObj->ErrorInfo;
            }
        } catch (\Throwable $err) {
            $this->lastError = $err->getCode().': '.$err->getMessage();
        }

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
        $this->mailObj->AltBody = $text;
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
        if ($html) {
            $this->mailObj->msgHTML($body);

            return;
        }

        $this->setAlternativeBody($body);
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
        $this->mailObj->Subject = $subject;
    }
}
