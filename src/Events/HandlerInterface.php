<?php
/**
 * Event handler interface.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Events;

/**
 * Event handler interface.
 */
interface HandlerInterface
{
    /**
     * Registers this class as handlers in the necessary events.
     *
     * @param Mediator $mediator
     *
     * @return void
     */
    public function subscribes(Mediator $mediator);
}
