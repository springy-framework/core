<?php
/**
 * Mock function for test case for Springy\HTTP\UploadedFile class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

function is_uploaded_file($tmpName): bool
{
    return file_exists($tmpName);
}

function move_uploaded_file($tmpName, $to): bool
{
    return @rename($tmpName, $to);
}
