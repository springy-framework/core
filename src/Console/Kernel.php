<?php

/**
 * Kernel for the console application requisition.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Console;

use Springy\Core\Kernel as MainKernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Kernel for the console application requisition.
 */
class Kernel extends MainKernel
{
    /** @var static Kernel globally instance */
    protected static $instance;

    /** @var int exit status code */
    protected $exitStatus;
    /** @var OutputInterface the console output interface */
    protected $output;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     *
     * @param array|string $conf the array of configuration or
     *                           the full path name of the configuration file.
     */
    protected function __construct($appConf = null)
    {
        parent::__construct($appConf);
        parent::$instance = $this;
        self::$instance = $this;
    }

    /**
     * Tries to discover a command line controller.
     *
     * @return bool
     */
    protected function discoverController(): bool
    {
        $this->exitStatus = 0;
        $input = new ArgvInput();
        $this->output = new ConsoleOutput();

        if ($this->isVersionArg($input)) {
            return true;
        }

        $command = $input->getFirstArgument() ?? '';
        if (
            !$this->loadController('App\\Console\\' . $this->normalizeNamePath([$command]), [])
            && !$this->discoverInternals($command)
        ) {
            return false;
        }

        $this->exitStatus = $this->controller->run($input, $this->output);

        return true;
    }

    /**
     * Checks for internal commands.
     *
     * @param string $command
     *
     * @return bool
     */
    protected function discoverInternals(string $command = null): bool
    {
        if (is_null($command)) {
            $command = 'help';
        }

        $commands = [
            'errors'   => 'Springy\Console\ErrorsCommand',
            'help'     => 'Springy\Console\HelpCommand',
            'migrator' => 'Springy\Console\MigratorCommand',
        ];
        if (!isset($commands[$command])) {
            return false;
        }

        $this->controller = new $commands[$command]([$command]);

        return true;
    }

    /**
     * Returns the name of the application and its version.
     *
     * @return string
     */
    protected function getAppNameVersion(): string
    {
        return sprintf(
            '%s <info>%s</info>',
            $this->getApplicationName(),
            $this->getApplicationVersion()
        );
    }

    /**
     * Checks whether the -V|--version option was received.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function isVersionArg(InputInterface $input): bool
    {
        if (true === $input->hasParameterOption(['--version', '-V'], true)) {
            $this->output->writeln($this->getAppNameVersion());

            return true;
        }

        return false;
    }

    /**
     * Prints the command not found error message.
     *
     * @return void
     */
    protected function notFound()
    {
        $this->output->writeln([
            $this->getAppNameVersion(),
            '',
            '<error>Command not found.</error>',
            '',
            'Try:',
            '  ' . $_SERVER['PHP_SELF'] . ' --help',
        ]);
    }

    /**
     * Gets the exit code status.
     *
     * @return int
     */
    public function getExitStatus(): int
    {
        return $this->exitStatus;
    }
}
