<?php

declare(strict_types=1);

namespace App;

interface ApiConstants
{
    public const ROOT = '/mobilecmsapi';
    public const VERSION = '/v42';

    public const API = self::ROOT.self::VERSION;
}
