<?php
/**
 * Migration revisions.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Migration;

use DirectoryIterator;
use Iterator;
use Springy\Exceptions\SpringyException;

class Revisions implements Iterator
{
    /** @var array the list of applied revision scripts */
    protected $applied;
    /** @var array the list of not applied revision scripts */
    protected $notApplied;
    /** @var string the revisions path */
    protected $revPath;
    /** @var array the list of revisions */
    protected $revs;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        if (!is_dir($path)) {
            throw new SpringyException('"'.$path.'" is not a directory.');
        }

        $this->applied = [];
        $this->notApplied = [];
        $this->revPath = $path;
        $this->revs = [];

        foreach (new DirectoryIterator($path) as $file) {
            $dir = $file->getBasename();

            if ($file->isDir() && !$file->isDot() && is_numeric($dir)) {
                $this->getScriptsFiles($dir);
            }
        }

        usort($this->revs, function ($left, $right) {
            return $left->getIdentity() < $right->getIdentity() ? -1 : 1;
        });
    }

    /**
     * Load the migration script files.
     *
     * @param string $version
     *
     * @return void
     */
    protected function getScriptsFiles(string $version)
    {
        $path = $this->revPath.DS.$version;

        foreach (new DirectoryIterator($path) as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $this->revs[] = new MigrationScript($this->revPath, $version, $file->getBasename());
        }
    }

    /**
     * Returns the current migration script.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->revs);
    }

    /**
     * Gets the MigrationScript identified by index.
     *
     * @param int $index
     *
     * @return MigrationScript
     */
    public function get(int $index): MigrationScript
    {
        if (!isset($this->revs[$index])) {
            throw new SpringyException('Undefined index '.$index.' in revisions iterator.');
        }

        return $this->revs[$index];
    }

    /**
     * Returns the list of applied migrations.
     *
     * @return array
     */
    public function getApplied(): array
    {
        return $this->applied;
    }

    /**
     * Returns the list of not applied migrations.
     *
     * @return array
     */
    public function getNotApplied(): array
    {
        return $this->notApplied;
    }

    /**
     * Get the array of revisions.
     *
     * @return array
     */
    public function getRevisions(): array
    {
        return $this->revs;
    }

    /**
     * Returns the key of current revision.
     *
     * @return int
     */
    public function key()
    {
        return key($this->revs);
    }

    /**
     * Moves the iterator to next revision.
     *
     * @return void
     */
    public function next()
    {
        next($this->revs);
    }

    /**
     * Rewinds the iterator to first revision.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->revs);
    }

    /**
     * Sets as applied the revision identified by key.
     *
     * @param int $key
     *
     * @return void
     */
    public function setApplied(int $key)
    {
        if (!isset($this->revs[$key])) {
            return;
        }

        $name = $this->revs[$key]->getIdentity();

        if (array_key_exists($name, $this->applied)) {
            return;
        }

        $this->applied[$name] = $key;
        krsort($this->applied);

        if (array_key_exists($name, $this->notApplied)) {
            unset($this->notApplied[$name]);
        }
    }

    /**
     * Sets as not applied the revision identified by key.
     *
     * @param int $key
     *
     * @return void
     */
    public function setNotApplied(int $key)
    {
        if (!isset($this->revs[$key])) {
            return;
        }

        $name = $this->revs[$key]->getIdentity();

        if (array_key_exists($name, $this->notApplied)) {
            return;
        }

        $this->notApplied[$name] = $key;
        ksort($this->notApplied);

        if (array_key_exists($name, $this->applied)) {
            unset($this->applied[$name]);
        }
    }

    /**
     * Checke whether the itarator is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return current($this->revs) !== false;
    }
}
