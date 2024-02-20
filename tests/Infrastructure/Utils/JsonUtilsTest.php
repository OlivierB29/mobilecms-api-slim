<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Utils;

use App\Infrastructure\Utils\JsonUtils;
use PHPUnit\Framework\TestCase;

final class JsonUtilsTest extends TestCase
{
    public function testCanRead()
    {
        $this->assertJsonStringEqualsJsonString(
            '{}',
            json_encode(JsonUtils::readJsonFile('tests-data/jsonutils/mini.json'))
        );
    }

    public function testNewDirectory()
    {
        JsonUtils::writeJsonFile('tests-data/newpath/test.json', \json_decode('{}'));
        $this->assertTrue(file_exists('tests-data/newpath/test.json'));
    }

    /**
     * doesn't work in docker, since root is the owner of the file.
     */
    /*public function testWriteException()
    {
        $this->expectException(\Exception::class);
        JsonUtils::writeJsonFile('/randomdirectory5444554654/test.json', \json_decode('{}'));
    }
*/
    public function testGetByKey()
    {
        $data = JsonUtils::readJsonFile('tests-data/jsonutils/test.json');

        $item = JsonUtils::getByKey($data, 'id', '1');

        $this->assertJsonStringEqualsJsonString(
            '{"id":"1", "foo":"bar"}',
            json_encode($item)
        );
    }

    public function testUpdateByKey()
    {
        $data = JsonUtils::readJsonFile('tests-data/jsonutils/test.json');

        $item = JsonUtils::getByKey($data, 'id', '1');

        $item->{'foo'} = 'pub';

        $this->assertJsonStringEqualsJsonString(
            '{"id":"1", "foo":"pub"}',
            json_encode($item)
        );

        $this->assertJsonStringEqualsJsonString(
            '[{"id":"1", "foo":"pub"},{"id":"2", "foo":"bar"}]',
            json_encode($data)
        );
    }

    public function testCopy()
    {
        $data = JsonUtils::readJsonFile('tests-data/jsonutils/test.json');

        $item = JsonUtils::getByKey($data, 'id', '1');

        $this->assertJsonStringEqualsJsonString(
            '{"id":"1", "foo":"bar"}',
            json_encode($item)
        );

        $newItem = json_decode('{"id":"1", "foo":"pub"}');

        $this->assertTrue(
            $newItem != null
        );

        JsonUtils::copy($newItem, $item);

        $this->assertJsonStringEqualsJsonString(
            '{"id":"1", "foo":"pub"}',
            json_encode($item)
        );

        $this->assertJsonStringEqualsJsonString(
            '[{"id":"1", "foo":"pub"},{"id":"2", "foo":"bar"}]',
            json_encode($data)
        );
    }

    public function testReplace()
    {
        $data = JsonUtils::readJsonFile('tests-data/jsonutils/test.json');

        $item = JsonUtils::getByKey($data, 'id', '1');

        $this->assertJsonStringEqualsJsonString(
            '{"id":"1", "foo":"bar"}',
            json_encode($item)
        );

        $newItem = json_decode('{"id":"1", "foo":"pub" , "hello":"world"}');

        $this->assertTrue(
            $newItem != null
        );

        JsonUtils::replace($newItem, $item);

        $this->assertJsonStringEqualsJsonString(
            '{"id":"1", "foo":"pub", "hello":"world"}',
            json_encode($item)
        );

        $this->assertJsonStringEqualsJsonString(
            '[{"id":"1", "foo":"pub", "hello":"world"},{"id":"2", "foo":"bar"}]',
            json_encode($data)
        );
    }

    public function testPutExistingItem()
    {
        $data = JsonUtils::readJsonFile('tests-data/jsonutils/test.json');

        $item = json_decode('{"id":"1", "foo":"pub"}');
        $data = JsonUtils::put($data, 'id', $item);

        $this->assertJsonStringEqualsJsonString(
            '[{"id":"2", "foo":"bar"},{"id":"1", "foo":"pub"}]',
            json_encode($data)
        );
    }

    public function testPutNewItem()
    {
        $data = JsonUtils::readJsonFile('tests-data/jsonutils/test.json');

        $item = json_decode('{"id":"100", "foo":"bar"}');
        $data = JsonUtils::put($data, 'id', $item);

        $this->assertJsonStringEqualsJsonString(
            '[{"id":"1", "foo":"bar"},{"id":"2", "foo":"bar"},{"id":"100", "foo":"bar"}]',
            json_encode($data)
        );
    }
}
