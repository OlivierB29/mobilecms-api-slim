<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Infrastructure\Rest\Response as RestResponse;
use App\Infrastructure\Utils\Properties;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

abstract class RestAction extends Action
{
    protected $usepost = false;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * Get main working directory.
     *
     * @return string rootDir main working directory
     */
    public function getRootDir(): string
    {
        return Properties::getInstance()->getRootDir();
    }

    /**
     * Get public directory.
     *
     * @return string publicdir main public directory
     */
    public function getPublicDirPath(): string
    {
        return $this->getRootDir().$this->getConf()->{'publicdir'};
    }

    /**
     * Get public directory.
     *
     * @return string publicdir main public directory
     */
    public function getMediaDirPath(): string
    {
        return $this->getRootDir().$this->getConf()->{'media'};
    }

    /**
     * Get privatedir directory.
     *
     * @return string privatedir main privatedir directory
     */
    public function getPrivateDirPath(): string
    {
        return $this->getRootDir().$this->getConf()->{'privatedir'};
    }

    /**
     * get JSON conf.
     *
     * @return \stdClass JSON conf
     */
    public function getConf()
    {
        return Properties::getInstance()->getConf();
    }

    /**
     * get conf.
     *
     * @return Properties conf
     */
    public function getProperties()
    {
        return Properties::getInstance();
    }

    /**
     * replace it later.
     */
    public function getParam(string $arg): string
    {
        return $this->resolveArg($arg);
    }

    /**
     * Initialize a default Response object.
     *
     * @return RestResponse object
     */
    protected function getDefaultResponse(): RestResponse
    {
        $response = new RestResponse();
        $response->setCode(400);
        $response->setResult(new \stdClass());

        return $response;
    }

    /**
     * @param RestResponse $resp
     *
     * @return ResponseInterface
     */
    protected function withResponse(RestResponse $resp): ResponseInterface
    {
        // $this->slimException($request, $resp);
        return $this->respondWithData($resp->getResult(), $resp->getCode());
    }

    protected function getRequestBody()
    {
        $postformdata = $this->getProperties()->getString('postformdata');

        if ($postformdata === 'post') {
            return $this->striptags($_POST);
        }
        if ($postformdata === 'parsedbody') {
            return  $this->xssjson($this->request->getParsedBody());
        }

        if ($postformdata === 'phpinput') {
            $input = json_decode($this->striptags(file_get_contents('php://input')));

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new HttpBadRequestException($this->request, 'Malformed JSON input.');
            }

            return $input;
        }

        throw new \Exception('request body');
    }

    private function getFormData()
    {
        if ($this->usepost) {
            return $_POST;
        } else {
            $input = json_decode(file_get_contents('php://input'));

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new HttpBadRequestException($this->request, 'Malformed JSON input.');
            }

            return $input;
        }
    }

    protected function xsshtml($input)
    {
        return $this->striptags($input);
    }

    protected function xssjson($input)
    {
        /* $dirty_html = \json_encode($input);
         $config = \HTMLPurifier_Config::createDefault();
         $purifier = new \HTMLPurifier($config);
         $clean_html = $purifier->purify($dirty_html);
         return \json_decode($clean_html);*/
        return  \json_decode($this->striptags(\json_encode($input)));
    }

    protected function striptags($input)
    {
        $result = \strip_tags($input);
        $result = $this->xss_clean($result);
        $result = \htmlspecialchars($result, ENT_NOQUOTES, 'UTF-8');

        return $result;
    }

    // https://stackoverflow.com/questions/1336776/xss-filtering-function-in-php
    protected function xss_clean($data)
    {
        // Fix &entity\n;
        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }

    private function cleanInputs($data)
    {
        $clean_input = [];
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->cleanInputs($v);
            }
        } else {
            $clean_input = trim($this->striptags($data));
        }

        return $clean_input;
    }
}
