<?php

declare(strict_types=1);

use App\Infrastructure\Utils\Properties;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    Properties::init($_SERVER['DOCUMENT_ROOT'], $_SERVER['DOCUMENT_ROOT'].'../private/conf/conf.json');

    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => false, // Should be set to false in production
            'logger'              => [
                'name'  => 'slim-app',
                'path'  => isset($_ENV['docker']) ? 'php://stdout' : __DIR__.'/../logs/app.log',
                'level' => Logger::DEBUG,
            ],
        ],
    ]);
};
