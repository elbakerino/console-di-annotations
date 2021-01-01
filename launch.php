<?php

/**
 * Setup + Launch of the whole ConsoleApp
 *
 * 1. composer autoloader
 * 2. .env support by DotEnv
 * 3. Whoops CLI error handler
 * 4. Doctrine\Annotations with Orbiter\AnnotationsUtil (simple interface incl. cached reflections)
 * 5. Dependency Injection by PHP-DI
 * 6. incl. automatic service definition and class search caching
 * 7. register commands with DI enabled callable/resolvables
 * 8. launch the ConsoleApp handler
 *
 * PHP version 7.3+
 *
 * LICENSE: MIT, see README.md and LICENSE
 *
 * @author     Michael Becker, https://mlbr.xyz
 * @copyright  2019 Michael Becker
 * @license    MIT
 * @link       https://packagist.org/packages/elbakerino/console
 */

use Invoker\InvokerInterface;
use Orbiter\AnnotationsUtil\AnnotationsUtil;
use Orbiter\AnnotationsUtil\CodeInfo;
use DI\ContainerBuilder;
use function DI\autowire;

date_default_timezone_set('UTC');

// this config can be used to customize the automatic discovery and registration logic's folders
$config = [
    // Folders containing annotations
    'annotations' => [
        // PSR4\Namespace => abs/Path
        'Annotations' => __DIR__ . '/Annotations',
    ],
    // annotations to ignore, Doctrine\Annotations applies a default filter
    'annotations_ignore' => [
        'dummy',
    ],
    // Folders compiled into DI-Container
    'di_services' => [
        __DIR__ . '/Commands',
        __DIR__ . '/Lib',
    ],
];
// Cache config is hard coded at each cache-registration in this file

(static function($config) {
    // 1. composer autoloader
    require_once __DIR__ . '/vendor/autoload.php';

    // 2. setup .env
    // add `.env` to `.gitignore` in your final app, don't commit config to git!
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load(); // throws error if no .env found

    // if no env is set, pretend it is production
    if(empty(getenv('env'))) {
        putenv('env=prod');
    }

    if(getenv('env') !== 'prod') {
        // 3. register `filp/whoops` nice and universal error handling
        $whoops = new Whoops\Run;
        if(PHP_SAPI === 'cli') {
            $whoops->prependHandler(new Whoops\Handler\PlainTextHandler());
            $whoops->register();
        } else {
            // add other handlers if needed, e.g. for HTML:
            // $whoops->prependHandler(new Whoops\Handler\PrettyPageHandler());
            // $whoops->register();
        }
    }

    // 4. setup annotations with the Orbiter\AnnotationsUtil

    // add all directories which are containing annotations
    foreach($config['annotations'] as $annotation_ns => $annotation_ns_dir) {
        AnnotationsUtil::registerPsr4Namespace($annotation_ns, $annotation_ns_dir);
    }
    foreach($config['annotations_ignore'] as $annotation_ig) {
        Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName($annotation_ig);
    }

    AnnotationsUtil::useReader(
        AnnotationsUtil::createReader(
            getenv('env') === 'prod' ? __DIR__ . '/tmp/annotations' : null // DO NOT enable caches during development
        )
    );

    // 5. Setup DI
    $container_builder = new ContainerBuilder();
    $container_builder->useAutowiring(true);
    $container_builder->useAnnotations(true);// includes @Inject and more: http://php-di.org/doc/annotations.html

    // 5. + 6. further DI + class search caching for automatic service definition

    $code_info = new CodeInfo(); // init helper for static code analyzes (e.g. get classes in dir)

    if(getenv('env') === 'prod') { // DO NOT enable caches during development
        // enable cache for service/class search and DI
        // one-time caches, if existing they don't update! delete file & directories
        $code_info->enableFileCache(__DIR__ . '/tmp/codeinfo.cache');
        $container_builder->enableCompilation(__DIR__ . '/tmp/di');
    }

    // these directories get automatically scanned for classes to register as services
    $code_info->defineDirs('services', $config['di_services']);

    // execute scanning
    $code_info->process();

    $definitions = [];

    // setup services automatically
    // why automatically is summarized in Orbiter\AnnotationsUtils README.md
    // - https://github.com/bemit/orbiter-annotations-util#example-codeinfo-di-service-setup
    // - meta info: http://php-di.org/doc/performances.html
    $services = $code_info->getClassNames('services');

    foreach($services as $service) {
        $definitions[$service] = autowire($service);
    }

    $definitions = array_merge($definitions, (require __DIR__ . '/_definitions.php')());
    $definitions[CodeInfo::class] = $code_info;
    $definitions['config'] = $config;

    $container_builder->addDefinitions($definitions);
    // add further DI definitions when needed, see PHP-DI definitions docs.
    // http://php-di.org/doc/php-definitions.html

    try {
        // build container by definitions
        $container = $container_builder->build();
    } catch(\Exception $e) {
        error_log('launch: Container build failed: ' . $e->getMessage());
        exit(2);
    }

    // 7. add console setup
    $invoker = $container->get(InvokerInterface::class);
    $commands = require __DIR__ . '/_commands.php';
    $invoker->call($commands);

    // here you can also add a Router-Middleware/Console switch

    // 8. finally we launch the console handler!
    $container->call([Lib\ConsoleApp::class, 'handle']);
})($config);
