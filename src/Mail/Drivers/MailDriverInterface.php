<?php

/**
 * Interface for mail drivers implementations.
 *
 * @copyright 2015 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   3.0.0
 */

namespace Springy\Mail\Drivers;

/**
 * Interface for mail drivers implementations.
 */
interface MailDriverInterface
{
    /**
     * Adds a file to be attached to the e-mail.
     *
     * @param string $path full pathname to the attachment.
     * @param string $name override the attachment name.
     * @param string $type MIME type/file extension type.
     *
     * @return void
     */
    public function addAttachment(string $path, string $name = '', string $type = '');

    /**
     * Adds an address to the 'BCC' field.
     *
     * @param string $email the email address.
     * @param string $name  the name of the person (optional).
     *
     * @return void
     */
    public function addBcc(string $email, string $name = '');

    /**
     * Adds an address to the 'CC' field.
     *
     * @param string $email the email address.
     * @param string $name  the name of the person (optional).
     *
     * @return void
     */
    public function addCc(string $email, string $name = '');

    /**
     * Adds a standard email message header.
     *
     * @param string $header
     * @param string $value
     *
     * @return void
     */
    public function addHeader(string $header, string $value);

    /**
     * Adds an address to the 'To' field.
     *
     * @param string $email the email address.
     * @param string $name  the name of the person (optional)
     *
     * @return void
     */
    public function addTo(string $email, string $name = '');

    /**
     * Gets the last send error message.
     *
     * @return string
     */
    public function getLastError(): string;

    /**
     * Sends the mail message.
     *
     * @return bool
     */
    public function send(): bool;

    /**
     * Sets the alternative plain-text message body for old message readers.
     *
     * @param string $text
     *
     * @return void
     */
    public function setAlternativeBody(string $text);

    /**
     * Adds message content body.
     *
     * @param string $body HTML ou text message body.
     * @param bool   $html set true if body is HTML ou false if plain text.
     *
     * @return void
     */
    public function setBody(string $body, bool $html = true);

    /**
     * Sets the 'From' field.
     *
     * @param string $email the email address.
     * @param string $name  the name of the person (optional).
     *
     * @return void
     */
    public function setFrom(string $email, string $name = '');

    /**
     * Sets the mail subject.
     *
     * @param string $subject the subject text.
     *
     * @return void
     */
    public function setSubject(string $subject);
}
