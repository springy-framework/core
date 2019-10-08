<?php

/**
 * Driver for use with SendGrid v7 class for integration with SendGrid API v3.
 *
 * @copyright 2015 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @see       https://github.com/sendgrid/sendgrid-php
 *
 * @version   4.0.0
 *
 * The SendGrid PHP SDK library is not a dependency of this project.
 * This driver is implemented only as a facility to the developers.
 *
 * You must add the SendGrid PHP SDK library as dependency of your project
 * by adding it in your project's composer.json file like this:
 *
 * {
 *   "require": {
 *     "sendgrid/sendgrid": "~7"
 *   }
 * }
 *
 * Or install it yourself with the following command line:
 *
 * $ composer require "sendgrid/sendgrid:~7"
 */

namespace Springy\Mail\Drivers;

use SendGrid as SendGridAPI;
use SendGrid\Mail\Mail;
use Springy\Exceptions\SpringyException;
use Throwable;

/**
 * Driver class for use with SendGrid v7 class for integration with SendGrid API v3.
 */
class SendGrid implements MailDriverInterface
{
    /** @var string last send error message */
    protected $lastError;
    /** @var SendGridAPI the SendGrid API object */
    protected $sendgrid;
    /** @var Mail the SendGrid mail transport object */
    protected $mailObj;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $apikey = $config['apikey'] ?? false;

        if (!$apikey) {
            throw new SpringyException('SendGrid API key undefined');
        }

        $options = $config['options'] ?? [];
        if (!is_array($options)) {
            throw new SpringyException('Invalid SendGrid configuration options');
        }

        $this->lastError = '';
        $this->sendgrid = new SendGridAPI($apikey, $options);
        $this->mailObj = new Mail();
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
        // $fileEncoded = base64_encode(file_get_contents($path));
        // Removed base64_encode due to bug in SDK
        $fileEncoded = file_get_contents($path);
        $this->mailObj->addAttachment($fileEncoded, $type, $name);
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
     * Adds a category to the e-mail.
     *
     * @param string $category
     *
     * @return void
     */
    public function addCategory(string $category)
    {
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
        $this->mailObj->personalization[0]->addHeader($header, $value);
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
        $this->mailObj->addSubstitution($name, $value);
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

        try {
            $response = $this->sendgrid->send($this->mailObj);
            $this->lastError = $response->body();
        } catch (Throwable $err) {
            $this->lastError = $err->getCode()
                . ' - ' . $err->getMessage()
                . ' at ' . $err->getFile()
                . ' (' . $err->getLine() . ')';
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
        $this->mailObj->addContent('text/plain', $text);
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
        $this->mailObj->addContent($html ? 'text/html' : 'text/plain', $body);
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
    public function setTemplateId(string $tid)
    {
        $this->mailObj->setTemplateId($tid);
    }
}
