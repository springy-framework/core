<?php
/**
 * Errors console command.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Console;

use DateTime;
use DirectoryIterator;
use Springy\Core\Kernel;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class ErrorsCommand extends Controller
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Application errors';

    /** @var string the errors log directory */
    protected $logDir;
    /** @var string parameter to the instruction */
    protected $parameter;
    /** @var string third argument */
    protected $argument;

    // List of show sub-commands
    const SHOW_COMMAND = [
        'cookie',
        'debug',
        'details',
        'get',
        'post',
        'server',
        'session',
        'trace',
    ];

    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->addArgument('instruction', InputArgument::OPTIONAL, 'Instruction.');
        $this->addArgument('parameter', InputArgument::OPTIONAL, 'Parameter.');
        $this->addArgument('argument', InputArgument::OPTIONAL, 'Third argument.');

        $this->addUsage($this->getCommandTag().' <instruction> [<options>]');

        $this->logDir = Kernel::getInstance()->configuration()->get('main.errors_log');
    }

    /**
     * Runs the command.
     *
     * @return void
     */
    protected function index()
    {
        $this->printTitle();

        $instruction = $this->input->getArgument('instruction');
        if ($instruction === null) {
            $this->printHelp();

            return 1;
        }

        $this->parameter = $this->input->getArgument('parameter');
        $this->argument = $this->input->getArgument('argument');

        $result = $this->instruction($instruction, false);
        if ($result > 0) {
            return $result;
        }

        return $this->getInstruction();
    }

    /**
     * Deletes an application error.
     *
     * @param string $file
     *
     * @return int
     */
    protected function delete(string $file): int
    {
        if (!is_file($file)) {
            $this->output->writeln('<error>CRC error not found.</>');

            return 1;
        }

        $deleted = unlink($file);
        if (!$deleted) {
            $this->output->writeln('<error>Can not delete the error.</>');

            return 1;
        }

        $this->output->writeln('<info>Error successfully deleted.</>');

        return 0;
    }

    /**
     * Delete all application errors.
     *
     * @return int
     */
    protected function deleteAll(): int
    {
        foreach (new DirectoryIterator($this->logDir) as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if ($this->delete($file->getPathname()) > 0) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * Deletes an application error.
     *
     * @return int
     */
    protected function doDelete(): int
    {
        if ($this->parameter === null) {
            $helper = new QuestionHelper();
            $question = new Question('What error to delete? CRC: ');
            $this->parameter = $helper->ask($this->input, $this->output, $question);

            if (!$this->parameter) {
                return 1;
            }
        }

        if ($this->parameter === 'all') {
            return $this->deleteAll();
        }

        return $this->delete($this->logDir.DS.$this->parameter.'.yml');
    }

    /**
     * Shows the list of application errors.
     *
     * @return int
     */
    protected function doList(): int
    {
        $table = new Table($this->output);
        $table->setStyle('box');
        $table->setHeaders([
            'CRC',
            'Qtty',
            // 'Date',
            'Code',
            'File',
            'Line',
        ]);
        $table->setColumnMaxWidth(0, 8);
        $table->setColumnMaxWidth(1, 4);
        $table->setColumnMaxWidth(2, 5);
        $table->setColumnMaxWidth(3, 45);
        $table->setColumnMaxWidth(4, 4);

        foreach (new DirectoryIterator($this->logDir) as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'yml') {
                continue;
            }

            $date = new DateTime();
            $date->setTimestamp($file->getMTime());

            $error = Yaml::parseFile($file->getPathname());

            $table->addRow([
                $file->getBasename('.yml'),
                sprintf('%4d', $error['occurrences']),
                // $date->format('y-m-d H:i'),
                $error['informations']['code'],
                $this->parsePath($error['informations']['file']),
                sprintf('%4d', $error['informations']['line']),
            ]);
        }

        $table->render();

        return 0;
    }

    /**
     * Gets the CRC if needed.
     *
     * @return int
     */
    protected function getCrc(): int
    {
        if (!in_array($this->parameter, self::SHOW_COMMAND)) {
            $this->argument = $this->parameter;
            $this->parameter = 'details';

            return 0;
        }

        if ($this->argument === null) {
            $helper = new QuestionHelper();
            $question = new Question('Enter CRC: ');
            $this->argument = $helper->ask($this->input, $this->output, $question);

            if (!$this->parameter) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * Show an error details.
     *
     * @return int
     */
    protected function doShow(): int
    {
        if ($this->parameter === null) {
            $helper = new QuestionHelper();
            $question = new Question('What to show? ');
            $question->setAutocompleterValues(self::SHOW_COMMAND);
            $this->parameter = $helper->ask($this->input, $this->output, $question);

            if (!$this->parameter) {
                return 1;
            }
        }

        if ($this->getCrc()) {
            return 1;
        }

        $file = $this->logDir.DS.$this->argument.'.yml';
        if (!is_file($file)) {
            $this->output->writeln('<error>CRC error not found.</>');

            return 1;
        }

        $error = Yaml::parseFile($file);
        $commands = [
            'cookie'  => 'showCookie',
            'debug'   => 'showDebug',
            'details' => 'showDetails',
            'get'     => 'showGet',
            'post'    => 'showPost',
            'server'  => 'showServer',
            'session' => 'showSession',
            'trace'   => 'showTrace',
        ];
        $func = $commands[$this->parameter];

        return call_user_func([$this, $func], $error);
    }

    /**
     * Gets the instruction from standard IO.
     *
     * @return int
     */
    protected function getInstruction(): int
    {
        if ($this->output->isQuiet() || !$this->input->isInteractive() || $this->parameter !== null) {
            return 0;
        }

        do {
            $this->parameter = null;
            $this->argument = null;

            $helper = new QuestionHelper();
            $question = new Question('instruction> ');
            $question->setAutocompleterValues([
                'delete',
                'exit',
                'list',
                'quit',
                'show',
                'sho cookie',
                'sho debug',
                'sho details',
                'sho get',
                'sho post',
                'sho server',
                'sho session',
                'sho trace',
                'show cookie',
                'show debug',
                'show details',
                'show get',
                'show post',
                'show server',
                'show session',
                'show trace',
            ]);
            $question->setNormalizer(function ($value) {
                return $value ? trim($value) : '';
            });
            $input = $helper->ask($this->input, $this->output, $question);

            $parts = explode(' ', $input);
            $instruction = $parts[0];
            $this->parameter = $parts[1] ?? null;
            $this->argument = $parts[2] ?? null;

            if ($instruction == 'exit' || $instruction == 'q' || $instruction == 'quit') {
                return 0;
            }

            $this->instruction($instruction, true);
        } while (true);
    }

    /**
     * Proccesses the instruction.
     *
     * @SuppressWarnings(CyclomaticComplexity)
     *
     * @param string $instruction
     * @param bool   $onlyInstructions
     *
     * @return int
     */
    protected function instruction(string $instruction = null, bool $onlyInstructions = false): int
    {
        switch ($instruction) {
            case 'd':
            case 'del':
            case 'delete':
                return $this->doDelete();
            case '?':
            case 'h':
            case 'help':
                return $this->printHelp($onlyInstructions);
            case 'l':
            case 'list':
                return $this->doList();
            case 's':
            case 'sho':
            case 'show':
                return $this->doShow();
        }

        $this->output->writeln([
            sprintf('Invalid instruction <error>%s</>', $instruction),
            '',
        ]);
        $this->printHelp($onlyInstructions);

        return 1;
    }

    /**
     * Parses the path and returns shorten form.
     *
     * @param string $path
     *
     * @return string
     */
    protected function parsePath(string $path): string
    {
        $mea = explode(DS, __DIR__);
        $hea = explode(DS, $path);

        do {
            if ($mea[0] != $hea[0]) {
                return implode(DS, $hea);
            }

            array_shift($mea);
            array_shift($hea);
        } while (count($hea) && count($mea));

        return implode(DS, $hea);
    }

    /**
     * Shows help message.
     *
     * @return void
     */
    protected function printHelp(bool $onlyInstructions = false)
    {
        if ($onlyInstructions) {
            return $this->printInstructions();
        }

        $this->setHelp([
            'Usage:',
            '  '.$this->getCommandTag().' [<instruction>] [<options>]',
            '',
            'Instructions:',
            '  delete <CRC>|all  Delete an application error or all errors.',
            '  list              Display the list of application errors.',
            '  show <parameter>  Display an application error details.',
            '',
            'Instruction show parameters:',
            '  details <CRC>  Display the error details.',
            '  post <CRC>     Display content of the $_POST var.',
            '  server <CRC>   Display content of the $_SERVER var.',
            '  session <CRC>  Display content of the $_SESSION var.',
            '  trace <CRC>    Display the stack trace.',
            '',
        ]);
        $this->output->writeln($this->getProcessedHelp());
        $this->printOptions();

        return 0;
    }

    /**
     * Prints reduced error informations.
     *
     * @param array $error
     *
     * @return void
     */
    protected function printInfo(array $error)
    {
        $this->output->writeln([
            sprintf('Error ID: <info>%s</>', $this->argument),
            sprintf('Code:     <comment>%s</>', $error['informations']['code'] ?? ''),
            sprintf('Message:  <info>%s</>', $error['informations']['message'] ?? ''),
            '',
        ]);
    }

    /**
     * Shows help message.
     *
     * @return void
     */
    protected function printInstructions()
    {
        $this->setHelp([
            'Instructions:',
            '  delete  Delete an application error.',
            '  list    Display the list of application errors.',
            '  show    Display an application error details.',
            '  exit    Exit to terminal.',
            '',
        ]);
        $this->output->writeln($this->getProcessedHelp());

        return 0;
    }

    /**
     * Prints array variables.
     *
     * @param array $array
     * @param int   $indent
     *
     * @return void
     */
    protected function printVar(array $array, $indent = 1)
    {
        foreach ($array as $var => $value) {
            $spaces = str_repeat('  ', $indent);

            if (is_array($value)) {
                $this->output->writeln($spaces.sprintf('<info>%s</> => <comment>[</>', $var));
                $this->printVar($value, $indent + 1);
                $this->output->writeln($spaces.'<comment>]</>,');

                continue;
            }

            $this->output->writeln($spaces.sprintf('<info>%s</>: %s',
                $var,
                $value
            ));
        }
    }

    /**
     * Shows the error $_COOKIE var content.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showCookie(array $error): int
    {
        $this->printInfo($error);
        $this->output->writeln('<comment>$_COOKIE:</>');
        $this->printVar($error['php_vars']['cookie']);
        $this->output->writeln('');

        return 0;
    }

    /**
     * Shows the debug data.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showDebug(array $error): int
    {
        $this->printInfo($error);
        $this->output->writeln('<comment>Debug data:</>');
        $this->printVar($error['debug']);
        $this->output->writeln('');

        return 0;
    }

    /**
     * Shows the error details.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showDetails(array $error): int
    {
        $this->output->writeln([
            sprintf('Error ID:     <info>%s</>', $this->argument),
            sprintf('Occurrences:  <info>%d</>', $error['occurrences'] ?? ''),
            sprintf('Last:         <info>%s</>', $error['date'] ?? ''),
            '',
            '<comment>Error Informations:</>',
            sprintf('  Code:       <info>%s</>', $error['informations']['code'] ?? ''),
            sprintf('  File:       <info>%s</>', $error['informations']['file'] ?? 'unknow'),
            sprintf('  Line:       <info>%d</>', $error['informations']['line'] ?? ''),
            sprintf('  Message:    <info>%s</>', $error['informations']['message'] ?? ''),
            sprintf('  First time: <info>%s</>', $error['informations']['first'] ?? ''),
            sprintf('  System:     <info>%s</>', $error['informations']['uname'] ?? ''),
            sprintf('  Safe mode:  <info>%s</>', $error['informations']['safe_mode'] ?? ''),
            sprintf('  Interface:  <info>%s</>', $error['informations']['sapi_name'] ?? ''),
            '',
            '<comment>Request:</>',
            sprintf('  Host:       <info>%s</>', $error['request']['host'] ?? ''),
            sprintf('  URI:        <info>%s</>', $error['request']['uri'] ?? ''),
            sprintf('  Method:     <info>%s</>', $error['request']['method'] ?? ''),
            sprintf('  Protocol:   <info>%s</>', $error['request']['protocol'] ?? ''),
            sprintf('  Secure:     <info>%s</>', $error['request']['secure'] ?? ''),
            '',
            '<comment>Client:</>',
            sprintf('  Address:    <info>%s</>', $error['client']['address'] ?? ''),
            sprintf('  Reverse:    <info>%s</>', $error['client']['reverse'] ?? ''),
            sprintf('  Referrer:   <info>%s</>', $error['client']['referrer'] ?? ''),
            sprintf('  User-agent: <info>%s</>', $error['client']['user_agent'] ?? ''),
            '',
            '<comment>Variables (data quantity):</>',
            sprintf('  $_GET: <info>%d</>  $_POST: <info>%d</>  $_SESSION: <info>%d</>  $_COOKIE: <info>%d</>',
                count($error['php_vars']['get']),
                count($error['php_vars']['post']),
                count($error['php_vars']['session']),
                count($error['php_vars']['cookie'])
            ),
            '',
        ]);

        return 0;
    }

    /**
     * Shows the error $_GET var content.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showGet(array $error): int
    {
        $this->printInfo($error);
        $this->output->writeln('<comment>$_GET:</>');
        $this->printVar($error['php_vars']['get']);
        $this->output->writeln('');

        return 0;
    }

    /**
     * Shows the error $_POST var content.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showPost(array $error): int
    {
        $this->printInfo($error);
        $this->output->writeln('<comment>$_POST:</>');
        $this->printVar($error['php_vars']['post']);
        $this->output->writeln('');

        return 0;
    }

    /**
     * Shows the error $_SERVER var content.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showServer(array $error): int
    {
        $this->printInfo($error);
        $this->output->writeln('<comment>$_SERVER:</>');
        $this->printVar($error['php_vars']['server']);
        $this->output->writeln('');

        return 0;
    }

    /**
     * Shows the error $_SESSION var content.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showSession(array $error): int
    {
        $this->printInfo($error);
        $this->output->writeln('<comment>$_SESSION:</>');
        $this->printVar($error['php_vars']['session']);
        $this->output->writeln('');

        return 0;
    }

    /**
     * Shows the error stack trace.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showTrace(array $error): int
    {
        $this->printInfo($error);
        $this->output->writeln('Stack trace:');

        foreach ($error['trace'] as $index => $trace) {
            $this->output->writeln(sprintf('  <info>%4d</>: %s: <comment>%d</>',
                $index,
                $trace['file'] ?? (($trace['class'] ?? '').($trace['type'] ?? '').($trace['funcion'] ?? '').'()'),
                $trace['line'] ?? ''
            ));
        }

        $this->output->writeln('');

        return 0;
    }
}
