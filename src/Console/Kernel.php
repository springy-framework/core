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

class Kernel extends MainKernel
{
    /** @var int exit status code */
    protected $exitStatus;
    /** @var OutputInterface the console output interface */
    protected $output;

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

        $command = $input->getFirstArgument();
        // if ($command === null) {
        //     return $this->discoverInternals($command);
        // }

        $segment = $this->findController('App\\Console\\', [$command ?? '']);
        if ($segment < 0 && !$this->discoverInternals($command)) {
            return false;
        }

        $this->exitStatus = static::$controller->run($input, $this->output);

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
        if ($command === null) {
            $command = 'help';
        }

        switch ($command) {
            case 'help':
                static::$controller = new HelpCommand([$command]);
                return true;
            case 'migrator':
                static::$controller = new MigratorCommand([$command]);
                return true;
        }

        return false;
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
            '  '.$_SERVER['PHP_SELF'].' --help',
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
