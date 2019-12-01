<?php

namespace Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class CommandOption {
    public $short;
    public $long;
    public $mode;
    public $default;
    public $description;
    public $validation;
}
