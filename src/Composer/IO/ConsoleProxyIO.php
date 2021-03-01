<?php
namespace App\Composer\IO;

use Composer\IO\ConsoleIO;

/**
 * Console proxy IO class. This class takes a ConsoleIO as parameter in its constructor,
 * sets its input to non-interactive and then allows to return it.
 *
 * It only extends ConsoleIO to allow accessing its protected properties.
 */
class ConsoleProxyIO extends ConsoleIO
{
    /**
     * @var ConsoleIO
     */
    private $io;

    /**
     * Constructor.
     *
     * @param ConsoleIO $io The IO to render non-interactive.
     */
    public function __construct(ConsoleIO $io)
    {
        $this->io = $io;
    }

    /**
     * Sets the IO interactive status.
     *
     * @param bool True to set the console as interactive, false to set it as non-interactive.
     */
    public function setInteractive($interactive = true)
    {
        $this->io->input->setInteractive($interactive);
    }
}
