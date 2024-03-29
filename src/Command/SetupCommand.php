<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\ApiClientService;
use App\Exception\RPCException;

class SetupCommand extends Command
{
    protected static $defaultName = 'app:setup';

    protected const REQUIRED_EXTENSIONS = ["pdo_sqlite"];
    protected const ENV_FILE = '.env';
    protected const ENV_LOCAL_FILE = '.env.local';

    private $kernel;
    private $apiConfigPath;
    private $databaseUrl;

    /**
     * SetupCommand constructor.
     * @param KernelInterface $kernel
     * @param string $databaseUrl
     * @param string $apiConfigPath
     */
    public function __construct(KernelInterface $kernel, string $databaseUrl, string $apiConfigPath)
    {
        parent::__construct();
        $this->kernel = $kernel;
        $this->apiConfigPath = $apiConfigPath;
        $this->databaseUrl = $databaseUrl;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sets up the public admin.')
            ->setHelp('Use this command to create the base setup for Hedgebot\'s public admin.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Execute the setup only if the setup file doesn't exist
        if (is_file($this->apiConfigPath)) {
            $output->writeln(['Hedgebot admin panel already installed. Setup skipped.']);
            return 0;
        }

        $envFilePath = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . self::ENV_FILE;
        $envLocalFilePath = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . self::ENV_LOCAL_FILE;
        $dotenv = new Dotenv();

        $output->writeln([
            "Hedgebot admin panel setup",
            "==========================",
            "",
            "Welcome to the Hedgebot admin panel setup.",
            "It seems it's the first time you're installing the Hedgebot admin panel.",
            "",
            "Here you'll be configuring basic parameters for the panel.",
            "Just answer the few questions that'll be asked next and everything will be alright.",
            "Note: for some questions, there will be a value specified in parentheses. If you do not answer",
            "anything for the question, then this value will be used.",
            ""
        ]);
        $output->write("Checking extensions...");
        foreach (self::REQUIRED_EXTENSIONS as $extension) {
            if (!extension_loaded($extension)) {
                return $this->error($input, $output, "Extension " . $extension . " isn't loaded.");
            }
        }

        $output->writeln(["<info>OK</info>", ""]);

        // Ask for configuration settings
        $dbLocation = $this->askForDBLocation($input, $output);
        $botSettings = $this->askForBotSettings($input, $output);
        $eventManagerSettings = $this->askForEventManagerSettings($input, $output);
        $output->writeln(["", "That's all we need to set. Let's set everything up!", ""]);

        // Parsing .env from the dist
        $output->writeln(["Parsing environment file...", ""]);
        $envParsed = $dotenv->parse(file_get_contents($envFilePath), $envFilePath);

        $envParsed['APP_ENV'] = $botSettings['env'];
        $envParsed['APP_SECRET'] = sha1(random_bytes(32));
        $envParsed['DATABASE_URL'] = $dbLocation;
        $envParsed['LOCALE'] = $botSettings['locale'];
        $envParsed['HEDGEBOT_API_URI'] = $botSettings['uri'];
        $envParsed['HEDGEBOT_API_TOKEN'] = $botSettings['token'];
        $envParsed['EVENTMANAGER_TYPE'] = $eventManagerSettings['type'];
        $envParsed['EVENTMANAGER_HOST'] = $eventManagerSettings['host'];
        $envParsed['EVENTMANAGER_HUB_URL'] = $eventManagerSettings['hub_url'];
        $envParsed['EVENTMANAGER_TOPIC'] = $eventManagerSettings['topic'];

        // Generating .env.local from the dist
        $output->write("Generating local environment file...");
        $envParameters = "# Environment variables automatically generated by setup command" . PHP_EOL;
        foreach ($envParsed as $key => $value) {
            if (
                filter_var($value, FILTER_VALIDATE_URL) ||
                substr($value, 0, 9) == "sqlite://" ||
                substr($value, 0, 8) == "mysql://"
            ) {
                $value = '"' . $value . '"';
            }
            $envParameters .= $key . '=' . $value . PHP_EOL;
        }
        if (!file_put_contents($envLocalFilePath, $envParameters)) {
            $output->writeln("<error>Failed</error>");
            return 134;
        }
        $output->writeln("<info>OK</info>");

        // Writing the bot's interface extended configuration file
        $output->write('Generating extended configuration file...');
        $defaultBaseConfig = ["modules" => [], "settings" => []];
        $defaultBaseConfigYaml = Yaml::dump($defaultBaseConfig);
        if (!file_put_contents($this->apiConfigPath, $defaultBaseConfigYaml)) {
            $output->writeln("<error>Failed</error>");
            return 134;
        }
        $output->writeln("<info>OK</info>");

        // Updating database schema
        $migrateCommand = $this->getApplication()->find('doctrine:schema:update');
        $migrateCommand->run(new ArrayInput(['command' => 'doctrine:schema:update', '--force' => true]), $output);

        // Flushing cache
        $output->write("Flushing cache...");
        $command = $this->getApplication()->find('cache:clear');
        $command->run(new ArrayInput(['command' => 'cache:clear', '--env' => 'prod']), new NullOutput());
        $output->writeln(["<info>OK</info>", ""]);
        $output->writeln([
            "Now, you're almost ready to go. One last step is to create the first user account.",
            "To do that, just use the following command:",
            "", "php bin/console app:create-user", "",
            "You will be able to use this command in the future each time you want to create an user."
        ]);

        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|mixed|string|null
     */
    protected function askForDBLocation(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // Creating questions
        $dbLocQuestion = new Question("<question>Database file location (data.db):</question> ", "data.db");
        $dbExistsQuestion = new ConfirmationQuestion(
            "<question>This file already exists. Do you want to erase it ? (n)</question> ",
            false
        );
        $dbDirectoryQuestion = new ConfirmationQuestion(
            "<question>Directory doesn't exist. Create it ? (y)</question> ",
            true
        );
        $output->writeln([
            "The first step will be to configure where the database file will be stored.",
            "If you do not specify an absolute path for the file, then it will be taken from the ",
            "base var directory (var/).",
            ""
        ]);

        // Loop until a valid database file is set
        while (true) {
            $dbLocation = $helper->ask($input, $output, $dbLocQuestion);
            $dbTestLocation = null;

            // Set the basedir for the location if it isn't an absolute location
            if (
                (PHP_OS == 'WINNT' && !preg_match('#^[a-zA-Z]:\\\\#', $dbLocation)) ||
                $dbLocation[0] != DIRECTORY_SEPARATOR
            ) {
                $dbTestLocation = $this->kernel->getProjectDir() .
                    DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . $dbLocation;
                $dbLocation = 'sqlite:///%kernel.project_dir%' .
                    DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . $dbLocation;
            } else {
                $dbTestLocation = $dbLocation;
            }

            // Check if file exists
            if (file_exists($dbTestLocation)) {
            // Ask if the user want to erase the db file, and go back to asking a new location if not
                $eraseDb = $helper->ask($input, $output, $dbExistsQuestion);
                if (!$eraseDb) {
                    $output->writeln([
                        "",
                        "To prevent data erasing and keep your old database,",
                        "we will ask you to define a new db location.",
                        ""
                    ]);
                    continue;
                }
            }

            // Check if directory for the entered file exists, and ask to create it if needed
            $enteredDir = pathinfo($dbTestLocation, PATHINFO_DIRNAME);
            if (!is_dir($enteredDir)) {
                $createDirectory = $helper->ask($input, $output, $dbDirectoryQuestion);
                if (!$createDirectory) {
                    continue;
                }

                mkdir($enteredDir, 0777, true);
            }

            $output->writeln("");
            return $dbLocation;
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function askForBotSettings(InputInterface $input, OutputInterface $output): array
    {
        $helper = $this->getHelper('question');

        // Creating questions
        $botAddressQuestion = new Question(
            "<question>Bot access address and port (http://127.0.0.1:8081):</question> ",
            "http://127.0.0.1:8081"
        );
        $hasTokenQuestion = new ConfirmationQuestion(
            "<question>Is the bot access protected by a token ? (y)</question> ",
            true
        );
        $botTokenQuestion = new Question("<question>Bot access token:</question> ", null);
        $output->writeln([
            "Now, it's time to configure how the admin panel will connect to the bot.",
            "If you've installed the bot on the same machine and you're not sure what to set,",
            "refer to the documentation.",
            "A test will be done to check that the bot responds with the given settings.",
            "Ensure that the bot is started before validating settings."
        ]);
        $tries = 0;
        while ($tries < 5) {
            $output->writeln("");

            // Ask credentials
            $botAddress = $helper->ask($input, $output, $botAddressQuestion);
            $botToken = null;

            if ($helper->ask($input, $output, $hasTokenQuestion)) {
                $botToken = $helper->ask($input, $output, $botTokenQuestion);
            }

            // Test bot connection
            $output->writeln("");
            $output->write("Testing bot connection...");
            try {
                $apiService = new ApiClientService($botAddress, $botToken);
                $endpoint = $apiService->endpoint("/");
                $pingResult = $endpoint->ping();
                if ($pingResult) {
                    $output->writeln(["<info>OK</info>", ""]);
                    break;
                }
            } catch (RPCException $e) {
                $tries++;
                $this->error($input, $output, $e->getMessage());
                continue;
            }
        }

        $botEnvQuestion = new Question(
            "<question>Environment type ('prod' per default, can be 'dev' or 'test'):</question> ",
            "prod"
        );
        $botEnv = $helper->ask($input, $output, $botEnvQuestion);

        $botLocaleQuestion = new Question(
            "<question>Locale ('en_US' per default, must be an ICU locale ID format):</question> ",
            "en_US"
        );
        $botLocale = $helper->ask($input, $output, $botLocaleQuestion);
        $output->write("");

        return [
            'uri' => $botAddress,
            'token' => $botToken,
            'env' => $botEnv,
            'locale' => $botLocale
        ];
    }

    /**
     * @TODO
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function askForEventManagerSettings(InputInterface $input, OutputInterface $output): array
    {
        $eventManagerType = '';
        $eventManagerHost = '';
        $eventManagerHubUrl = '';
        $eventManagerTopic = '';

        return [
            'type' => $eventManagerType,
            'host' => $eventManagerHost,
            'hub_url' => $eventManagerHubUrl,
            'topic' => $eventManagerTopic
        ];
    }

    /**
     * Helper function to show errors.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $message The message to show.
     * @return false, to allow sequence breaking of calling method.
     */
    protected function error(InputInterface $input, OutputInterface $output, string $message): bool
    {
        $output->writeln([
            "<error>ERROR",
            "",
            $message . "</error>"
        ]);
        return false;
    }
}
