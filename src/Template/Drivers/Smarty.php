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
use Springy\Exceptions\SpringyException;

class Smarty implements TemplateDriverInterface
{
    /** @var string compile id for the template */
    private $compileId;
    /** @var bool strict mode */
    protected $strict;
    /** @var string the template file name */
    protected $templateFile;
    /** @var array the template variables */
    protected $templateVars;
    /** @var SmartyTemplate the Smarty object */
    protected $tplObj;

    public function __construct()
    {
        $this->tplObj = new SmartyTemplate();
        $this->templateVars = [];
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
        $this->templateVars[$var] = [
            'value'   => $value,
            'nocache' => $nocache,
        ];
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
        $this->tplObj->clearCompiledTemplate($this->templateFile, $this->compileId, $expTime);
    }

    /**
     * Clears all compiled templates.
     *
     * @return void
     */
    public function clearCompileDir()
    {
        $this->tplObj->clearCompiledTemplate();
    }

    /**
     * Returns the template output.
     *
     * @return string
     */
    public function fetch()
    {
        if ($this->templateFile === null) {
            throw new SpringyException('Template file undefined');
        }

        // if (!$this->templateExists($this->templateFile)) {
        //     throw new SpringyException('Template file not found');
        // }

        // Alimenta as variáveis CONSTANTES
        // $this->tplObj->assign('HOST', URI::buildURL());
        // $this->tplObj->assign('CURRENT_PAGE_URI', URI::currentPageURI());
        // $this->tplObj->assign('SYSTEM_NAME', Kernel::systemName());
        // $this->tplObj->assign('SYSTEM_VERSION', Kernel::systemVersion());
        // $this->tplObj->assign('PROJECT_CODE_NAME', Kernel::projectCodeName());
        // $this->tplObj->assign('ACTIVE_ENVIRONMENT', Kernel::environment());

        // Alimenta as variáveis padrão da aplicação
        // foreach (Kernel::getTemplateVar() as $name => $value) {
        //     $this->tplObj->assign($name, $value);
        // }

        // Alimenta as variáveis do template
        foreach ($this->templateVars as $name => $data) {
            $this->tplObj->assign($name, $data['value'], $data['nocache']);
        }

        // Inicializa a função padrão assetFile
        // $this->tplObj->registerPlugin('function', 'assetFile', [$this, 'assetFile']);

        // Inicializa as funções personalizadas padrão
        // foreach (Kernel::getTemplateFunctions() as $func) {
        //     $this->tplObj->registerPlugin($func[0], $func[1], $func[2], $func[3], $func[4]);
        // }

        // Inicializa as funções personalizadas do template
        // foreach ($this->templateFuncs as $func) {
        //     $this->tplObj->registerPlugin($func[0], $func[1], $func[2], $func[3], $func[4]);
        // }

        if ($this->strict) {
            $this->tplObj->error_reporting = E_ALL & ~E_NOTICE;
        }

        SmartyTemplate::muteExpectedErrors();

        // return $this->tplObj->fetch($this->templateFile, $this->templateCacheId, $this->compileId);
        return $this->tplObj->fetch($this->templateFile, null, $this->compileId);
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
     */
    public function setCacheDir(string $path)
    {
        $this->tplObj->setCacheDir($path);
    }

    /**
     * Defines template caching strategy.
     *
     * @param mixed $cache
     *
     * @return void
     */
    public function setCaching($cache = 'current')
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
        $this->templateFile = $template;
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
