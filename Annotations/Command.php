<?php

namespace Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Command {
    public $name;
    public $handler;
    /**
     * @var \Annotations\CommandOption[]
     */
    public $options = [];
    /**
     * @var \Annotations\CommandOperand[]
     */
    public $operands = [];
}
