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

        $body = $this->request->getParsedBody();
        $putResponse = $this->getService()->post($this->getParam('type'), self::ID, $body);
        $myobjectJson = $putResponse->getResult();
        unset($putResponse);

        // step 2 : publish to index
        $id = $myobjectJson->{self::ID};
        unset($myobjectJson);
                
        // issue : sometimes, the index is not refreshed
        $response = $this->getService()->publishById($this->getParam('type'), self::ID, $id);
        // $response = $this->getService()->rebuildIndex($this->getParam('type'), self::ID);

        return $this->withResponse($response);
    }
}
