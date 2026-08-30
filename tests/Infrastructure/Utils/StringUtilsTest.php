<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Utils;

use App\Infrastructure\Utils\StringUtils;
use Tests\ApiUtility;

final class StringUtilsTest extends ApiUtility
{
    public function testStartsWith()
    {
        $this->assertTrue(StringUtils::startsWith('foobar', 'foo'));
        $this->assertFalse(StringUtils::startsWith('foobar', 'bar'));
    }

    public function testEndsWith()
    {
        $this->assertTrue(StringUtils::endsWith('foobar', 'bar'));
        $this->assertFalse(StringUtils::endsWith('foobar', 'foo'));
    }

    public function testSlugify()
    {
        $this->assertEquals('aaaaaaaaaa', StringUtils::slugify('aaaaaaaaaa'));
        $this->assertEquals('2026-08-23', StringUtils::slugify('2026-08-23'));
        $this->assertEquals('hello-world', StringUtils::slugify('Hello World!'));
        $this->assertEquals('ete-kendo', StringUtils::slugify('Été kendo'));
        $this->assertEquals('', StringUtils::slugify('   '));
    }
}
