<?php
/**
 * Container class for text messages.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Utils;

use ArrayAccess;

/**
 * Container class for text messages.
 */
class MessageContainer implements ArrayAccess
{
    /// Array que guarda as mensagens adicionadas no container
    protected $messages;
    /// Formato do placeholder da mensagem
    protected $format = ':msg';

    /**
     * Constructor.
     *
     * @param array $messages
     */
    public function __construct(array $messages = [])
    {
        $this->messages = [];
        $this->setMessages($messages);
    }

    /**
     * Checks if there is any message stored in the container.
     *
     * @return bool
     */
    public function hasAny(): bool
    {
        return !empty($this->messages);
    }

    /**
     * Checks if any messages are stored with the identification key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->messages[$key]) && $this->messages[$key] != '';
    }

    /**
     * Gets the first message stored with the key.
     *
     * @param string $key    identification key.
     * @param string $format placeholder format.
     *
     * @return string|null
     */
    public function first(string $key, string $format = ':msg')
    {
        if ($this->has($key)) {
            $first = $this->formatMsg($key, reset($this->messages[$key]), $format);

            return $first[0];
        }
    }

    /**
     * Gets all messages stored with the key.
     *
     * @param string $key    identification key.
     * @param string $format placeholder format.
     *
     * @return array
     */
    public function get(string $key, string $format = ':msg'): array
    {
        if ($this->has($key)) {
            return $this->formatMsg($key, $this->messages[$key], $format);
        }

        return [];
    }

    /**
     * Gets all stored messages.
     *
     * @param string $format placeholder format.
     *
     * @return array
     */
    public function all(string $format = ':msg'): array
    {
        $msgs = [];

        foreach ($this->messages as $key => $msg) {
            $msgs = array_merge($msgs, $this->formatMsg($key, $msg, $format));
        }

        return $msgs;
    }

    /**
     * Adds a message to the identifying key.
     *
     * @param string $key identification key.
     * @param string $msg the message.
     *
     * @return MessageContainer
     */
    public function add(string $key, string $msg)
    {
        if (is_array($msg)) {
            foreach ($msg as $m) {
                $this->add($key, $m);
            }

            return $this;
        }

        if ($this->unique($key, $msg)) {
            $this->messages[$key][] = $msg;
        }

        return $this;
    }

    /**
     * Concatenates this message container with another.
     *
     * @param MessageContainer $messageContainer.
     *
     * @return void
     */
    public function merge(self $messageContainer)
    {
        $this->setMessages($messageContainer->getMessages());
    }

    /**
     * Sets the messages in the container.
     *
     * @param array $messages
     *
     * @return MessageContainer
     */
    public function setMessages(array $messages)
    {
        foreach ($messages as $key => $msg) {
            $this->add($key, $msg);
        }

        return $this;
    }

    /**
     * Gets the messages in the container.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Destroy the messages with the key.
     *
     * @param string $key
     *
     * @return void
     */
    public function forget($key)
    {
        unset($this->messages[$key]);
    }

    /**
     * Returns whether the message is unique to the identifying key.
     *
     * @param string $key identification key.
     * @param string $msg the message
     *
     * @return bool
     */
    protected function unique(string $key, string $msg): bool
    {
        return !isset($this->messages[$key]) || !in_array($msg, $this->messages[$key]);
    }

    /**
     * Compiles the message by replacing the placeholders with the messages themselves.
     *
     * @param string       $key    identification key.
     * @param array|string $msg    the message.
     * @param string|null  $format placeholder format.
     *
     * @return array
     */
    protected function formatMsg(string $key, $msg, string $format = null): array
    {
        $msgs = [];
        $params = [':key', ':msg'];

        foreach ((array) $msg as $m) {
            $msgs[] = str_replace($params, [$key, $m], $this->format($format));
        }

        return $msgs;
    }

    /**
     * Gets the format with the placeholder.
     *
     * @param string $format
     *
     * @return string
     */
    protected function format(string $format = null): string
    {
        if (!$format) {
            return $this->format;
        }

        return $format;
    }

    /**
     * An alias for 'has()'.
     *
     * Will be deprecated.
     *
     * @deprecated 1.1
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * An alias for 'get()'.
     *
     * Will be deprecated.
     *
     * @deprecated 1.1
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * An alias for 'add()'.
     *
     * Will be deprecated.
     *
     * @deprecated 1.1
     */
    public function offsetSet($offset, $value)
    {
        $this->add($offset, $value);
    }

    /**
     * An alias for 'forget()'.
     *
     * Will be deprecated.
     *
     * @deprecated 1.1
     */
    public function offsetUnset($offset)
    {
        $this->forget($offset);
    }
}
