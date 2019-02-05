<?php
/**
 * Interface for template plugin drivers.
 *
 * This class is an interface for building drivers for interaction
 * with session.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP\SessionDrivers;

interface SessionDriverInterface
{
    public function defined(string $name): bool;

    public function forget(string $name);

    public function get(string $name, $default = null);

    public function getId(): string;

    public function set(string $name, $value = null);

    public function setId(string $sessId);

    public function start(): bool;
}
