<?php

/**
 * Help and usage console command.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Console;

/**
 * Help and usage console command controller.
 */
class HelpCommand extends Controller
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Console help';

    /**
     * Runs the command.
     *
     * @return void
     */
    protected function index()
    {
        $this->name = '';
        $this->setHelp([
            'Usage:',
            '  ' . (php_sapi_name() === 'cli' ? $_SERVER['PHP_SELF'] . ' ' : '') . '<command> <options>',
            '',
            'Commands:',
            '  help      Display this help message.',
            '  errors    Display the application errors log.',
            '  migrator  Install database migrations.',
            '',
        ]);

        $this->printTitle();
        $this->output->writeln($this->getProcessedHelp());
        $this->printOptions();

        return 0;
    }
}
