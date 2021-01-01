<?php

use Orbiter\AnnotationsUtil\AnnotationsUtil;
use Orbiter\AnnotationsUtil\CodeInfo;
use Doctrine\Common\Cache;

//
// with `Lib\ConsoleApp::addInit` you should only setup GetOpt
// registered callables/resolvables are executed just before processing current console input

return static function(
    GetOpt\GetOpt $get_opt,
    CodeInfo $code_info,
    Cache\PhpFileCache $cache
) {
    // already dependency injected!

    // add commands manually
    $get_opt->addCommands([
        // new GetOpt\Command(<name>, <handler>, <options>[optional])

        // commands are getting container resolved & dependency injected!

        // use any defined service or callable
        // new GetOpt\Command('demo', [Commands\Demo::class, 'handle']),

        // also possible for short commands, just an injected closure
        new GetOpt\Command('get-services', static function(CodeInfo $code_info) {
            $services = $code_info->getClassNames('services');
            echo 'Found `' . count($services) . '` Services registered: ' . PHP_EOL;
            foreach($services as $service) {
                echo '  ' . $service . PHP_EOL;
            }
            echo 'end.' . PHP_EOL;
        }),
    ]);

    // discovering commands by annotations
    $commands = [];
    if(
        getenv('env') === 'prod' &&
        $cache->contains('commands')
    ) {
        // caching found commands at production
        $commands = $cache->fetch('commands');
    } else {
        $annotated = $code_info->getClassNames('services');
        foreach($annotated as $annotated_class) {
            // parsing all command annotations and adding them to the `commands` value in container, to register later (see _commands.php)
            $class_annotation = AnnotationsUtil::getClassAnnotation($annotated_class, Annotations\Command::class);
            if($class_annotation) {
                /**
                 * @var \Annotations\Command $class_annotation
                 */
                $commands[] = [
                    'class' => $annotated_class,
                    'annotation' => $class_annotation,
                ];
            }
        }

        $annotated_methods = $code_info->getClassMethods('services');
        foreach($annotated_methods as $class_name => $annotated_class_methods) {
            $methods = [];
            array_push($methods, ...$annotated_class_methods['public']);
            array_push($methods, ...$annotated_class_methods['static']);
            foreach($methods as $method) {
                $method_annotation = AnnotationsUtil::getMethodAnnotation($class_name, $method, Annotations\Command::class);
                if($method_annotation) {
                    /**
                     * @var \Annotations\Command $method_annotation
                     */
                    $commands[] = [
                        'class' => $class_name,
                        'method' => $method,
                        'annotation' => $method_annotation,
                    ];
                }
            }
        }

        if(getenv('env') === 'prod') {
            $cache->save('commands', $commands);
        }
    }

    // registering of commands, discovered by annotations
    if(is_array($commands)) {
        foreach($commands as $command) {
            if(!isset($command['class'], $command['annotation'])) {
                continue;
            }
            $annotation = $command['annotation'];
            if(isset($command['method'])) {
                // If the annotation was targeted at an method, set the method as handler
                $annotation->handler = $command['method'];
            }
            $cmd = Lib\CommandBuilder::make($command['class'], $annotation);

            if($cmd) {
                $get_opt->addCommand($cmd);
            }
        }
    }
};
