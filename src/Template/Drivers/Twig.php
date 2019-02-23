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

use Springy\Utils\FileSystemUtils;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

class Twig implements TemplateDriverInterface
{
    use FileSystemUtils;

    /** @var int the cache life time in seconds */
    protected $cacheTime;
    /** @var array the environment options */
    protected $envOptions;
    /** @var array the list of template home directories */
    protected $templateDirs;
    /** @var string the template file name */
    protected $templateFile;
    /** @var array the template variables */
    protected $templateVars;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->envOptions = [
            'debug'            => false,
            'cache'            => false,
            'auto_reload'      => false,
            'strict_variables' => true,
            'autoescape'       => false,
            'optimizations'    => -1,
        ];

        $this->cacheTime = 3600;
        $this->templateDirs = [];
        $this->templateFile = '';
        $this->templateVars = [];
    }

    /**
     * Creates the Twig object.
     *
     * @return TwigEnvironment
     */
    protected function createTplObj(): TwigEnvironment
    {
        return new TwigEnvironment(
            new FilesystemLoader($this->templateDirs),
            $this->envOptions
        );
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
        if (is_array($dir)) {
            $this->templateDirs = array_merge($this->templateDirs, $dir);

            return;
        }

        $this->templateDirs[] = $dir;
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
     * Clears the template cache folder.
     *
     * @param int $expireTime
     *
     * @return void
     */
    public function clearCache(int $expTime = 0)
    {
        if (!$this->envOptions['cache']) {
            return;
        }

        $dir = $this->envOptions['cache'].DS;
        $objects = scandir($dir);
        if (!$objects) {
            return;
        }

        foreach ($objects as $object) {
            if ($object == '.' || $object == '..') {
                continue;
            }

            $this->unlinkExtended(
                $dir.$object,
                function ($file) use ($expTime) {
                    return filemtime($file) <= (time() - $expTime);
                },
                true
            );
        }
    }

    /**
     * Clears the compiled version of the template.
     *
     * This method do nothing. Exists only by an interface requisition.
     *
     * @param int $expTime only compiled templates older than $expTime seconds are cleared.
     *
     * @return void
     */
    public function clearCompiled(int $expTime)
    {
        $expTime = 0;
    }

    /**
     * Clears all compiled templates.
     *
     * This method do nothing. Exists only by an interface requisition.
     *
     * @return void
     */
    public function clearCompileDir()
    {
        // Do nothing
    }

    /**
     * Turns on the auto_reload option.
     *
     * In Twig template has no method to deletes a single
     * template cache.
     *
     * @param int $expireTime not used.
     *
     * @return void
     */
    public function clearTemplateCache(int $expireTime = null)
    {
        $expireTime = 0;

        $this->envOptions['auto_reload'] = true;
    }

    /**
     * Returns the template output.
     *
     * @return string
     */
    public function fetch()
    {
        // Alimenta as variáveis CONSTANTES
        $vars = [];
        // $vars = [
        //     'HOST'               => URI::buildURL(),
        //     'CURRENT_PAGE_URI'   => URI::currentPageURI(),
        //     'SYSTEM_NAME'        => Kernel::systemName(),
        //     'SYSTEM_VERSION'     => Kernel::systemVersion(),
        //     'PROJECT_CODE_NAME'  => Kernel::projectCodeName(),
        //     'ACTIVE_ENVIRONMENT' => Kernel::environment(),
        // ];

        // Alimenta as variáveis padrão da aplicação
        // foreach (Kernel::getTemplateVar() as $name => $data) {
        //     $vars[$name] = $data;
        // }

        // Alimenta as variáveis do template
        foreach ($this->templateVars as $name => $data) {
            $vars[$name] = $data['value'];
        }

        // Inicializa a função padrão assetFile
        // $this->tplObj->addFunction(new \Twig_SimpleFunction('assetFile', [$this, 'assetFile']));

        // Inicializa as funções personalizadas padrão
        // foreach (Kernel::getTemplateFunctions() as $func) {
        //     $this->tplObj->addFunction(new \Twig_SimpleFunction($func[1], $func[2]));
        // }

        // Inicializa as funções personalizadas do template
        // foreach ($this->templateFuncs as $func) {
        //     $this->tplObj->addFunction(new \Twig_SimpleFunction($func[1], $func[2]));
        // }

        return $this->createTplObj()->render($this->templateFile, $vars);
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
     */
    public function isCached(): bool
    {
        // $this->createTplObj()->getLoader()->isFresh($this->templateFile, time() - $this->cacheTime);
        // $this->createTplObj()->isTemplateFresh($this->templateFile, time() - $this->cacheTime);
        // Above methods returns true if template file is older than time() - $this->cacheTime
        // Inverted logic?
        // There is no method to check is cache file exists or get your name

        return false;
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
        $this->envOptions['autoescape'] = $autoEscape;
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
        $this->envOptions['debug'] = $debug;
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
        $this->envOptions['auto_reload'] = $forceCompile ?? $this->envOptions['debug'];
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
        $this->envOptions['optimizations'] = $optimizations;
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
        $this->envOptions['strict_variables'] = $strict;
    }

    /**
     * Sets the template cache folder.
     *
     * @param string $path path in the file system.
     *
     * @return void
     */
    public function setCacheDir(string $path)
    {
        $this->envOptions['cache'] = $path;
    }

    /**
     * Sets the cache id.
     *
     * This method do nothing. Exists only by an interface requisition.
     *
     * Twig does not support identification for cached templates.
     *
     * @param string $cid
     *
     * @return void
     */
    public function setCacheId(string $cid)
    {
        $cid = '';
    }

    /**
     * Sets the template cache lifetime.
     *
     * This method do nothing. Exists only by an interface requisition.
     *
     * Twig does not support life time for cached templates.
     *
     * @param int $seconds
     *
     * @return void
     */
    public function setCacheLifetime(int $seconds)
    {
        $this->cacheTime = $seconds;
    }

    /**
     * Defines template caching strategy.
     *
     * This method works only to turns cache off.
     *
     * @param bool $cache
     *
     * @return void
     */
    public function setCaching($cache = false)
    {
        if ($cache !== false) {
            return;
        }

        $this->envOptions['cache'] = false;
    }

    /**
     * Defines the compiled template folder.
     *
     * This method do nothing. Twig has not compiled directory.
     * Exists only by an interface requisition.
     *
     * @param string $path
     *
     * @return void
     */
    public function setCompileDir(string $path)
    {
        $path = '';
    }

    /**
     * Sets the compile identifier.
     *
     * This method do nothing. Twig has not compile process.
     *
     * @param string $cid
     *
     * @return void
     */
    public function setCompileId(string $cid)
    {
        $cid = '';
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
        if (!is_array($path)) {
            $path = [$path];
        }

        $this->templateDirs = $path;
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
        $this->envOptions['use_sub_dirs'] = $useSubDirs;
        unset($this->envOptions['use_sub_dirs']);
    }

    /**
     * Checks whether the specified template exists.
     *
     * @param string $templateFile
     *
     * @return bool
     *
     * @see https://twig.symfony.com/doc/2.x/api.html#loaders
     */
    public function templateExists(string $templateFile = null): bool
    {
        if ($templateFile === null) {
            $templateFile = $this->templateFile;
        }

        return $this->createTplObj()->getLoader()->exists($templateFile);
    }

    /**
     * Unassigns an assigned variable.
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

