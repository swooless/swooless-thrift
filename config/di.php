<?php declare(strict_types=1);

use Swooless\Config\Loader\ZKLoader;
use Swooless\Registry\RedisRegistry;
use Swooless\Registry\SPI;
use Swooless\Config\Loader\LoaderInterface;

use function DI\create;

return [
    'SERVER_NAME' => env('SERVER_NAME', 'demo'),

    'ServerProcessor' => create()
        ->constructor(create(DemoHandler::class)),

    LoaderInterface::class => create(ZKLoader::class),

    SPI::class => create(RedisRegistry::class),
];
