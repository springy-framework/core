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
     * Clears all messages.
     *
     * @return MessageContainer
     */
    public function clear()
    {
        $this->messages = [];

        return $this;
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
     * Destroy the messages with the key.
     *
     * @param string $key
     *
     * @return void
     */
    public function forget(string $key)
    {
        unset($this->messages[$key]);
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
     * Gets the messages in the container.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
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
     * Checks if there is any message stored in the container.
     *
     * @return bool
     */
    public function hasAny(): bool
    {
        return !empty($this->messages);
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
     * Implementation due to ArrayAccess interface.
     *
     * An alias to has() method.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Implementation due to ArrayAccess interface.
     *
     * A pseudo alias to get() method.
     *
     * @param string $key
     * @param string $format
     *
     * @return array
     */
    public function offsetGet($key): array
    {
        return $this->get($key);
    }

    /**
     * Implementation due to ArrayAccess interface.
     *
     * An alias to add() method.
     *
     * @return void
     */
    public function offsetSet($key, $msg)
    {
        $this->add($key, $msg);
    }

    /**
     * Implementation due to ArrayAccess interface.
     *
     * An alias to forget() method.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->forget($key);
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
}
