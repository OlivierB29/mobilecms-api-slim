<?php

declare(strict_types=1);

namespace App;

interface ApiConstants
{
    public const ROOT = '/mobilecmsapi';
    public const VERSION = '/v50';

    public const API = self::ROOT.self::VERSION;
}
