<?php

use function DI\autowire;

return static function() {
    return [
        \Doctrine\Common\Cache\PhpFileCache::class => autowire()
            ->constructorParameter('directory', __DIR__ . '/tmp/commands'),
    ];
};
