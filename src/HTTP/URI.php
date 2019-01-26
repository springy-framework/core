<?php
/**
 * URI handler class.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Lucas Cardozo <lucas.cardozo@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   3.0.0
 */
namespace Springy\HTTP;

// use Springy\Utils\Strings_ANSI;
// use Springy\Utils\Strings_UTF8;

/**
 * URI handler class.
 */
class URI
{
    // URI globally instance
    private static $instance;

    /// String da URI
    private static $uri_string = '';
    /// Array dos segmentos da URI
    private static $segments = [];
    /// Array dos segmentos ignorados
    private static $ignored_segments = [];
    /// Array da relação dos parâmetros recebidos por GET
    private static $get_params = [];
    /// Índice do segmento que determina a página atual
    private static $segment_page = 0;
    /// Nome da classe da controller
    private static $class_controller = null;

    /**
     * Return the current host with protocol.
     *
     * @return string
     */
    public function host(): string
    {
        if (php_sapi_name() === 'cli') {
            return 'cli';
        }

        return trim(
            preg_replace(
                '/([^:]+)(:\\d+)?/',
                '$1$2',
                $_SERVER['HTTP_HOST'] ?? ''
            ), ' ..@'
        );
    }

    /**
     * Returns current instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
