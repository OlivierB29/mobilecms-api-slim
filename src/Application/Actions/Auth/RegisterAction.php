<?php
declare(strict_types=1);

namespace App\Application\Actions\Auth;


use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;
use App\Infrastructure\Services\AuthService;
use App\Infrastructure\Utils\FileUtils;

class RegisterAction extends AuthAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        //throw error if wrong configuration, such as empty directory
        $this->checkConfiguration();
        $service = new AuthService($this->getPrivateDirPath() . '/users');


        // register and create a user

            $user = $this->getRequestBody();
            //returns a empty string if success, a string with the message otherwise

            $createresult = $service->createUser(
                $user->{'name'},
                $user->{'email'},
                $user->{'password'},
                'create'
            );
            if ($createresult === null) {
                $response->setCode(200);
                $response->setResult(new \stdClass);
            } else {
                $response->setError(400, 'Bad user parameters');
            }
        

                return $this->response($this->request, $response);
    }
}
