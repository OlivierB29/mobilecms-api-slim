<?php
declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;


use App\Application\Actions\Cms\IndexGetAction;
use App\Application\Actions\Cms\IndexPostAction;

use App\Application\Actions\Cms\ContentTypesGetAction;
use App\Application\Actions\Cms\ContentPostAction;
use App\Application\Actions\Cms\ContentGetByIdAction;
use App\Application\Actions\Cms\ContentGetListAction;
use App\Application\Actions\Cms\ContentDeleteByIdAction;

use App\Application\Actions\Cms\DeleteListAction;
use App\Application\Actions\Cms\StatusGetAction;
use App\Application\Actions\Cms\MetadataGetAction;
use App\Application\Actions\Cms\TemplateGetAction;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('');
        return $response;
    });
    $app->group('/mobilecmsapi/v1/cmsapi/status', function (Group $group) {
        $group->get('', StatusGetAction::class);
    });
    $app->group('/debugapi', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });


    $app->group('/mobilecmsapi/v1/cmsapi/content', function (Group $group) {
        $group->get('/{type}/{id}', ContentGetByIdAction::class);
        $group->get('', ContentTypesGetAction::class);
        $group->delete('/{type}/{id}', ContentDeleteByIdAction::class);
        $group->get('/{type}', ContentGetListAction::class);
        $group->post('/{type}', ContentPostAction::class);
    });
    $app->group('/mobilecmsapi/v1/cmsapi/deletelist', function (Group $group) {
        $group->post('/{type}', DeleteListAction::class);
    });


    $app->group('/mobilecmsapi/v1/cmsapi/index', function (Group $group) {
        $group->get('/{type}', IndexGetAction::class);
        $group->post('/{type}', IndexPostAction::class);
    });

    $app->group('/mobilecmsapi/v1/cmsapi/metadata', function (Group $group) {
        $group->get('/{type}', MetadataGetAction::class);
    });
    $app->group('/mobilecmsapi/v1/cmsapi/template', function (Group $group) {
        $group->get('/{type}', TemplateGetAction::class);
    });




};
