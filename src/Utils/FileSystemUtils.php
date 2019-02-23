<?php
/**
 * Trait with file system helper functions.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version	  1.0.0
 *
 * The methods of this trait can be accessed by seting use
 * of this trait inside user classes.
 *
 * @see http://php.net/manual/pt_BR/language.oop5.traits.php
 */

namespace Springy\Utils;

trait FileSystemUtils
{
    /**
     * Unlink extended function.
     *
     * Removes files and folders recursively if the closures callback
     * function returns true for passed file system object.
     *
     * @param string   $path
     * @param \Closure $callback
     * @param bool     $recursive
     *
     * @return bool
     */
    protected function unlinkExtended(string $path, \Closure $callback, $recursive = false): bool
    {
        if (!is_dir($path)) {
            return $callback($path) && unlink($path);
        }

        $objects = scandir($path);
        $empty = true;

        foreach ($objects as $object) {
            if ($object == '.' || $object == '..') {
                continue;
            }

            $empty = $this->unlinkExtended($path.DS.$object, $callback, $recursive) && $empty;
        }

        return $empty && rmdir($path);
    }
}
