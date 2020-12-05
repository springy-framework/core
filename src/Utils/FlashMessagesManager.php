<?php

/**
 * Session flash data manager.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Utils;

use Springy\HTTP\Session;

/**
 * Session flash data manager.
 */
class FlashMessagesManager
{
    // Flash data session key
    public const FLASH_KEY = '__FLASHDATA__';

    /** @var MessageContainer new error messages to be saved to the next request */
    protected $newErrors;
    /** @var MessageContainer new messages to be saved to the next request */
    protected $newMessages;
    /** @var MessageContainer last request saved error messages */
    protected $oldErrors;
    /** @var MessageContainer last request saved messages */
    protected $oldMessages;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->newErrors = new MessageContainer();
        $this->newMessages = new MessageContainer();
        $this->oldErrors = new MessageContainer();
        $this->oldMessages = new MessageContainer();

        $this->loadLastSessionData();

        // Clears the last request session messages data
        Session::getInstance()->forget(self::FLASH_KEY);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->registerSessionData();
    }

    /**
     * Loads the container with the last request session data.
     *
     * @return void
     */
    protected function loadLastSessionData()
    {
        $sessionData = Session::getInstance()->get(self::FLASH_KEY);

        if (isset($sessionData['errors'])) {
            $this->oldErrors->setMessages($sessionData['errors']);
        }

        if (isset($sessionData['messages'])) {
            $this->oldMessages->setMessages($sessionData['messages']);
        }
    }

    /**
     * Saves in session current messages containers to be used in next request.
     *
     * @return void
     */
    protected function registerSessionData()
    {
        $flashData = [];

        if ($this->newErrors->hasAny()) {
            $flashData['errors'] = $this->newErrors->getMessages();
        }

        if ($this->newMessages->hasAny()) {
            $flashData['messages'] = $this->newMessages->getMessages();
        }

        if (!empty($flashData)) {
            Session::getInstance()->set(self::FLASH_KEY, $flashData);
        }
    }

    /**
     * Gets the error messages container.
     *
     * @return MessageContainer
     */
    public function errors(): MessageContainer
    {
        return $this->newErrors;
    }

    /**
     * Gets the last request error messages container.
     *
     * @return MessageContainer
     */
    public function lastErrors()
    {
        return $this->oldErrors;
    }

    /**
     * Gets the last request messages container.
     *
     * @return MessageContainer
     */
    public function lastMessages()
    {
        return $this->oldMessages;
    }

    /**
     * Gets the messages container.
     *
     * @return MessageContainer
     */
    public function messages(): MessageContainer
    {
        return $this->newMessages;
    }

    /**
     * Sets error messages container with new data.
     *
     * @param MessageContainer $errors
     *
     * @return void
     */
    public function setErrors(MessageContainer $errors)
    {
        $this->newErrors = $errors;
    }

    /**
     * Sets messages container with new data.
     *
     * @param MessageContainer $messages
     *
     * @return void
     */
    public function setMessages(MessageContainer $messages)
    {
        $this->newMessages = $messages;
    }
}
