<?php
/**
 * Parent class for console command controllers.
 *
 * Extends this class to construct consolle commands to the applications.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Console;

use Springy\Core\ControllerInterface;
use Springy\Exceptions\SpringyException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

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

    protected $terminal;

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

        $this->terminal = new Terminal();

        parent::__construct($this->name);

        $this->setDescription($this->description);
        $this->addArgument('command', InputArgument::REQUIRED, 'The command to execute');
        $this->addOption('help', 'h', InputOption::VALUE_NONE, 'Display this help message.');
        $this->addOption('quiet', 'q', InputOption::VALUE_NONE, 'Do not output any message.');
        $this->addOption('verbose', 'v|vv|vvv', InputOption::VALUE_OPTIONAL, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug.');
        $this->addOption('version', 'V', InputOption::VALUE_NONE, 'Display application version.');
        $this->addOption('ansi', null, InputOption::VALUE_NONE, 'Force ANSI output.');
        $this->addOption('no-ansi', null, InputOption::VALUE_NONE, 'Disable ANSI output.');
        $this->addOption('no-interaction', 'n', InputOption::VALUE_NONE, 'Do not ask any interactive question.');
    }

    /**
     * Throws a "Forbidden" error.
     *
     * @throws SpringyException
     *
     * @return void
     */
    public function _forbidden()
    {
        throw new SpringyException('Forbidden.');
    }

    /**
     * Checks whether the user has permission to the resource.
     *
     * @return bool
     */
    public function _hasPermission(): bool
    {
        return true;
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

    protected function configIO()
    {
        putenv('LINES='.$this->terminal->getHeight());
        putenv('COLUMNS='.$this->terminal->getWidth());

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
