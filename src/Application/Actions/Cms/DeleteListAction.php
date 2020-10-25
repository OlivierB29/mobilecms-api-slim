<?php
declare(strict_types=1);

namespace App\Application\Actions\Cms;

use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;



class DeleteListAction extends CmsAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

     //   $this->checkConfiguration();

        //  $pathId = $this->getParam('id');



                // save a record and update the index. eg : /mobilecmsapi/v1/content/calendar


                // step 1 : delete records

                $body = $this->request->getParsedBody();
                $putResponse = $this->getService()->deleteRecords(
                    $this->getParam('type'),
                    $body
                );
                $myobjectJson = $putResponse->getResult();
                unset($putResponse);
                // step 2 : publish to index
                unset($myobjectJson);
                $response = $this->getService()->rebuildIndex($this->getParam('type'), self::ID);


        return $this->withResponse($response);

    }
}
