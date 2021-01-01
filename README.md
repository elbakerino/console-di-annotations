# PHP Console with DI and Annotations

Build PHP Console applications with Dependency Injection and Annotations.

Setup app skeleton and install dependencies with [composer](https://getcomposer.org/):

    composer create-project elbakerino/console
    
That's it - ready to code!

**Run demo commands:**    

    # get help
    php cli -h

    # how to access operands, see `Commands\Demo->handle`    
    php cli demo
    php cli demo World
    php cli demo:welcome
    php cli demo:bye
    
For [Docker](https://www.docker.com/), download repository and spin up services:
    
    git clone https://github.com/elbakerino/console-di-annotations.git
    
    docker-compose up
    
    # open second terminal:
    # open `bash` in docker service `app` with user `www-data`
    docker-compose exec -u www-data app bash
    composer install

## Command Setup

At [_commands.php](_commands.php) demo [GetOpt-PHP](http://getopt-php.github.io/getopt-php/commands.html) commands are registered with inline documentation.

Here also the commands are registered for found annotations.

For only manual command, it's as short as:

```php
<?php
return static function(GetOpt\GetOpt $get_opt) {
    // already dependency injected!

    $get_opt->addCommand(new GetOpt\Command('demo', [Commands\Demo::class, 'handle']));
    //                   new GetOpt\Command(<name>,  <handler>,                     <options>[optional])
};
```

You can register commands with annotations, like the demo commands *(todo: annotation command docs)*.

See [Commands\Demo](Commands/Demo.php) for a demo `class` command handler, any `callable`/`resolvable` is possible as command handler.

See [Commands\DemoMultiple](Commands/DemoMultiple.php) for a demo `class` command handler which uses annotations at method level, not class level.

Schematic use of defined commands:

    php cli <name> <..operand> <..-a=opt>
    
## More Details
    
In [launch.php](launch.php) the whole setup and auto-config is done, see inline-comments for details.

In [_definitions.php](_definitions.php) you can define/overwrite service definitions for PHP-DI.

See [Lib\ConsoleApp](Lib/ConsoleApp.php) for the execution of GetOpt, use as base to switch to another console framework.

See [Annotations](Annotations) for example annotations, further docs on annotation will follow / can be found below in the libraries links.

Configure Docker in [docker-compose.yml](docker-compose.yml). Simply change PHP version, image base and add other PHP extensions in [Dockerfile](Dockerfile), rebuild image with `docker-compose up --build`. When changing [docker-opcache.ini](docker-opcache.ini) or [docker-vhost.conf](docker-vhost.conf) a rebuild is needed.

For further details see:

- [GetOpt-PHP Commands](http://getopt-php.github.io/getopt-php/commands.html)
- [PHP-DI](http://php-di.org)
- [Orbiter\AnnotationsUtil](https://packagist.org/packages/orbiter/annotations-util) 
    - uses [Doctrine\Annotations](https://www.doctrine-project.org/projects/annotations.html)
    - doctrine setup helper utility
    - with cached reflections
    - with caching static code analyzer for e.g. getting class names by directory (needed for auto-config)
    
Install any other dependency for your project from [packagist](https://packagist.org/).

## Todos

There is not really more needed, you can write simple and efficient console apps.

Some nice to haves would be:

- [ ] print line, print success, print error helper functions
- [ ] print in color helper functions
- [ ] input handling helper functions
- [ ] some middleware pipelines around commands
    - [ ] time and performance middleware 
- [ ] logging with PSR logger (monolog) by default 

### Downloads

- See [Composer Package](https://packagist.org/packages/elbakerino/console).
- Repository `git clone https://github.com/elbakerino/console-di-annotations.git`
    
## License

This project is free software distributed under the **MIT License**.

See: [LICENSE](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository.

***

Author: [Michael Becker](https://mlbr.xyz)

