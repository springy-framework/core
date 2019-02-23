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

use Mustache_Engine as MustacheEngine;
use Mustache_Loader_FilesystemLoader as FilesystemLoader;
use Mustache_Logger_StreamLogger as StreamLogger;
use Springy\Utils\FileSystemUtils;

class Mustache implements TemplateDriverInterface
{
    use FileSystemUtils;

    /** @var array the list of template home directories */
    protected $templateDirs;
    /** @var string the template file name */
    protected $templateFile;
    /** @var array the template variables */
    protected $templateVars;
    /** @var MustacheEngine the Mustache object */
    protected $tplObj;
    /** @var array the template options */
    protected $tplOptions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->templateDirs = [];
        $this->templateFile = '';
        $this->templateVars = [];
        $this->tplOptions = [
            'cache'                  => null,
            'cache_file_mode'        => 0666,
            'cache_lambda_templates' => false,
            // 'helpers' => [
            //     'i18n' => function ($text) {
            //         // do something translatey here...
            //     }
            // ],
            'escape' => function ($value) {
                return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
            },
            'charset' => 'UTF-8',
            'logger' => new StreamLogger('php://stderr'),
            'strict_callables' => true,
            'pragmas' => [MustacheEngine::PRAGMA_FILTERS],
        ];
    }

    /**
     * Creates the Mustache object.
     *
     * @return MustacheEngine
     */
    protected function createTplObj(): MustacheEngine
    {
        $options = $this->tplOptions;
        $options['loader'] = new FilesystemLoader(
            $this->templateDirs[0],
            [
                'extension' => '',
            ]
        );
        // $options['partials_loader'] = new FilesystemLoader($this->templateDirs[0]);

        return new MustacheEngine($options);
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
            foreach ($dir as $path) {
                $this->addTemplateDir($dir);
            }

            return;
        }

        // Mustache template engine does not accept multiple template directories
        // then always overwrites the 0 index.
        $this->templateDirs[0] = $dir;
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
        if (!$this->tplOptions['cache']) {
            return;
        }

        $dir = $this->tplOptions['cache'].DS;
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
     * Turns off the cache_lambda_templates option.
     *
     * In Mustache template has no method to deletes a single
     * template cache.
     *
     * @param int $expireTime not used.
     *
     * @return void
     */
    public function clearTemplateCache(int $expireTime = null)
    {
        $expireTime = 0;

        $this->envOptions['cache_lambda_templates'] = false;
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
        // $this->tplObj->addFunction(new \Mustache_SimpleFunction('assetFile', [$this, 'assetFile']));

        // Inicializa as funções personalizadas padrão
        // foreach (Kernel::getTemplateFunctions() as $func) {
        //     $this->tplObj->addFunction(new \Mustache_SimpleFunction($func[1], $func[2]));
        // }

        // Inicializa as funções personalizadas do template
        // foreach ($this->templateFuncs as $func) {
        //     $this->tplObj->addFunction(new \Mustache_SimpleFunction($func[1], $func[2]));
        // }

        $template = $this->createTplObj()->loadTemplate($this->templateFile);

        return $template->render($vars);
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
        return false;
    }

    /**
     * Sets the auto-escaping strategy.
     *
     * @param string|bool $autoEscape
     *
     * @return void
     *
     * @see https://github.com/bobthecow/mustache.php/wiki
     */
    public function setAutoEscape($autoEscape)
    {
        if (is_object($autoEscape) && ($autoEscape instanceof Closure)) {
            $this->envOptions['escape'] = $autoEscape;

            return;
        }

        $this->envOptions['escape'] = null;
    }

    /**
     * Turns the debug on or off.
     *
     * This method do nothing. Exists only by an interface requisition.
     *
     * @param bool $debug
     *
     * @return void
     */
    public function setDebug(bool $debug)
    {
        $debug = false;
    }

    /**
     * Turns on or off the recompilation of the template on every invocation.
     *
     * @param bool $forceCompile
     *
     * @return void
     *
     * @see https://github.com/bobthecow/mustache.php/wiki
     */
    public function setForceCompile(bool $forceCompile = null)
    {
        $this->envOptions['cache_lambda_templates'] = !$forceCompile;
    }

    /**
     * Turns on or off the optimizations.
     *
     * This method do nothing. Exists only by an interface requisition.
     *
     * @param int $optimizations
     *
     * @return void
     *
     * @see https://github.com/bobthecow/mustache.php/wiki
     */
    public function setOptimizations(int $optimizations)
    {
        $optimizations = 0;
    }

    /**
     * Turns on or off the strict mode.
     *
     * This method do nothing. Exists only by an interface requisition.
     *
     * @param bool $strict
     *
     * @return void
     */
    public function setStrict(bool $strict)
    {
        $strict = false;
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
     * @param int $seconds
     *
     * @return void
     */
    public function setCacheLifetime(int $seconds)
    {
        $seconds = 0;
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

        unset($this->envOptions['cache']);
    }

    /**
     * Defines the compiled template folder.
     *
     * This method do nothing. Mustache has not compiled directory.
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
     * This method do nothing. Mustache has not compile process.
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
        if (is_array($path)) {
            $path = $path[0];
        }

        $this->templateDirs[0] = $path;
    }

    /**
     * Turns on or off the use of subdirectories to save compiled
     * and cached templates.
     *
     * This has no effect on Mustache template.
     *
     * @param bool $useSubDirs
     *
     * @return void
     */
    public function setUseSubDirs(bool $useSubDirs)
    {
        $useSubDirs = false;
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

        return true;
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
