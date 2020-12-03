<?php

/**
 * Driver for Smarty template engine.
 *
 * @copyright 2015 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.8.0
 *
 * This class implements support for Smarty template engine.
 *
 * @see       http://www.smarty.net/
 */

namespace Springy\Template\Drivers;

use Smarty as SmartyTemplate;
use Springy\Core\Kernel;

/**
 * Class driver for Smarty template engine.
 */
class Smarty implements TemplateDriverInterface
{
    /** @var string cache id for the template */
    private $cacheId;
    /** @var string compile id for the template */
    private $compileId;
    /** @var bool strict mode */
    protected $strict;
    /** @var string the template file name */
    protected $templateFile;
    /** @var SmartyTemplate the Smarty object */
    protected $tplObj;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tplObj = new SmartyTemplate();
        $this->templateFile = '';
    }

    /**
     * Adds alternate path to the template folders.
     *
     * @param array|string $dir
     *
     * @return void
     */
    public function addTemplateDir($dir)
    {
        $this->tplObj->addTemplateDir($dir);
    }

    /**
     * Clears the template cache folder.
     *
     * @param int $expireTime
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/api.clear.all.cache.tpl
     */
    public function clearCache(int $expTime = 0)
    {
        $this->tplObj->clearAllCache($expTime);
    }

    /**
     * Clears the compiled version of the template.
     *
     * @param int $expTime only compiled templates older than $expTime seconds are cleared.
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/api.clear.compiled.tpl.tpl
     */
    public function clearCompiled(int $expTime)
    {
        $this->tplObj->clearCompiledTemplate($this->templateFile, $this->compileId, $expTime);
    }

    /**
     * Clears all compiled templates.
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/api.clear.compiled.tpl.tpl
     */
    public function clearCompileDir()
    {
        $this->tplObj->clearCompiledTemplate();
    }

    /**
     * Invalidates the cache for current template.
     *
     * @param int $expireTime if defined only template cache older than expireTime seconds are deleted.
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/api.clear.cache.tpl
     */
    public function clearTemplateCache(int $expireTime = null)
    {
        $this->tplObj->clearCache($this->templateFile, $this->cacheId, $this->compileId, $expireTime);
    }

    /**
     * Returns the template output.
     *
     * @param array $vars
     * @param array $functions
     *
     * @return string
     */
    public function fetch(array $vars, array $functions = []): string
    {
        foreach ($vars as $name => $data) {
            $this->tplObj->assign($name, $data);
        }

        // Registers personal functions
        foreach ($functions as $name => $callback) {
            $this->tplObj->registerPlugin(
                'function',
                $name,
                $callback
            );
        }

        $kernel = Kernel::getInstance();
        $ignoredErrros = $kernel->errorHandler()->getIgnoredErrors();

        if (!$this->strict) {
            $kernel->errorHandler()->addIgnoredError(E_NOTICE);
            $this->tplObj->error_reporting = E_ALL & ~E_NOTICE;
            SmartyTemplate::muteExpectedErrors();
        }

        $parsed = $this->tplObj->fetch($this->templateFile, $this->cacheId, $this->compileId);

        $kernel->errorHandler()->delIgnoredError(E_NOTICE);
        $kernel->errorHandler()->addIgnoredError($ignoredErrros);

        return $parsed;
    }

    /**
     * Returns the template file name.
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateFile;
    }

    /**
     * Checks if the template is cached.
     *
     * @return bool
     *
     * @see https://www.smarty.net/docs/en/api.is.cached.tpl
     */
    public function isCached(): bool
    {
        return $this->tplObj->isCached($this->templateFile, $this->cacheId, $this->compileId);
    }

    /**
     * Sets the auto-escaping strategy.
     *
     * Turns the variables escaping on or off.
     *
     * @param string|bool $autoEscape
     *
     * @return void
     */
    public function setAutoEscape($autoEscape)
    {
        $this->tplObj->escape_html = ($autoEscape !== false);
    }

    /**
     * Sets the template cache folder.
     *
     * @param string $path
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/api.set.cache.dir.tpl
     */
    public function setCacheDir(string $path)
    {
        $this->tplObj->setCacheDir($path);
    }

    /**
     * Sets the cache id.
     *
     * @param string $cid
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/variable.cache.id.tpl
     */
    public function setCacheId(string $cid)
    {
        $this->cacheId = $cid;
    }

    /**
     * Sets the template cache lifetime.
     *
     * @param int $seconds
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/variable.cache.lifetime.tpl
     */
    public function setCacheLifetime(int $seconds)
    {
        $this->tplObj->setCacheLifetime($seconds);
    }

    /**
     * Defines template caching strategy.
     *
     * @param mixed $cache
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/variable.caching.tpl
     */
    public function setCaching($cache)
    {
        if ($cache === false) {
            $this->tplObj->setCaching(SmartyTemplate::CACHING_OFF);
        }

        $this->tplObj->setCaching(
            $cache == 'current'
            ? SmartyTemplate::CACHING_LIFETIME_CURRENT
            : SmartyTemplate::CACHING_LIFETIME_SAVED
        );
    }

    /**
     * Defines the compiled template folder.
     *
     * @param string
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/api.set.compile.dir.tpl
     */
    public function setCompileDir(string $path)
    {
        $this->tplObj->setCompileDir($path);
    }

    /**
     * Sets the compile identifier.
     *
     * @param string $cid
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/variable.compile.id.tpl
     */
    public function setCompileId(string $cid)
    {
        $this->compileId = $cid;
    }

    /**
     * Turns the debug on or off.
     *
     * @param bool $debug
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/variable.debugging.tpl
     */
    public function setDebug(bool $debug)
    {
        $this->tplObj->debugging = $debug;
    }

    /**
     * Turns on or off the recompilation of the template on every invocation.
     *
     * @param bool $forceCompile
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/variable.force.compile.tpl
     */
    public function setForceCompile(bool $forceCompile = null)
    {
        $this->tplObj->force_compile = $forceCompile ?? $this->tplObj->debugging;
    }

    /**
     * Turns on or off the optimizations.
     *
     * This has no effect in Smarty template
     *
     * @param int $optimizations
     *
     * @return void
     */
    public function setOptimizations(int $optimizations)
    {
        $optimizations = 0;
    }

    /**
     * Turns on or off the strict mode.
     *
     * @param bool $strict
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/variable.error.reporting.tpl
     * @see https://www.smarty.net/docs/en/api.mute.expected.errors.tpl
     */
    public function setStrict(bool $strict)
    {
        $this->strict = $strict;
    }

    /**
     * Sets the template file.
     *
     * @param string $template
     *
     * @return void
     */
    public function setTemplate(string $template)
    {
        $this->templateFile = $template;
    }

    /**
     * Sets the path to the template folder.
     *
     * @param array|string $path
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/api.set.template.dir.tpl
     */
    public function setTemplateDir($path)
    {
        $this->tplObj->setTemplateDir($path);
    }

    /**
     * Turns on or off the use of subdirectories to save compiled
     * and cached templates.
     *
     * @param bool $useSubDirs
     *
     * @return void
     *
     * @see https://www.smarty.net/docs/en/variable.use.sub.dirs.tpl
     */
    public function setUseSubDirs(bool $useSubDirs)
    {
        $this->tplObj->use_sub_dirs = $useSubDirs;
    }

    /**
     * Checks whether the specified template exists.
     *
     * @param string $templateFile
     *
     * @return bool
     *
     * @see https://www.smarty.net/docs/en/api.template.exists.tpl
     */
    public function templateExists(string $templateFile = null): bool
    {
        if ($templateFile === null) {
            $templateFile = $this->templateFile;
        }

        return $this->tplObj->templateExists($templateFile);
    }
}
