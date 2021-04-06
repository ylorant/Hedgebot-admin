<?php

namespace App\Command;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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
        $roles = $this->askForRoles($input, $output);

        // Creating the user
        $output->write("Creating user...");

        $user = new User();
        $user->setUsername($credentials['username']);
        $user->setPlainPassword($credentials['password']);
        $user->setRoles($roles);

        if (!$this->userService->create($user)) {
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

        $output->writeln(["Enter new user's credentials below.", ""]);

        $loginQuestion = new Question("<question>Username (admin):</question> ", "admin");
        $passwordQuestion = new Question("<question>Password:</question> ");
        $passwordQuestion->setHidden(true);

        $login = $helper->ask($input, $output, $loginQuestion);
        $password = $helper->ask($input, $output, $passwordQuestion);

        $passwordConfirmQuestion = new Question("<question>Confirm password:</question> ");
        $passwordConfirmQuestion->setValidator(function ($value) use ($password) {
            if (trim($value) !== $password) {
                throw new Exception('The confirmation does not match with password.');
            }

            return $value;
        });
        $passwordConfirmQuestion->setHidden(true);

        $passwordConfirmation = $helper->ask($input, $output, $passwordConfirmQuestion);

        return [
            "username" => $login,
            "password" => $password,
            "password_confirmation" => $passwordConfirmation
        ];
    }

    /**
     * @TODO find a way to ask roles like web interface (multi-select depends of predefined list)
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function askForRoles(InputInterface $input, OutputInterface $output): array
    {
        $roles = [];
        $helper = $this->getHelper('question');

        $rolesQuestion = new ConfirmationQuestion(
            "<question>This user must be an admin user ? (n)</question> ",
            false
        );

        if ($helper->ask($input, $output, $rolesQuestion)) {
            $roles[] = User::ROLE_ADMIN;
        }

        return $roles;
    }
}
