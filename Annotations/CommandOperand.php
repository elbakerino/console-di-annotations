<?php

namespace Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class CommandOperand {
    public $name;
    public $mode;
    public $description;
    public $default;
    public $validation;
}
