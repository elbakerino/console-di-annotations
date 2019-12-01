<?php

namespace Commands;

use Annotations\Command;
use Annotations\CommandOperand;
use DI\Annotation\Inject;
use Lib\ConsoleApp;

/**
 * Class Demo
 *
 * @Command(
 *     name="demo",
 *     handler="handle",
 *     operands={
 *          @CommandOperand(name="name", description="the name to welcome")
 *     }
 * )
 * @package Commands
 */
class Demo {

    /**
     * this property uses the `Inject` annotation from `php-di` and get's the ConsoleApp injected
     *
     * @Inject
     * @var ConsoleApp $console
     */
    protected $console;

    public function handle() {
        error_log('Hi ' . (isset($this->console->getOperands()[0]) ? $this->console->getOperands()[0] : 'there') . '!');
    }
}
