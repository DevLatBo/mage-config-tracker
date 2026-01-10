<?php

namespace Devlat\Settings\Console;

use Devlat\Settings\Model\Service\Management;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CleanUp extends Command
{

    /**
     * @var Management
     */
    private Management $management;

    /**
     * Constructor.
     * @param Management $management
     * @param string|null $name
     */
    public function __construct(
        Management $management,
        ?string $name = null
    )
    {
        $this->management = $management;
        parent::__construct($name);
    }

    /**
     * Configure the command.
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('devlat:settings:cleanup');
        $this->setDescription('Execute this command in case you are about to remove the module config tracker.');

        parent::configure();
    }

    /**
     * Command Logic for the drop table process.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting cleaning up tracker table process.</info>');

        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion(
            '<question>This will delete all the data from devlat_setting_tracker from the database. Are you sure you want to proceed? (y/n)</question>',
            false,
            '/^(y|yes)/i'
        );
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<error>Operation Cancelled.</error>');
            return Command::SUCCESS;
        }


        $this->management->dropTable();
        $output->writeln('<info>Cleaning up tracker table process has ended succesfully.</info>');
        return Command::SUCCESS;
    }
}
