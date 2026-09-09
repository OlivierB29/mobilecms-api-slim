<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Services;

use App\Infrastructure\Services\IdGeneratorUtils;

use Tests\ApiUtility;


final class IdGeneratorUtilsTest extends ApiUtility
{
	public function testGenerateReturnsUniqueNonEmptyIds(): void
	{
	

		$firstId = IdGeneratorUtils::assignGeneratedId(
            'news',
            'id',
            (object) ['title' => 'Test Title', 'date' => '2026-08-23'],
            [
                (object) ['name' => 'id', 'editor' => 'line', 'generated' => 'date,title'],
                (object) ['name' => 'title', 'editor' => 'text'],
                (object) ['name' => 'date', 'editor' => 'date']
            ]
        );
		$secondId = IdGeneratorUtils::assignGeneratedId(
            'news',
            'id',
            (object) ['title' => 'Test Title', 'date' => '2026-08-23'],
            [
                (object) ['name' => 'id', 'editor' => 'line', 'generated' => 'date,title'],
                (object) ['name' => 'title', 'editor' => 'text'],
                (object) ['name' => 'date', 'editor' => 'date']
            ]
        );

		self::assertIsString($firstId);
		self::assertNotSame('', $firstId);
		self::assertNotSame($firstId, $secondId);
	}
	    public function testSlugify()
    {
        $this->assertEquals('aaaaaaaaaa', IdGeneratorUtils::slugify('aaaaaaaaaa'));
        $this->assertEquals('2026-08-23', IdGeneratorUtils::slugify('2026-08-23'));
        $this->assertEquals('hello-world', IdGeneratorUtils::slugify('Hello World!'));
        $this->assertEquals('ete-kendo', IdGeneratorUtils::slugify('Été kendo'));
        $this->assertEquals('', IdGeneratorUtils::slugify('   '));
    }


}
