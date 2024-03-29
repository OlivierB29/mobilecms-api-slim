<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Infrastructure\Services\ContentService;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Delete a user.
 */
class AdminContentDeleteAction extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();
        $service = new ContentService($this->getPrivateDirPath());
        $this->checkConfiguration();

        // delete a single record.
        // eg : /mobilecmsapi/v1/content/calendar/1/foo/bar --> ['1', 'foo', 'bar']
        $response = $service->deleteRecord($this->getParam('type'), $this->getParam('id'));
        if ($response->getCode() === 200) {
            // rebuild index
            $response = $service->rebuildIndex($this->getParam('type'), 'email');
        }

        // delete a record and update the index. eg : /mobilecmsapi/v1/content/calendar/1.json
        return $this->withResponse($response);
    }
}
