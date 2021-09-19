<?php

/**
 * Errors console command.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Console;

use DateTime;
use DirectoryIterator;
use Springy\Core\Configuration;
use stdClass;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

/**
 * Errors console command controller.
 *
 * @SuppressWarnings(PHPMD.CountInLoopExpression)
 */
class ErrorsCommand extends Controller
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Application errors';

    /** @var array the list of instructions */
    protected $commandInstructions;
    /** @var bool comming from cli interface */
    protected $commingCli;
    /** @var string the errors log directory */
    protected $logDir;
    /** @var stdClass|null the instruction */
    protected $instruction;
    /** @var string parameter to the instruction */
    protected $parameter;
    /** @var string error crc */
    protected $crc;
    /** @var bool stay into interactive mode */
    protected $stayInteractive;

    /**
     * Adds an instruction to instructions list.
     *
     * @param string $name
     * @param string $function
     * @param string $parameter
     * @param string $description
     * @param bool   $onlyInteractive
     * @param string $alias
     * @param bool   $hidden
     * @param bool   $terminator
     *
     * @return void
     */
    protected function addInstruction(
        string $name,
        string $function,
        string $parameter,
        bool $showInfo,
        string $description,
        bool $onlyInteractive = false,
        string $alias = '',
        bool $hidden = false,
        bool $terminator = false
    ) {
        $this->commandInstructions[$name] = (object) [
            'description'     => $description,
            'caller'          => $function,
            'parameter'       => $parameter,
            'showInfo'        => $showInfo,
            'onlyInteractive' => $onlyInteractive,
            'alias'           => $alias,
            'hidden'          => $hidden,
            'terminator'      => $terminator,
        ];
    }

    /**
     * Configures the command.
     *
     * @SuppressWarnings(ExcessiveMethodLength)
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->addArgument('instruction', InputArgument::OPTIONAL, 'Instruction.');
        $this->addArgument('crc', InputArgument::OPTIONAL, 'Error CRC.');
        $this->addUsage('<instruction> [<options>]');

        $this->logDir = Configuration::getInstance()->get('main.errors_log');

        $crcStr = '<CRC>';
        $this->addInstruction('cookie', 'showCookie', $crcStr, true, 'Display the $_COOKIE var content for the error.');
        $this->addInstruction('debug', 'showDebug', $crcStr, true, 'Display the debug content for the error.');
        $this->addInstruction('delete', 'doDelete', $crcStr . '|all', false, 'Delete one or all application errors.');
        $this->addInstruction('details', 'showDetails', $crcStr, false, 'Display the details for the error.', false, 'show');
        $this->addInstruction('exit', '', '', false, 'Exit from interactive mmode.', true, 'quit', false, true);
        $this->addInstruction('get', 'showGet', $crcStr, true, 'Display the $_GET var content for the error.');
        $this->addInstruction('help', 'printHelp', '', false, 'Display this help messagem.', false, '?');
        $this->addInstruction('list', 'doList', '', false, 'Display the list of application errors.');
        $this->addInstruction('post', 'showPost', $crcStr, true, 'Display the $_POST var content for the error.');
        $this->addInstruction('server', 'showServer', $crcStr, true, 'Display the $_SERVER var content for the error.');
        $this->addInstruction('session', 'showSession', $crcStr, true, 'Display the $_SESSION var content for the error.');
        $this->addInstruction('trace', 'showTrace', $crcStr, true, 'Display the stack trace content for the error.');
    }

    /**
     * Runs the command.
     *
     * @return void
     */
    protected function index()
    {
        $this->printTitle();

        $receivedInstruction = $this->input->getArgument('instruction');

        if (is_null($receivedInstruction)) {
            $this->printHelp();

            return 1;
        }

        $this->commingCli = true;
        $this->getInstruction($receivedInstruction);
        $this->crc = $this->input->getArgument('crc');
        $this->stayInteractive = !$this->output->isQuiet() && $this->input->isInteractive();

        $result = $this->runInstruction();
        if ($result > 0) {
            return $result;
        }

        return $this->inputInstruction();
    }

    /**
     * Deletes an application error.
     *
     * @param string $file
     * @param bool   $silent
     *
     * @return int
     */
    protected function delete(string $file, bool $silent = false): int
    {
        $deleted = unlink($file);
        if (!$deleted) {
            $this->output->writeln('<error>Can not delete the error.</>');

            return 1;
        }

        if (!$silent) {
            $this->output->writeln('<info>Error successfully deleted.</>');
        }

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
            if (!$file->isFile() || $file->getExtension() !== 'yml') {
                continue;
            }

            if ($this->delete($file->getPathname(), true) > 0) {
                return 1;
            }
        }

        $this->output->writeln('<info>All errors successfully deleted.</>');

        return 0;
    }

    /**
     * Deletes one or all application errors.
     *
     * @return int
     */
    protected function doDelete(): int
    {
        if ($this->crc === 'all') {
            return $this->deleteAll();
        }

        return $this->delete($this->logDir . DS . $this->crc . '.yml');
    }

    /**
     * Shows the list of application errors.
     *
     * @return void
     */
    protected function doList(): void
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
    }

    /**
     * Gets the CRC from default IO.
     *
     * @return void
     */
    protected function getCrc(): void
    {
        if (is_null($this->crc)) {
            $helper = new QuestionHelper();
            $question = new Question('Enter CRC: ');
            $this->crc = $helper->ask($this->input, $this->output, $question);
        }
    }

    /**
     * Gets the error crc.
     *
     * @return array|bool
     */
    protected function getError()
    {
        $this->getCrc();

        if (!$this->crc) {
            return false;
        } elseif ($this->instruction->caller === 'doDelete' && $this->crc === 'all') {
            return true;
        }

        return $this->loadErrorYml();
    }

    /**
     * Finds the instruction object by given name and sets internal property.
     *
     * @param string $name
     *
     * @return void
     */
    protected function getInstruction(string $name = null): void
    {
        $this->instruction = null;

        foreach ($this->commandInstructions as $instruction => $obj) {
            if ($instruction === $name || $obj->alias === $name) {
                $this->instruction = $obj;

                return;
            }
        }

        $this->output->writeln([
            sprintf('Invalid instruction <error>%s</>', $name),
            '',
        ]);
    }

    /**
     * Gets the list os instruction names.
     *
     * @return array
     */
    protected function getInstructionsKeys(): array
    {
        $instructions = [];
        foreach ($this->commandInstructions as $key => $obj) {
            $instructions[] = $key;
            if ($obj->alias) {
                $instructions[] = $obj->alias;
            }
        }

        return $instructions;
    }

    /**
     * Gets requested parameter if needed.
     *
     * @return array|bool
     */
    protected function getParameter()
    {
        if (empty($this->instruction->parameter)) {
            return true;
        }

        $error = $this->getError();

        if ($error === false) {
            return false;
        } elseif ($this->instruction->showInfo) {
            $this->printInfo($error);
        }

        return $error;
    }

    /**
     * Gets the instruction from standard IO.
     *
     * @return int
     */
    protected function inputInstruction(): int
    {
        while ($this->stayInteractive) {
            $this->commingCli = false;
            $this->crc = null;

            $helper = new QuestionHelper();
            $question = new Question('instruction> ');
            $question->setAutocompleterValues($this->getInstructionsKeys());
            $question->setNormalizer(function ($value) {
                return $value ? trim($value) : '';
            });
            $input = $helper->ask($this->input, $this->output, $question);

            if (!$input) {
                continue;
            }

            $parts = explode(' ', $input);

            $this->getInstruction($parts[0]);
            $this->crc = $parts[1] ?? null;

            if (!is_null($this->instruction) && $this->instruction->terminator) {
                break;
            }

            $this->runInstruction();
        }

        return 0;
    }

    /**
     * Gets the error data.
     *
     * @return array|bool
     */
    protected function loadErrorYml()
    {
        $file = $this->logDir . DS . $this->crc . '.yml';

        if (!is_file($file)) {
            $this->output->writeln(
                '<error>Error identified by <comment>'
                . $this->crc
                . '</> CRC not found.</>'
            );

            return false;
        }

        return Yaml::parseFile($file);
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
    protected function printHelp()
    {
        if ($this->commingCli) {
            $this->stayInteractive = false;
            $this->setHelp([
                'Usage:',
                '  ' . $this->getCommandTag() . ' [<instruction>] [<options>]',
                '',
            ]);
            $this->output->writeln($this->getProcessedHelp());
        }

        $table = new Table($this->output);
        $table->setStyle('compact');
        foreach ($this->commandInstructions as $name => $obj) {
            if (!$obj->hidden && (!$this->commingCli || !$obj->onlyInteractive)) {
                $table->addRow([
                    ' ' . $name . ($obj->alias ? '|' . $obj->alias : '') . ' ' . $obj->parameter,
                    ' ' . $obj->description,
                ]);
            }
        }

        $this->output->writeln('Instructions:');
        $table->render();
        $this->output->writeln('');

        if ($this->commingCli) {
            $this->printOptions();
        }

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
            sprintf('Error ID: <info>%s</>', $this->crc),
            sprintf('Code:     <comment>%s</>', $error['informations']['code'] ?? ''),
            sprintf('Message:  <info>%s</>', $error['informations']['message'] ?? ''),
            '',
        ]);
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
                $this->output->writeln($spaces . sprintf('<info>%s</> => <comment>[</>', $var));
                $this->printVar($value, $indent + 1);
                $this->output->writeln($spaces . '<comment>]</>,');

                continue;
            }

            $this->output->writeln($spaces . sprintf(
                '<info>%s</>: %s',
                $var,
                $value
            ));
        }
    }

    /**
     * Processes the instruction.
     *
     * @return int
     */
    protected function runInstruction(): int
    {
        if (is_null($this->instruction)) {
            return 1;
        }

        $error = $this->getParameter();

        if ($error === false) {
            return 1;
        }

        $result = call_user_func([$this, $this->instruction->caller], $error);
        $this->output->writeln('');

        return is_int($result) ? $result : 0;
    }

    /**
     * Shows the error $_COOKIE var content.
     *
     * @param array $error
     *
     * @return void
     */
    protected function showCookie(array $error): void
    {
        $this->output->writeln('<comment>$_COOKIE:</>');
        $this->printVar($error['php_vars']['cookie']);
    }

    /**
     * Shows the debug data.
     *
     * @param array $error
     *
     * @return void
     */
    protected function showDebug(array $error): void
    {
        $this->output->writeln('<comment>Debug data:</>');
        $this->printVar($error['debug']);
    }

    /**
     * Shows the error details.
     *
     * @param array $error
     *
     * @return void
     */
    protected function showDetails(array $error): void
    {
        $this->output->writeln([
            sprintf('Occurrences:  <info>%d</>', $error['occurrences'] ?? ''),
            sprintf('Last:         <info>%s</>', $error['date'] ?? ''),
            '',
        ]);
        $this->output->writeln([
            '<comment>Error Informations:</>',
            sprintf('  File:       <info>%s</>', $error['informations']['file'] ?? 'unknow'),
            sprintf('  Line:       <info>%d</>', $error['informations']['line'] ?? ''),
            sprintf('  First time: <info>%s</>', $error['informations']['first'] ?? ''),
            sprintf('  System:     <info>%s</>', $error['informations']['uname'] ?? ''),
            sprintf('  Safe mode:  <info>%s</>', $error['informations']['safe_mode'] ?? ''),
            sprintf('  Interface:  <info>%s</>', $error['informations']['sapi_name'] ?? ''),
            '',
        ]);
        $this->output->writeln([
            '<comment>Request:</>',
            sprintf('  Host:       <info>%s</>', $error['request']['host'] ?? ''),
            sprintf('  URI:        <info>%s</>', $error['request']['uri'] ?? ''),
            sprintf('  Method:     <info>%s</>', $error['request']['method'] ?? ''),
            sprintf('  Protocol:   <info>%s</>', $error['request']['protocol'] ?? ''),
            sprintf('  Secure:     <info>%s</>', $error['request']['secure'] ?? ''),
            '',
        ]);
        $this->output->writeln([
            '<comment>Client:</>',
            sprintf('  Address:    <info>%s</>', $error['client']['address'] ?? ''),
            sprintf('  Reverse:    <info>%s</>', $error['client']['reverse'] ?? ''),
            sprintf('  Referrer:   <info>%s</>', $error['client']['referrer'] ?? ''),
            sprintf('  User-agent: <info>%s</>', $error['client']['user_agent'] ?? ''),
            '',
        ]);
        $this->output->writeln([
            '<comment>Variables (data quantity):</>',
            sprintf(
                '  $_GET: <info>%d</>  $_POST: <info>%d</>  $_SESSION: <info>%d</>  $_COOKIE: <info>%d</>',
                count($error['php_vars']['get']),
                count($error['php_vars']['post']),
                count($error['php_vars']['session']),
                count($error['php_vars']['cookie'])
            ),
        ]);
    }

    /**
     * Shows the error $_GET var content.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showGet(array $error): void
    {
        $this->output->writeln('<comment>$_GET:</>');
        $this->printVar($error['php_vars']['get']);
    }

    /**
     * Shows the error $_POST var content.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showPost(array $error): void
    {
        $this->output->writeln('<comment>$_POST:</>');
        $this->printVar($error['php_vars']['post']);
    }

    /**
     * Shows the error $_SERVER var content.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showServer(array $error): void
    {
        $this->output->writeln('<comment>$_SERVER:</>');
        $this->printVar($error['php_vars']['server']);
    }

    /**
     * Shows the error $_SESSION var content.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showSession(array $error): void
    {
        $this->output->writeln('<comment>$_SESSION:</>');
        $this->printVar($error['php_vars']['session']);
    }

    /**
     * Shows the error stack trace.
     *
     * @param array $error
     *
     * @return int
     */
    protected function showTrace(array $error): void
    {
        $this->output->writeln('Stack trace:');

        foreach ($error['trace'] as $index => $trace) {
            $this->output->writeln(sprintf(
                '  <info>%4d</>: %s: <comment>%d</>',
                $index,
                $trace['file'] ?? (($trace['class'] ?? '') . ($trace['type'] ?? '') . ($trace['funcion'] ?? '') . '()'),
                $trace['line'] ?? ''
            ));
        }
    }
}
