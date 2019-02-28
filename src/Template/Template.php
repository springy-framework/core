<?php
/**
 * Template handler class.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Lucas Cardozo <lucas.cardozo@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   5.0.0
 */

namespace Springy\Template;

use Springy\Core\Kernel;
use Springy\Exceptions\SpringyException;

class Template
{
    const DRV_MUSTACHE = 'mustache';
    const DRV_SMARTY = 'smarty';
    const DRV_TWIG = 'twig';

    /** @var string sufix for template files */
    protected $fileSufix;
    /** @var array template personal functions */
    private $templateFuncs;
    /** @var array the template variables */
    protected $templateVars;
    /** @var object the template driver */
    protected $tplObj;

    /**
     * Constructor method.
     *
     * @param array|string|null $template the template file name
     */
    public function __construct($template = null)
    {
        $this->startDriver();

        $kernel = Kernel::getInstance();
        $config = $kernel->configuration();

        $this->fileSufix = $config->get('template.file_sufix', '.html');

        $this->tplObj->setAutoEscape($config->get('template.auto_escape', ''));
        $this->tplObj->setDebug($config->get('template.debug', false));
        $this->tplObj->setForceCompile($config->get('template.force_compile', false));
        $this->tplObj->setOptimizations($config->get('template.optimizations', -1));
        $this->tplObj->setStrict($config->get('template.strict', false));
        $this->tplObj->setUseSubDirs($config->get('template.use_sub_dirs', false));

        $this->tplObj->setCacheDir($config->get('template.paths.cache'));
        $this->tplObj->setCompileDir($config->get('template.paths.compiled'));
        $this->tplObj->setTemplateDir($config->get('template.paths.templates'));

        $altPath = $config->get('template.paths.alternative');
        if ($altPath) {
            $this->tplObj->addTemplateDir($altPath);
        }

        $this->templateFuncs = [];

        // Initiates template vars with global vars
        $this->templateVars = [
            'APP_NAME'      => $kernel->getApplicationName(),
            'APP_VERSION'   => $kernel->getApplicationVersion(),
            'APP_CODE_NAME' => $kernel->getAppCodeName(),
            'ENVIRONMENT'   => $kernel->getEnvironment(),
        ];

        if ($template !== null) {
            $this->setTemplate($template);
        }
    }

    /**
     * Builds the template driver object.
     *
     * @throws SpringyException
     *
     * @return void
     */
    protected function startDriver()
    {
        $driver = config_get('template.driver');
        if ($driver === null) {
            throw new SpringyException('Template driver undefined');
        }

        $drivers = [
            self::DRV_MUSTACHE => 'Mustache',
            self::DRV_SMARTY   => 'Smarty',
            self::DRV_TWIG     => 'Twig',
        ];

        if (!isset($drivers[$driver])) {
            throw new SpringyException('Template driver unknown or not supported');
        }

        $driver = __NAMESPACE__.'\\Drivers\\'.$drivers[$driver];

        $this->tplObj = new $driver();
    }

    /**
     * Registers custom functions or methods as template plugins.
     *
     * @param string $name     defines the name of the plugin.
     * @param mixed  $callback defines the callback.
     *
     * @return void
     */
    public function addFunction(string $name, $callback)
    {
        $this->templateFuncs[$name] = $callback;
    }

    /**
     * Adds alternates template home directories.
     *
     * @param array|string $dir
     *
     * @return void
     */
    public function addTemplateDir($dir)
    {
        return $this->tplObj->addTemplateDir($dir);
    }

    /**
     * Assigns a variable to the template.
     *
     * @param string $var   the name of the variable.
     * @param mixed  $value the value of the variable.
     *
     * @return void
     */
    public function assign(string $var, $value = null)
    {
        $this->templateVars[$var] = $value;
    }

    /**
     * Clears the entire template cache.
     *
     * As an optional parameter, you can supply a minimum age in seconds
     * the cache files must have to get deleted.
     *
     * @param int $expTime
     *
     * @return void
     */
    public function clearCache(int $expTime = 0)
    {
        return $this->tplObj->clearCache($expTime);
    }

    /**
     * Clears the compiled version of the template.
     *
     * @param int $expTime only compiled templates older than $expTime seconds are cleared.
     *
     * @return void
     */
    public function clearCompiled(int $expTime)
    {
        return $this->tplObj->clearCompiled($expTime);
    }

    /**
     * Clears the compiled templates folder.
     *
     * @return void
     */
    public function clearCompileDir()
    {
        return $this->tplObj->clearCompileDir();
    }

