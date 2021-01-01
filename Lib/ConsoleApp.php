<?php

namespace Lib;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use Invoker\InvokerInterface;

/**
 * Integrates GetOpt and invokes the matched command with dependencies injected.
 *
 * @package Lib
 */
class ConsoleApp {
    /**
     * @var array the options currently used
     */
    protected array $options = [];
    /**
     * @var array the operands currently used
     */
    protected array $operands = [];

    /**
     * @var GetOpt
     */
    protected GetOpt $get_opt;

    /**
     * ConsoleApp constructor.
     *
     * This constructor uses `constructor Injection` to get current `GetOpt` service
     *
     * @param \GetOpt\GetOpt $get_opt
     */
    public function __construct(GetOpt $get_opt) {
        $this->get_opt = $get_opt;

        // add default help
        $this->get_opt->addOption((new Option('h', 'help', Getopt::NO_ARGUMENT))->setDescription('Displays help with all commands.'));
    }

    /**
     * Matching of the console request to a command, setting matched data and invoking it.
     *
     * @param \Invoker\InvokerInterface $invoker
     *
     * @throws \GetOpt\ArgumentException
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function handle(InvokerInterface $invoker) {
        // you can type-hint also for the ContainerInterface or Invoker anywhere else (which is called from an invoker)

        $this->get_opt->process();

        if($this->get_opt->getOption('h')) {
            // handle default help
            echo $this->get_opt->getHelpText();
            exit;
        }

        $command = $this->get_opt->getCommand();

        if(!$command || !$command->getHandler()) {
            // no command or command handler
            throw new \Exception('Console: no command match.');
        }

        $this->options = $this->get_opt->getOptions();
        $this->operands = $this->get_opt->getOperands();

        // Calling the matched handler with the DI enabled invoker
        // add current command as optional receiver for only this handler
        $invoker->call($command->getHandler(), [
            Command::class => $command,
        ]);
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOperands() {
        return $this->operands;
    }
}
