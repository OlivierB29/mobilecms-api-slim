<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Utils;

use App\Infrastructure\Utils\MailUtils;
use Tests\ApiUtility;

final class MailUtilsTest extends ApiUtility
{
    public function testFrom()
    {
        $u = new MailUtils('');
        $result = $u->getFrom('foo@bar.org');
        $this->assertTrue(strpos($result, 'foo@bar.org') !== false);
    }
}