    /**
     * Invalidates the cache for current template.
     *
     * @param int $expireTime if defined only template cache older than expireTime seconds are deleted.
     *
     * @return void
     */
    public function clearTemplateCache(int $expireTime = null)
    {
        return $this->tplObj->clearTemplateCache($expireTime);
    }

    /**
     * Gets the template compiled.
     *
     * @return string
     */
    public function fetch(): string
    {
        $template = $this->tplObj->getTemplateName();

        if ($template === '') {
            throw new SpringyException('Template file undefined');
        } elseif (!$this->tplObj->templateExists($template)) {
            throw new SpringyException('Template file "'.$template.'" does not exists');
        }

        return $this->tplObj->fetch($this->templateVars, $this->templateFuncs);
    }

    /**
     * Returns the internal template driver object.
     *
     * @return object
     */
    public function getTemplateDriver()
    {
        return $this->tplObj;
    }

    /**
     * Checks if the template is cached.
     *
     * @return bool
     */
    public function isCached(): bool
    {
        return $this->tplObj->isCached();
    }

    /**
     * Sets the auto-escaping strategy.
     *
     * For Smarty driver sets escale_html true or false.
     *
     * @param string $autoEscape
     *
     * @return void
     */
    public function setAutoEscape(string $autoEscape)
    {
        $this->tplObj->setAutoEscape($autoEscape);
    }

    /**
     * Sets the template cache folder.
     *
     * @param string $path
     *
     * @return void
     */
    public function setCacheDir(string $path)
    {
        return $this->tplObj->setCacheDir($path);
    }

    /**
     * Sets the cache id.
     *
     * @param string $cid
     *
     * @return void
     */
    public function setCacheId(string $cid)
    {
        return $this->tplObj->setCacheId($cid);
    }

    /**
     * Sets the template cache lifetime.
     *
     * @param int $seconds
     *
     * @return void
     */
    public function setCacheLifetime(int $seconds)
    {
        $this->tplObj->setCacheLifetime($seconds);
    }

    /**
     * Defines template caching strategy.
     *
     * @param string|bool $cache
     *
     * @return void
     */
    public function setCaching($cache = false)
    {
        return $this->tplObj->setCaching($cache);
    }

    /**
     * Defines the compiled template folder.
     *
     * @param string
     *
     * @return void
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
     */
    public function setCompileId(string $cid)
    {
        return $this->tplObj->setCompileId($cid);
    }

    /**
     * Turns the template engine debug on or off.
     *
     * @param bool $debug
     *
     * @return void
     */
    public function setDebug(bool $debug)
    {
        $this->tplObj->setDebug($debug);
    }

    /**
     * Turns on or off the recompilation of the template on every invocation.
     *
     * @param bool $forceCompile
     *
     * @return void
     */
    public function setForceCompile(bool $forceCompile = null)
    {
        $this->tplObj->setForceCompile($forceCompile);
    }

    /**
     * Turns on or off the optimizations.
     *
     * @param int $optimizations
     *
     * @return void
     */
    public function setOptimizations(int $optimizations)
    {
        $this->tplObj->setOptimizations($optimizations);
    }

    /**
     * Turns on or off the strict mode.
     *
     * @param bool $strict
     *
     * @return void
     */
    public function setStrict(bool $strict)
    {
        $this->tplObj->setStrict($strict);
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
        $this->tplObj->setTemplate($template.$this->fileSufix);
    }

    /**
     * Sets the template home directories.
     *
     * @param array|string $path
     *
     * @return void
     */
    public function setTemplateDir($path)
    {
        return $this->tplObj->setTemplateDir($path);
    }

    /**
     * Turns on or off the use of subdirectories to save compiled
     * and cached templates.
     *
     * @param bool $useSubDirs
     *
     * @return void
     */
    public function setUseSubDirs(bool $useSubDirs)
    {
        $this->tplObj->setUseSubDirs($useSubDirs);
    }

    /**
     * Checks whether the specified template exists.
     *
     * @param string $templateName
     *
     * @return bool
     */
    public function templateExists(string $templateName = null): bool
    {
        return $this->tplObj->templateExists(
            $templateName === null
            ? $this->tplObj->getTemplateName()
            : $templateName.$this->fileSufix
        );
    }

    /**
     * Unissigns an assigned variable.
     *
     * @param string $var
     *
     * @return void
     */
    public function unassign(string $var)
    {
        unset($this->templateVars[$var]);
    }
}
