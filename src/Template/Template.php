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
    const DRV_SMARTY = 'smarty';
    const DRV_TWIG = 'twig';

    /** @var string sufix for template files */
    protected $fileSufix;
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

        $config = Kernel::getInstance()->configuration();

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
            self::DRV_SMARTY => 'Springy\Template\Drivers\Smarty',
            self::DRV_TWIG   => 'Springy\Template\Drivers\Twig',
        ];

        if (!isset($drivers[$driver])) {
            throw new SpringyException('Template driver unknown or not supported');
        }

        $this->tplObj = new $drivers[$driver]();
    }

    /**
     * Assigns a variable to the template.
     *
     * @param string $var     the name of the variable.
     * @param mixed  $value   the value of the variable.
     * @param bool   $nocache (optional) if true, the variable is assigned as nocache variable.
     *
     * @return void
     */
    public function assign(string $var, $value = null, $nocache = false)
    {
        return $this->tplObj->assign($var, $value, $nocache);
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
     * Gets the template compiled.
     *
     * @return string
     */
    public function fetch(): string
    {
        return $this->tplObj->fetch();
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
}
