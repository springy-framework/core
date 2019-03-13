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
    protected $exitStatus;

    protected $output;

    /**
     * Tries to discover a command line controller.
     *
     * @return bool
     */
    protected function discoverController(): bool
    {
        // if (self::$envType === self::ENV_TYPE_WEB) {
        //     return false;
        // }

        $this->exitStatus = 0;
        $input = new ArgvInput();
        $this->output = new ConsoleOutput();

        if ($this->isVersionArg($input, $this->output)) {
            return true;
        }

        $command = $input->getFirstArgument();
        if ($command === null) {
            return false;
        }

        $segment = $this->findController('App\\Controllers\\Console\\', [$command]);
        if ($segment < 0) {
            return false;
        }

        $this->exitStatus = static::$controller->run($input, $this->output);

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

    protected function getGeneralHelp()
    {
        return [
            'Usage: ',
        ];
    }

    /**
     * Checks whether the -V|--version option was received.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function isVersionArg(InputInterface $input, OutputInterface $output): bool
    {
        if (true === $input->hasParameterOption(['--version', '-V'], true)) {
            $output->writeln($this->getAppNameVersion());

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
        ]);

        $this->output->writeln($this->getGeneralHelp());
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
