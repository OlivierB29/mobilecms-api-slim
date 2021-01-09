<?php
declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;
use App\Infrastructure\Services\AuthService;

class ChangePasswordAction extends AuthAction
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

       
        // login and get token
        // eg : { "user": "test@example.com", "password":"Sample#123456"}

        $logindata = $this->getRequestBody();


        $captchaanswer = null;
        if (isset($logindata->{'captchaanswer'})) {
            $captchaanswer = $logindata->{'captchaanswer'};
        }

        //TODO : user contains either email of name

        // free variables before response
        $response = $service->changePassword(
            $this->getUser($logindata),
            $logindata->{'password'},
            $logindata->{'newpassword'},
            $captchaanswer
        );
        
        unset($logindata);
        

        return $this->withResponse($response);
    }
}
