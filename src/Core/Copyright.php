<?php
/**
 * Framework copyright class.
 *
 * @copyright 2016 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 *
 * @version   2.0.0
 */

namespace Springy\Core;

class Copyright
{
    /**
     * Prints the framework copyright page.
     *
     * @return string
     */
    public function content(): string
    {
        $html = file_get_contents(__DIR__.DS.'assets'.DS.'copyright.html');

        return $html;
    }

    /**
     * Gets self.
     *
     * @return self
     */
    public static function getInstance()
    {
        return new self();
    }
}
