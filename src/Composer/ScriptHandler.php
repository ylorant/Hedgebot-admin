<?php
namespace App\Composer;

use Composer\Script\Event;
use Composer\IO\ConsoleIO;
use App\Composer\IO\ConsoleProxyIO;

class ScriptHandler
{
    /**
     * Proxifies the call to buildParameters script as a non-interactive script.
     */
    public static function buildParameters(Event $event)
    {
        $io = $event->getIO();

        if ($io instanceof ConsoleIO) {
            $ioProxy = new ConsoleProxyIO($io);
            $ioProxy->setInteractive(false);
        }

        IncenteevScriptHandler::buildParameters($event);

        if ($io instanceof ConsoleIO) {
            $ioProxy = new ConsoleProxyIO($io);
            $ioProxy->setInteractive(true);
        }
    }

    /**
     * Executes the initial setup command.
     */
    public static function executeSetup(Event $event)
    {
        $options = self::getOptions($event);
        $consoleDir = self::getConsoleDir($event, 'setup');

        if ($consoleDir === null) {
            return;
        }

        // Execute the setup only if the setup file doesn't exist
        // FIXME: Try to replace that with a file name that isn't hard-coded ?
        $configFileLocation = $options['symfony-app-dir']. "/config/hedgebot.yaml";
        if (is_file($configFileLocation)) {
            return;
        }

        // Prompt the user to execute the setup command since it seems like it's the first time he installs
        $io = $event->getIO();
        $io->write("<question>");
        $io->write("Hey, it seems it's the first time you're installing the Hedgebot admin panel.");
        $io->write("Before being completely ready, it needs some configuration. Fortunately, there's a wizard.");
        $io->write("To start it, just type this command when the install has finished:");
        $io->write("</question>");
        $io->write("");
        $io->write("php bin/console setup");
        $io->write("");
        $io->write("<bg=yellow></>");
    }
}
