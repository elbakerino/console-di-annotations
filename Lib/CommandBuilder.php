<?php

namespace Lib;

use Annotations;
use GetOpt;

/**
 * Creates GetOpt Commands out of annotations
 *
 * @package Lib
 */
class CommandBuilder {

    /**
     * Takes an annotated class and its command annotation, makes a get-opt command out of it
     *
     * @param $class
     * @param \Annotations\Command $annotation
     *
     * @return \GetOpt\Command
     */
    public static function make($class, Annotations\Command $annotation) {
        $cmd = new GetOpt\Command($annotation->name, [$class, $annotation->handler]);
        $cmd->addOptions(static::makeOptions($class, $annotation->options));
        $cmd->addOperands(static::makeOperands($class, $annotation->operands));

        return $cmd;
    }

    /**
     * @param string $class
     * @param \Annotations\CommandOption[] $options
     *
     * @return GetOpt\Option[]
     */
    protected static function makeOptions($class, $options) {
        $opts = [];

        foreach($options as $option) {
            if(!$option) {
                continue;
            }
            $opt = new GetOpt\Option($option->short, $option->long, $option->mode ?? GetOpt\GetOpt::NO_ARGUMENT);
            $opt->setDescription($option->description ?? '');
            if($option->validation) {
                $opt->setDefaultValue($option->default);
            }
            if($option->validation) {
                $opt->setValidation([$class, $option->validation]);
            }
            $opts[] = $opt;
        }

        return $opts;
    }

    /**
     * @param string $class
     * @param \Annotations\CommandOperand[] $operands
     *
     * @return GetOpt\Operand[]
     */
    protected static function makeOperands($class, $operands) {
        $ops = [];

        foreach($operands as $operand) {
            if(!$operand) {
                continue;
            }
            $opt = new GetOpt\Operand($operand->name, $operand->mode ?? GetOpt\Operand::OPTIONAL);
            $opt->setDescription($operand->description);
            if($operand->default) {
                $opt->setDefaultValue($operand->default);
            }
            if($operand->validation) {
                $opt->setValidation([$class, $operand->validation]);
            }
            $ops[] = $opt;
        }

        return $ops;
    }
}
