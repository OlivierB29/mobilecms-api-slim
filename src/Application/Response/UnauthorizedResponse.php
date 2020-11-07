<?php
declare(strict_types=1);

/*
 * This file is part of the Slim API skeleton package
 *
 * Copyright (c) 2016-2017 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/slim-api-skeleton
 *
 */

namespace App\Application\Response;

use Slim\Psr7\Headers;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

class UnauthorizedResponse extends Response
{
    public function __construct($message, $status = 401)
    {
        $handle = fopen("php://temp", "wb+");
        $body = new Stream($handle);
        $body->write($message);
        $headers = new Headers;
        $headers->setHeader("Content-type", "application/problem+json");
        parent::__construct($status, $headers, $body);
    }
}
