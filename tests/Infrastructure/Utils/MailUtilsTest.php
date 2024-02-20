<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Utils;

use App\Infrastructure\Utils\MailUtils;
use PHPUnit\Framework\TestCase;

final class MailUtilsTest extends TestCase
{
    public function testFrom()
    {
        $u = new MailUtils('');
        $result = $u->getFrom('foo@bar.org');
        $this->assertTrue(strpos($result, 'foo@bar.org') !== false);
    }
}
