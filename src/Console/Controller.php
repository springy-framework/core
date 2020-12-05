<?php

/**
 * Parent class for console command controllers.
 *
 * Extends this class to construct consolle commands to the applications.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Console;

use Springy\Core\ControllerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Parent class for console command controllers.
 */
class Controller extends Command implements ControllerInterface
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * The input interface implementation.
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * The command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The output interface implementation.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructor.
     *
     * @param array $segments
     */
    public function __construct(array $segments)
    {
        $this->name = $segments[0] ?? null;

        parent::__construct($this->name);

        $this->setDescription($this->description);

        // Global options
        $this->addOption('help', 'h', InputOption::VALUE_NONE, 'Display this help message.');
        $this->addOption('quiet', 'q', InputOption::VALUE_NONE, 'Do not output any message.');
        $this->addOption('ansi', null, InputOption::VALUE_NONE, 'Force ANSI output.');
        $this->addOption('no-ansi', null, InputOption::VALUE_NONE, 'Disable ANSI output.');
        $this->addOption(
            'verbose',
            'v|vv|vvv',
            InputOption::VALUE_OPTIONAL,
            'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug.'
        );
        $this->addOption('no-interaction', 'n', InputOption::VALUE_NONE, 'Do not ask any interactive question.');
        $this->addOption('version', 'V', InputOption::VALUE_NONE, 'Display application version.');
    }

    /**
     * Default command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('command', InputArgument::REQUIRED, 'The command to execute');
    }

    /**
     * Execute the console command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return call_user_func([$this, 'index'], $input, $output);
    }

    /**
     * Configures the IO interface.
     *
     * @return void
     */
    protected function configIO()
    {
        $verbosities = [
            OutputInterface::VERBOSITY_QUIET => ['-q', '--quiet'],
            OutputInterface::VERBOSITY_DEBUG => ['-vvv', '--verbose=3'],
            OutputInterface::VERBOSITY_VERY_VERBOSE => ['-vv', '--verbose=2'],
            OutputInterface::VERBOSITY_VERBOSE => ['-v', '--verbose', '--verbose=1'],
        ];
        foreach ($verbosities as $key => $value) {
            if ($this->input->hasParameterOption($value, true)) {
                $this->output->setVerbosity($key);
            }
        }

        if ($this->input->hasParameterOption(['--ansi'], true)) {
            $this->output->setDecorated(true);
        } elseif ($this->input->hasParameterOption(['--no-ansi'], true)) {
            $this->output->setDecorated(false);
        }

        if ($this->input->hasParameterOption(['-n', '--no-interaction'], true)) {
            $this->input->setInteractive(false);
        }
    }

    /**
     * Gets the application name with version.
     *
     * @return string
     */
    protected function getAppNameVersion(): string
    {
        return sprintf('<options=bold>%s v%s</>', app_name(), app_version());
    }

    /**
     * Returns command tag for current sapi.
     *
     * @return string
     */
    protected function getCommandTag(): string
    {
        if (php_sapi_name() === 'cli') {
            return '%command.full_name%';
        }

        return '%command.name%';
    }

    /**
     * Prints out application title and version and command description or name.
     *
     * @return void
     */
    protected function printTitle()
    {
        $this->output->writeln([
            $this->getAppNameVersion() . ' - ' . ($this->description ? $this->description : $this->name),
            '',
        ]);
    }

    /**
     * Prints the list of options for help output.
     *
     * @return void
     */
    protected function printOptions()
    {
        $leftcol = 0;
        $options = [];

        foreach ($this->getDefinition()->getOptions() as $option) {
            $value = '';

            if ($option->acceptValue()) {
                $value = sprintf(
                    ' %s%s%s',
                    $option->isValueOptional() ? '[' : '',
                    strtoupper($option->getName()),
                    $option->isValueOptional() ? ']' : ''
                );
            }

            $shortcut = $option->getShortcut() ? sprintf('-%s ', $option->getShortcut()) : '';
            $optString = sprintf('%s--%s%s', $shortcut, $option->getName(), $value);
            $options[] = [
                $optString,
                $option->getDescription(),
            ];

            $leftcol = max($leftcol, strlen($optString));
        }

        $this->output->writeln('Options:');
        foreach ($options as $option) {
            $this->output->writeln(
                '  ' . str_pad($option[0], $leftcol + 2, ' ') . $option[1]
            );
        }
    }

    /**
     * Checks whether the user has permission to the resource.
     *
     * @return bool
     */
    public function hasPermission(): bool
    {
        return true;
    }

    /**
     * Run the console command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->configIO();

        return parent::run($this->input, $this->output);
    }
}
