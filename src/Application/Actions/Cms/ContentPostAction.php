<?php

declare(strict_types=1);

namespace App\Application\Actions\Cms;

use Psr\Http\Message\ResponseInterface as Response;

class ContentPostAction extends CmsAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        // step 1 : update Record

        $body = $this->getRequestBody();
        $putResponse = $this->getService()->post($this->getParam('type'), self::ID, $body);
        if ($putResponse->getCode() !== 200) {
            return $this->withResponse($putResponse);
        }

        $myobjectJson = $putResponse->getResult();

        // step 2 : publish to index
        $id = $myobjectJson->{self::ID};

        // issue : sometimes, the index is not refreshed
        $response = $this->getService()->publishById($this->getParam('type'), self::ID, $id);
        // $response = $this->getService()->rebuildIndex($this->getParam('type'), self::ID);

        if ($response->getCode() === 200) {
            $response->setResult($myobjectJson);
        }

        return $this->withResponse($response);
    }
}
