<?php
/**
 * Initialization script fir PHPUnit.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

// Registers the Composer autoload
require __DIR__.'/../vendor/autoload.php';

// Sets the default timezone
date_default_timezone_set('UTC');

if (!is_dir(__DIR__.'/var')) {
    mkdir(__DIR__.'/var', 0775);
}

$app = Springy\Core\Kernel::getInstance(__DIR__.'/conf/main.php');
