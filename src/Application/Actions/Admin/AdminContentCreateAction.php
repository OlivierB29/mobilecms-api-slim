<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use Psr\Http\Message\ResponseInterface as Response;

use App\Infrastructure\Services\ContentService;

use App\Infrastructure\Services\AuthService;
use App\Infrastructure\Utils\JsonUtils;

class AdminContentCreateAction extends AdminAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        $this->checkConfiguration();

        $service = new ContentService($this->getPrivateDirPath());
        $authService = new AuthService($this->getPrivateDirPath() . '/users');

        // get all properties of a user, unless $user->{'property'} will fail if the request is empty
        $user = $this->getDefaultUser();
        // get parameters from request
        $requestuser = $this->getRequestBody();

        JsonUtils::copy($requestuser, $user);

        //returns a empty string if success, a string with the message otherwise

        $createresult = $authService->createUser(
            $user->{'name'},
            $user->{'email'},
            $user->{'password'},
            'create'
        );
        if (empty($createresult)) {
            $id = $user->{'email'};
            $response = $service->publishById($this->getParam('type'), 'email', $id);
            unset($user);
            $response->setResult(new \stdClass);
            $response->setCode(200);
        } else {
            $response->setError(400, $createresult);
        }
        return $this->withResponse($response);
    }
}
