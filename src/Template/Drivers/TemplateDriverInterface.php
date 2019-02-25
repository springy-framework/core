<?php
/**
 * Interface for template plugin drivers.
 *
 * This class is an interface for building drivers for interaction
 * with template plugins.
 *
 * @copyright 2015 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.2.0
 */

namespace Springy\Template\Drivers;

interface TemplateDriverInterface
{
    /**
     * Adds alternate path to the template folders.
     *
     * @param array|string $dir
     *
     * @return void
     */
    public function addTemplateDir($dir);

    /**
     * Clears the template cache folder.
     *
     * @param int $expireTime
     *
     * @return void
     */
    public function clearCache(int $expTime = 0);

    /**
     * Clears the compiled version of the template.
     *
     * @param int $expTime only compiled templates older than $expTime seconds are cleared.
     *
     * @return void
     */
    public function clearCompiled(int $expTime);

    /**
     * Clears all compiled templates.
     *
     * @return void
     */
    public function clearCompileDir();

    /**
     * Invalidates the cache for current template.
     *
     * @param int $expireTime if defined only template cache older than expireTime seconds are deleted.
     *
     * @return void
     */
    public function clearTemplateCache(int $expireTime = null);

    /**
     * Returns the template output.
     *
     * @param array $vars
     * @param array $functions
     *
     * @return string
     */
    public function fetch(array $vars, array $functions = []): string;

    /**
     * Returns the template file name.
     *
     * @return string
     */
    public function getTemplateName(): string;

    /**
     * Checks if the template is cached.
     *
     * @return bool
     */
    public function isCached(): bool;

    /**
     * Sets the auto-escaping strategy.
     *
     * Turns the variables escaping on or off.
     *
     * @param string|bool $autoEscape
     *
     * @return void
     */
    public function setAutoEscape($autoEscape);

    /**
     * Sets the template cache folder.
     *
     * @param string $path
     *
     * @return void
     */
    public function setCacheDir(string $path);

    /**
     * Sets the cache id.
     *
     * @param string $cid
     *
     * @return void
     */
    public function setCacheId(string $cid);

    /**
     * Sets the template cache lifetime.
     *
     * @param int $seconds
     *
     * @return void
     */
    public function setCacheLifetime(int $seconds);

    /**
     * Defines template caching strategy.
     *
     * @param mixed $cache
     *
     * @return void
     */
    public function setCaching($cache);

    /**
     * Defines the compiled template folder.
     *
     * @param string
     *
     * @return void
     */
    public function setCompileDir(string $path);

    /**
     * Sets the compile identifier.
     *
     * @param string $cid
     *
     * @return void
     */
    public function setCompileId(string $cid);

    /**
     * Turns the debug on or off.
     *
     * @param bool $debug
     *
     * @return void
     */
    public function setDebug(bool $debug);

    /**
     * Turns on or off the recompilation of the template on every invocation.
     *
     * @param bool $forceCompile
     *
     * @return void
     */
    public function setForceCompile(bool $forceCompile = null);

    /**
     * Turns on or off the optimizations.
     *
     * @param int $optimizations
     *
     * @return void
     */
    public function setOptimizations(int $optimizations);

    /**
     * Turns on or off the strict mode.
     *
     * @param bool $strict
     *
     * @return void
     */
    public function setStrict(bool $strict);

    /**
     * Sets the template file.
     *
     * @param string $template
     *
     * @return void
     */
    public function setTemplate(string $template);

    /**
     * Sets the path to the template folder.
     *
     * @param array|string $path
     *
     * @return void
     */
    public function setTemplateDir($path);

    /**
     * Turns on or off the use of subdirectories to save compiled
     * and cached templates.
     *
     * @param bool $useSubDirs
     *
     * @return void
     */
    public function setUseSubDirs(bool $useSubDirs);

    /**
     * Checks whether the specified template exists.
     *
     * @param string $templateFile
     *
     * @return bool
     */
    public function templateExists(string $templateFile = null): bool;
}
