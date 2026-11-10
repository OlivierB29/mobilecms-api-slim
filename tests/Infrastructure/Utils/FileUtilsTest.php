<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Utils;

use App\Infrastructure\Utils\FileUtils;
use PHPUnit\Framework\TestCase;

final class FileUtilsTest extends TestCase
{
    public function testConcatDirectoriesRejectsPathTraversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new FileUtils())->concatDirectories('/tmp/root', '../escape');
    }
}
