<?php

declare(strict_types=1);

use App\Application\Middleware\CustomJwtAuthentication;
use App\Application\Middleware\SessionMiddleware;
use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    $app->add(SessionMiddleware::class);

    $app->add(new CustomJwtAuthentication([
        'header' => 'Authorization',
        'secure' => true,
        'path'   => '/',
        'ignore' => ['/token', '/info'],
        'secret' => 'UNUSED_HERE', // cf custom implementation
        //"logger" => $container["logger"],
        'attribute' => false,
        'relaxed'   => ['192.168.1.10', '127.0.0.1', 'localhost'],
        'error'     => function ($response, $arguments) {
            return $response;
        },

    ]));
};
