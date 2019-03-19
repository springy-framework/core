<?php
/**
 * Web console terminal controllers.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class Terminal
{
    /** @var Response the response object */
    protected $response;

    /**
     * Constructor.
     *
     * @param array $segments
     */
    public function __construct(array $segments)
    {
        Kernel::getInstance()->configuration()->set('application.debug', false);

        $this->response = Response::getInstance();

        $this->parseSegments($segments);
    }

    /**
     * Runs the command if exists or return invalid command error.
     *
     * @param string $command
     * @param string $parameters
     *
     * @return void
     */
    protected function command(string $command, string $parameters)
    {
        $commands = [
            'errors' => 'Springy\Console\ErrorsCommand',
            'help'   => 'Springy\Console\HelpCommand',
        ];

        if (!isset($commands[$command])) {
            $this->response->body('[[!gb;red;]Command not found.]');

            return;
        }

        $class = $commands[$command];
        $input = new StringInput($command.' '.$parameters);
        $output = new BufferedOutput();
        $command = new $class([$command]);
        $command->run($input, $output);
        $this->response->body($output->fetch());
    }

    /**
     * Parses the segments array and try to execute de command.
     *
     * @param array $segments
     *
     * @return void
     */
    protected function parseSegments(array $segments)
    {
        if (count($segments) === 0) {
            return $this->startTerminal();
        }

        $command = str_replace('%20', ' ', implode('/', $segments));
        $parameters = explode(' ', $command);
        $command = array_shift($parameters);
        $parameters[] = '--no-interaction';
        $parameters = implode(' ', $parameters);
        if (!strpos($parameters, '--no-ansi')) {
            $parameters .= ' --ansi';
        }

        $this->command($command, $parameters);
    }

    /**
     * Starts the terinal view.
     *
     * @return void
     */
    protected function startTerminal()
    {
        $body = file_get_contents(__DIR__.DS.'assets'.DS.'terminal.html');
        $this->response->body($body);
    }
}
