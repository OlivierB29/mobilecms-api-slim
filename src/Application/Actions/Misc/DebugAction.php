<?php
declare(strict_types=1);

namespace App\Application\Actions\Misc;

use Psr\Http\Message\ResponseInterface as Response;

use App\Application\Actions\RestAction;

class DebugAction extends RestAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        $response->setCode(200);
        
        $result = new \stdClass;
        $result->{'uri'} = $this->request->getUri()->getPath();

        $response->setResult($result);

        return $this->withResponse($response);
    }
}
