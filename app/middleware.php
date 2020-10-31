<?php
declare(strict_types=1);
use Psr\Http\Message\RuleInterface;


use Tuupola\Middleware\HttpBasicAuthentication;

use Tuupola\Middleware\HttpBasicAuthentication\AuthenticatorInterface;
use Tuupola\Middleware\JwtAuthentication\RequestPathRule;
use Tuupola\Middleware\JwtAuthentication\RequestMethodRule;

use Slim\App;

use App\Application\Middleware\SessionMiddleware;
use App\Application\Response\UnauthorizedResponse;
use App\Application\Middleware\UserAuthenticator;
use App\Application\Middleware\CustomJwtAuthentication;

use App\Application\Middleware\AuthorizeRule;

return function (App $app) {
    $container = $app->getContainer();

    $app->add(SessionMiddleware::class);


    $app->add(new CustomJwtAuthentication([
        "header" => "Authorization",
        "secure" => true,
        "path" => "/",
        "ignore" => ["/token", "/info"],
        "secret" => 'UNUSED_HERE', // cf custom implementation
        //"logger" => $container["logger"],
        "attribute" => false,
        "relaxed" => ["192.168.1.10", "127.0.0.1", "localhost"],
        "error" => function ($response, $arguments) {
            //return new UnauthorizedResponse($arguments["message"]);
            return $response;
        }
        /*,
        "rules" => [
            new RequestPathRule([
                "path" => "/",
                "ignore" => []
            ]),
            new RequestPathRule([
                "path" => "/",
                "ignore" => []
            ]),
            new AuthorizeRule([
                "userrole" => "guest",
                "editorpath" => ["/"],
                "adminpath" => ["/users"],
                "ignore" => ["/login"]
            ],
            )
        ]*/
        // "before" => function ($request, $arguments) use ($container) {
        //     $container["token"]->populate($arguments["decoded"]);
        // }
    ]));
    
    /*
        $app->add(new Tuupola\Middleware\JwtAuthentication([
            "rules" => [
                new Tuupola\Middleware\JwtAuthentication\RequestPathRule([
                    "path" => "/",
                    "ignore" => []
                ]),
                new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
                    "ignore" => ["OPTIONS"]
                ])
            ]
        ]));
        */
};
