<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use Psr\Http\Message\ResponseInterface as Response;

use App\Infrastructure\Services\ContentService;

use App\Infrastructure\Services\AuthService;

/**
 * Reset password
 */
class AdminContentResetAction extends AdminAction
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

        // save a record and update the index. eg : /mobilecmsapi/v1/content/calendar
        // step 1 : update Record

        // update password if needed
        $user = $this->getRequestBody();
        if (isset($user->{'newpassword'})) {
            $response = $authService->resetPassword($user->{'email'}, $user->{'newpassword'});
        } else {
            $putResponse = $service->update(
                $this->getParam('type'),
                'email',
                $this->getUserResponse($user)
            );

            $myobjectJson = $putResponse->getResult();
            unset($putResponse);
            // step 2 : publish to index
            $id = $myobjectJson->{'email'};
            unset($myobjectJson);
            $response = $service->publishById($this->getParam('type'), 'email', $id);
        }
        return $this->withResponse($response);
    }
}
