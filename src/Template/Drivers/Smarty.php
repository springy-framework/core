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
 * This class implements support for Smarty template engine.
 *
 * @see       http://www.smarty.net/
 */

namespace Springy\Template\Drivers;

use Smarty as SmartyTemplate;

class Smarty implements TemplateDriverInterface
{
    /** @var SmartyTemplate the Smarty object */
    protected $tplObj;
    /** @var bool strict mode */
    protected $strict;

    public function __construct()
    {
        $this->tplObj = new SmartyTemplate();
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
        $this->optimizations = $optimizations;
        unset($this->optimizations);
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
        // if ($this->strict) {
        //     $this->tplObj->error_reporting = E_ALL & ~E_NOTICE;
        //     \SmartyTemplate::muteExpectedErrors();

        //     return;
        // } elseif ($this->tplObj->error_reporting !== null) {
        //     $this->tplObj->error_reporting = null;
        //     \SmartyTemplate::unMuteExpectedErrors();

        //     return;
        // }

        $this->strict = $strict;
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
}
