<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use App\Entity\User;
use RuntimeException;
use Doctrine\Bundle\DoctrineBundle\Registry;

class ListUsersCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('user:list')
            ->setDescription('Lists users registered on the public admin.')
            ->setHelp('Use this command to list currently registered users on Hedgebot\'s public admin.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        // Check if database exists
        $databasePath = $this->getContainer()->getParameter('database_path');

        if (!is_file($databasePath)) {
            throw new RuntimeException("Database does not exist. Create an user first to create the database.");
        }

        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $repository = $doctrine->getRepository(User::class);

        $userList = $repository->findAll();

        /** @var User $user */
        foreach ($userList as $user) {
            $output->writeln($user->getUsername(). ": ". join(', ', $user->getRoles()));
        }
    }
}
