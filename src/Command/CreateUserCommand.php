<?php

namespace App\Command;

use App\Service\UserService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates an user on the public admin.')
            ->setHelp('Use this command to create an user on Hedgebot\'s public admin.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws ORMException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $credentials = $this->askForCredentials($input, $output);

        // Creating the user
        $output->write("Creating user...");

        if (!$this->userService->create($credentials)) {
            $output->writeln("<error>Failed</error>");
            $errors = $this->userService->getErrors();
            foreach ($errors as $error) {
                $output->writeln($error);
            }
            return 134;
        }

        $output->writeln("<info>OK</info>");
        return 0;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function askForCredentials(InputInterface $input, OutputInterface $output): array
    {
        $helper = $this->getHelper('question');

        $loginQuestion = new Question("<question>Username (admin):</question> ", "admin");
        $passwordQuestion = new Question("<question>Password:</question> ");
        $passwordConfirmQuestion = new Question("<question>Confirm password:</question> ");

        $passwordQuestion->setHidden(true);
        $passwordConfirmQuestion->setHidden(true);

        $output->writeln(["Enter new user's credentials below.", ""]);

        $login = $helper->ask($input, $output, $loginQuestion);
        $password = $helper->ask($input, $output, $passwordQuestion);
        $passwordConfirmation = $helper->ask($input, $output, $passwordConfirmQuestion);

        return [
            "username" => $login,
            "password" => $password,
            "password_confirmation" => $passwordConfirmation
        ];
    }
}
