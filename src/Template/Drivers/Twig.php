<?php
/**
 * Class driver for Smarty template engine.
 *
 * @copyright 2015 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.8.0
 *
 * This class implements support for Twig template engine.
 *
 * @see       https://twig.symfony.com/
 */

namespace Springy\Template\Drivers;

class Twig implements TemplateDriverInterface
{
    /** @var array the list of template directories */
    protected $templateDir;
    /** @var array otpions for the template engine */
    protected $tplOptions;

    public function __construct()
    {
        $this->tplOptions= [
            'debug'            => false,
            'cache'            => false,
            'auto_reload'      => false,
            'strict_variables' => true,
            'autoescape'       => false,
            'optimizations'    => -1,
        ];

        $this->templateDir = [];
    }

    /**
     * Sets the auto-escaping strategy.
     *
     * @param string|bool $autoEscape
     *
     * @return void
     *
     * @see https://twig.symfony.com/doc/2.x/api.html#environment-options
     */
    public function setAutoEscape($autoEscape)
    {
        $this->tplOptions['autoescape'] = $autoEscape;
    }

    /**
     * Turns the debug on or off.
     *
     * @param bool $debug
     *
     * @return void
     *
     * @see https://twig.symfony.com/doc/2.x/api.html#environment-options
     */
    public function setDebug(bool $debug)
    {
        $this->tplOptions['debug'] = $debug;
    }

    /**
     * Turns on or off the recompilation of the template on every invocation.
     *
     * @param bool $forceCompile
     *
     * @return void
     *
     * @see https://twig.symfony.com/doc/2.x/api.html#environment-options
     */
    public function setForceCompile(bool $forceCompile = null)
    {
        $this->tplOptions['auto_reload'] = $forceCompile ?? $this->tplOptions['debug'];
    }

    /**
     * Turns on or off the optimizations.
     *
     * @param int $optimizations
     *
     * @return void
     *
     * @see https://twig.symfony.com/doc/2.x/api.html#environment-options
     */
    public function setOptimizations(int $optimizations)
    {
        $this->tplOptions['optimizations'] = $optimizations;
    }

    /**
     * Turns on or off the strict mode.
     *
     * @param bool $strict
     *
     * @return void
     *
     * @see https://twig.symfony.com/doc/2.x/api.html#environment-options
     */
    public function setStrict(bool $strict)
    {
        $this->tplOptions['strict_variables'] = $strict;
    }

    /**
     * Sets the path to the template folder.
     *
     * @param array|string $path
     *
     * @return void
     */
    public function setTemplateDir($path)
    {
        $this->templateDir = $path;
    }

    /**
     * Turns on or off the use of subdirectories to save compiled
     * and cached templates.
     *
     * This has no effect on Twig template.
     *
     * @param bool $useSubDirs
     *
     * @return void
     */
    public function setUseSubDirs(bool $useSubDirs)
    {
        $this->tplOptions['use_sub_dirs'] = $useSubDirs;
        unset($this->tplOptions['use_sub_dirs']);
    }
}

