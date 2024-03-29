<?php

declare(strict_types=1);

use App\ApiConstants;
use App\Application\Actions\Admin\AdminContentCreateAction;
use App\Application\Actions\Admin\AdminContentDeleteAction;
use App\Application\Actions\Admin\AdminContentGetAction;
use App\Application\Actions\Admin\AdminContentGetListAction;
use App\Application\Actions\Admin\AdminContentResetAction;
use App\Application\Actions\Admin\AdminIndexGetAction;
use App\Application\Actions\Admin\AdminIndexPostAction;
use App\Application\Actions\Admin\AdminTypesGetAction;
use App\Application\Actions\Admin\MetadataAction;
use App\Application\Actions\Auth\AuthenticateAction;
use App\Application\Actions\Auth\ChangePasswordAction;
use App\Application\Actions\Auth\PublicInfoAction;
use App\Application\Actions\Auth\PublicInfoPostAction;
use App\Application\Actions\Auth\RegisterAction;
use App\Application\Actions\Auth\ResetPasswordAction;
use App\Application\Actions\Cms\ContentDeleteByIdAction;
use App\Application\Actions\Cms\ContentGetByIdAction;
use App\Application\Actions\Cms\ContentGetListAction;
use App\Application\Actions\Cms\ContentPostAction;
use App\Application\Actions\Cms\ContentTypesGetAction;
use App\Application\Actions\Cms\DeleteListAction;
use App\Application\Actions\Cms\IndexGetAction;
use App\Application\Actions\Cms\IndexPostAction;
use App\Application\Actions\Cms\MetadataGetAction;
use App\Application\Actions\Cms\StatusGetAction;
use App\Application\Actions\Cms\TemplateGetAction;
use App\Application\Actions\File\BasicUploadGetAction;
use App\Application\Actions\File\BasicUploadPostAction;
use App\Application\Actions\File\DeleteAction;
use App\Application\Actions\File\ThumbnailsAction;
use App\Application\Actions\Misc\DebugAction;
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
    $app->group(ApiConstants::API.'/cmsapi/status', function (Group $group) {
        $group->get('', StatusGetAction::class);
    });

    $app->group(ApiConstants::API.'/authapi', function (Group $group) {
        $group->get('/publicinfo/{id}', PublicInfoAction::class);
        $group->post('/publicinfo', PublicInfoPostAction::class);
        $group->post('/authenticate', AuthenticateAction::class);

        $group->post('/changepassword', ChangePasswordAction::class);
        $group->post('/resetpassword', ResetPasswordAction::class);
        $group->post('/register', RegisterAction::class);
    });

    $app->group(ApiConstants::API.'/debugapi', function (Group $group) {
        $group->get('', DebugAction::class);
    });

    $app->group(ApiConstants::API.'/cmsapi/content', function (Group $group) {
        $group->get('/{type}/{id}', ContentGetByIdAction::class);
        $group->get('', ContentTypesGetAction::class);
        $group->delete('/{type}/{id}', ContentDeleteByIdAction::class);
        $group->get('/{type}', ContentGetListAction::class);
        $group->post('/{type}', ContentPostAction::class);
    });
    $app->group(ApiConstants::API.'/cmsapi/deletelist', function (Group $group) {
        $group->post('/{type}', DeleteListAction::class);
    });

    $app->group(ApiConstants::API.'/fileapi', function (Group $group) {
        $group->get('/basicupload/{type}/{id}', BasicUploadGetAction::class);
        $group->post('/basicupload/{type}/{id}', BasicUploadPostAction::class);
        $group->post('/delete/{type}/{id}', DeleteAction::class);

        $group->post('/thumbnails/{type}/{id}', ThumbnailsAction::class);
    });

    $app->group(ApiConstants::API.'/adminapi', function (Group $group) {
        $group->get('/content', AdminTypesGetAction::class);
        $group->get('/metadata/{type}', MetadataAction::class);

        $group->get('/content/{type}/{id}', AdminContentGetAction::class);
        $group->get('/content/{type}', AdminContentGetListAction::class);

        $group->post('/content/{type}/{id}', AdminContentResetAction::class);
        $group->delete('/content/{type}/{id}', AdminContentDeleteAction::class);

        ///mobilecmsapi/v1/adminapi/content/users/
        $group->post('/content/{type}', AdminContentCreateAction::class);

        // /mobilecmsapi/v1/adminapi/index/users
        $group->post('/index/{type}', AdminIndexPostAction::class);
        $group->get('/index/{type}', AdminIndexGetAction::class);
    });

    $app->group(ApiConstants::API.'/cmsapi/index', function (Group $group) {
        $group->get('/{type}', IndexGetAction::class);
        $group->post('/{type}', IndexPostAction::class);
    });

    $app->group(ApiConstants::API.'/cmsapi/metadata', function (Group $group) {
        $group->get('/{type}', MetadataGetAction::class);
    });
    $app->group(ApiConstants::API.'/cmsapi/template', function (Group $group) {
        $group->get('/{type}', TemplateGetAction::class);
    });
};
