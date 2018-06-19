<?php
namespace Hedgebot\CoreBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as SensioScriptHandler;
use Composer\Script\Event;
use Composer\IO\ConsoleIO;
use Hedgebot\CoreBundle\Composer\IO\ConsoleProxyIO;
use Incenteev\ParameterHandler\ScriptHandler as IncenteevScriptHandler;

class ScriptHandler extends SensioScriptHandler
{
    /**
     * Proxifies the call to Incenteev's buildParameters script as a non-interactive script.
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
        $configFileLocation = $options['symfony-app-dir']. "/config/hedgebot.yml";
        if(is_file($configFileLocation)) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'setup', $options['process-timeout']);
    }
}
