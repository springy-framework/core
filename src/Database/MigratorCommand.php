<?php
/**
 * Database migration console command.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database;

use Springy\Database\Migration\Migrator;
use Springy\Console\Controller;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;

class MigratorCommand extends Controller
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database migrator command';
    /** @var string database configurarion name */
    protected $database;
    /** @var string the version target */
    protected $revTarget;

    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->addArgument('instruction', InputArgument::OPTIONAL, 'The migrate instruction.');

        $this->addOption('revision', 'r', InputOption::VALUE_OPTIONAL, 'The target revision.');
        $this->addOption('database', 'd', InputOption::VALUE_OPTIONAL, 'Database name.');

        $this->addUsage('%command.full_name% <instruction> [<options>]');
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

        $this->database = $this->input->getParameterOption('database', null);
        $this->revTarget = $this->input->getParameterOption('revision', null);

        switch ($instruction) {
            case 'migrate':
                return $this->doMigrate();
            case 'rollback':
                return $this->doRollback();
            case 'status':
                return $this->doStatus();
        }

        $this->output->writeln([
            sprintf('<error>Invalid instruction %s</>', $instruction),
            '',
        ]);
        $this->printHelp();

        return 1;
    }

    /**
     * Shows help message.
     *
     * @return void
     */
    protected function printHelp()
    {
        $this->setHelp([
            'Usage:',
            '  %command.full_name% migrate|rollback|status [<options>]',
            '',
            'Instructions:',
            '  migrate   Install database migrations.',
            '  rollback  Rollback database migrations.',
            '  status    Show database migration status.',
            '',
        ]);

        $this->output->writeln($this->getProcessedHelp());
        $this->printOptions();
    }

    /**
     * Runs migrate instruction.
     *
     * @return int
     */
    protected function doMigrate(): int
    {
        $migrator = new Migrator($this->database);

        $revisionsQtt = $migrator->countRevisionsUntil($this->revTarget);
        if ($revisionsQtt === 0) {
            $this->output->writeln('<error>Nothing to do.</>');

            return 0;
        }

        $this->output->writeln([
            sprintf('Applying <info>%d</> revision(s)...', $revisionsQtt),
            '',
        ]);

        $progress = new ProgressBar($this->output, $revisionsQtt);
        $progress->start();

        $done = $migrator->migrate($this->revTarget, function ($quantity) use ($progress) {
            $progress->setProgress($quantity);
        });

        $progress->clear();
        $this->output->writeln([
            sprintf('<info>%d</> revision(s) applied.', $done),
            '',
        ]);

        if ($done < $revisionsQtt) {
            $this->output->writeln(
                sprintf('<error>Error:</> %s', $migrator->getError())
            );

            return 1;
        }

        $this->output->writeln(['<info>Done.</>']);

        return 0;
    }

    /**
     * Runs rollback instruction.
     *
     * @return integer
     */
    protected function doRollback(): int
    {
        $migrator = new Migrator($this->database);

        $revisionsQtt = $migrator->countRollbackUntil($this->revTarget);
        if ($revisionsQtt === 0) {
            $this->output->writeln('<error>Nothing to do.</>');

            return 0;
        }

        $this->output->writeln([
            sprintf('Rolling back <info>%d</> revision(s)...', $revisionsQtt),
            '',
        ]);

        $progress = new ProgressBar($this->output, $revisionsQtt);
        $progress->start();

        $done = $migrator->rollback($this->revTarget, function ($quantity) use ($progress) {
            $progress->setProgress($quantity);
        });

        $progress->clear();
        $this->output->writeln([
            sprintf('<info>%d</> revision(s) rolled back.', $done),
            '',
        ]);

        if ($done < $revisionsQtt) {
            $this->output->writeln(
                sprintf('<error>Error:</> %s', $migrator->getError())
            );

            return 1;
        }

        $this->output->writeln(['<info>Done.</>']);

        return 0;
    }

    /**
     * Shows migration status.
     *
     * @return int
     */
    protected function doStatus(): int
    {
        $migrator = new Migrator($this->database);

        $this->output->writeln([
            sprintf('Applied revisions:         <info>%d</>', $migrator->getAppliedRevisionsCount()),
            sprintf('Revisions not applied yet: <info>%d</>', $migrator->getNotAppliedRevisionsCount()),
        ]);

        if ($migrator->getNotAppliedRevisionsCount() === 0) {
            $this->output->writeln([
                '',
                '<info>The database is up to date. No revisions to be applied.</>',
            ]);
        }

        return 0;
    }
}
