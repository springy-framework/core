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
     * Show an error details.
     *
     * @return int
     */
    protected function doShow(bool $trace = false): int
    {
        if ($this->parameter === null) {
            $helper = new QuestionHelper();
            $question = new Question('What error to show? CRC: ');
            $this->parameter = $helper->ask($this->input, $this->output, $question);

            if (!$this->parameter) {
                return 1;
            }
        }

        $file = $this->logDir.DS.$this->parameter.'.yml';
        if (!is_file($file)) {
            $this->output->writeln('<error>CRC error not found.</>');

            return 1;
        }

        $error = Yaml::parseFile($file);
        $this->showError($error, $trace);

        return 0;
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

            $helper = new QuestionHelper();
            $question = new Question('instruction> ');
            $question->setAutocompleterValues(['delete', 'exit', 'list', 'quit', 'show', 'trace']);
            $question->setNormalizer(function ($value) {
                return $value ? trim($value) : '';
            });
            $input = $helper->ask($this->input, $this->output, $question);

            $parts = explode(' ', $input);
            $instruction = $parts[0];
            $this->parameter = $parts[1] ?? null;

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
                return $this->doShow(false);
            case 't':
            case 'tra':
            case 'trace':
                return $this->doShow(true);
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
    protected function printInstructions()
    {
        $this->setHelp([
            'Instructions:',
            '  delete  Delete an application error.',
            '  list    Display the list of application errors.',
            '  show    Display an application error details.',
            '  trace   Display an application error stack trace.',
            '  exit    Exit to terminal.',
            '',
        ]);
        $this->output->writeln($this->getProcessedHelp());

        return 0;
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
            '  delete [CRC]  Delete an application error.',
            '  list          Display the list of application errors.',
            '  show [CRC]    Display an application error details.',
            '  trace [CRC]   Display an application error stack trace.',
            '',
        ]);
        $this->output->writeln($this->getProcessedHelp());
        $this->printOptions();

        return 0;
    }

    /**
     * Shows the error details.
     *
     * @param array $error
     * @param bool  $trace
     *
     * @return void
     */
    protected function showError(array $error, bool $trace = false)
    {
        if ($trace) {
            $this->output->writeln([
                sprintf('Error ID: <info>%s</>', $this->parameter),
                sprintf('Code:     <comment>%s</>', $error['informations']['code'] ?? ''),
                sprintf('Message:  <info>%s</>', $error['informations']['message'] ?? ''),
                '',
                'Stack trace:',
            ]);

            foreach ($error['trace'] as $index => $trace) {
                $this->output->writeln(sprintf('  <info>%4d</>: %s: <comment>%d</>',
                    $index,
                    $trace['file'] ?? (($trace['class'] ?? '').($trace['type'] ?? '').($trace['funcion'] ?? '').'()'),
                    $trace['line'] ?? ''
                ));
            }

            $this->output->writeln('');

            return;
        }

        $this->output->writeln([
            sprintf('Error ID:     <info>%s</>', $this->parameter),
            sprintf('Occurrences:  <info>%d</>', $error['occurrences'] ?? ''),
            sprintf('Last:         <info>%s</>', $error['date'] ?? ''),
            '',
            '<comment>Error Informations:</>',
            sprintf('  Code:       <info>%s</>', $error['informations']['code'] ?? ''),
            sprintf('  File:       <info>%s</>', $error['informations']['file'] ?? 'unknow'),
            sprintf('  Line:       <info>%d</>', $error['informations']['line'] ?? ''),
            sprintf('  Message:    <info>%s</>', $error['informations']['message'] ?? ''),
            sprintf('  First time: <info>%s</>', $error['informations']['first'] ?? ''),
            '',
            '<comment>Request:</>',
            sprintf('  URI:        <info>%s</>', $error['request']['uri'] ?? ''),
            sprintf('  Method:     <info>%s</>', $error['request']['method'] ?? ''),
            sprintf('  Protocol:   <info>%s</>', $error['request']['protocol'] ?? ''),
            '',
            '<comment>Client:</>',
            sprintf('  Address:    <info>%s</>', $error['client']['address'] ?? ''),
            sprintf('  Reverse:    <info>%s</>', $error['client']['reverse'] ?? ''),
            sprintf('  Referrer:   <info>%s</>', $error['client']['referrer'] ?? ''),
            sprintf('  User-agent: <info>%s</>', $error['client']['user_agent'] ?? ''),
            '',
        ]);
    }
}
