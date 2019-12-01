<?php

namespace Commands;

use Annotations\Command;
use Annotations\CommandOption;
use Annotations\CommandOperand;
use DI\Annotation\Inject;
use Lib\ConsoleApp;

class DemoMultiple {
    /**
     * this property uses the `Inject` annotation from `php-di` and get's the ConsoleApp injected
     *
     * @Inject
     * @var ConsoleApp $console
     */
    protected $console;


    /**
     * @Command(
     *     name="demo:welcome",
     *     options={
     *          @CommandOption(long="formal", description="if formal welcome or simple", default=false)
     *     },
     *     operands={
     *          @CommandOperand(name="name", description="the name to welcome")
     *     }
     * )
     * @package Commands
     */
    public function handleWelcome() {
        error_log(($this->console->getOptions()['formal'] ? 'Hello ' : 'Hi ') . (isset($this->console->getOperands()[0]) ? $this->console->getOperands()[0] : 'there') . '!');
    }

    /**
     * @Command(name="demo:bye")
     * @package Commands
     */
    public function handleBye() {
        error_log('Bye ' . (isset($this->console->getOperands()[0]) ? $this->console->getOperands()[0] : 'there') . '!');
    }
}
