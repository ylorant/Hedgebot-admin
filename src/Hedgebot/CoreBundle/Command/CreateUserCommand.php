<?php
namespace Hedgebot\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Hedgebot\CoreBundle\Entity\User;

class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Creates an user on web admin.')
            ->setHelp('Use this command to create an user on Hedgebot\'s web admin.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $credentials = $this->askForCredentials($input, $output);

        // Check if database exists
        $databasePath = $this->getContainer()->getParameter('database_path');

        if(!is_file($databasePath))
        {
            $output->writeln(["", "Notice: Database has not been found. Creating it.", ""]);
            $output->write("Creating database...");

            $command = $this->getApplication()->find('doctrine:database:create');
            $command->run(new ArrayInput(['command' => 'doctrine:database:create']), new NullOutput());

            // Updating schema (tables)
            $output->writeln("OK");
            $output->write("Creating schema...");

            $command = $this->getApplication()->find('doctrine:schema:update');
            $command->run(new ArrayInput(['command' => 'doctrine:schema:update', '--force' => true]), new NullOutput());

            $output->writeln("OK");
        }

        // Creating the user
        $output->write("Creating user...");

        $user = new User();
        $user->setUsername($credentials["username"]);

        // Hashing the password using the password_encoder
        $encoder = $this->getContainer()->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword($user, $credentials["password"]);

        $user->setPassword($encodedPassword);
        $user->setRoles(["ROLE_USER"]);

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $output->writeln("OK");
    }


    protected function askForCredentials(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $loginQuestion = new Question("Username (admin): ", "admin");
        $passwordQuestion = new Question("Password: ");
        $passwordConfirmQuestion = new Question("Confirm password: ");

        $passwordQuestion->setHidden(true);
        $passwordConfirmQuestion->setHidden(true);

        $output->writeln(["Enter new user's credentials below.", ""]);

        $password = null;
        $login = $helper->ask($input, $output, $loginQuestion);

        while(true)
        {
            $password = $helper->ask($input, $output, $passwordQuestion);
            $passwordConfirmation = $helper->ask($input, $output, $passwordConfirmQuestion);

            // Exit the loop when the passwords are good
            if($password == $passwordConfirmation)
                break;
            else
            {
                // Passwords are incorrect, show the error and loop
                $output->writeln([
                    "The password do not match.",
                    ""
                ]);
            }
        }

        return [
            "username" => $login,
            "password" => $password
        ];
    }
}
