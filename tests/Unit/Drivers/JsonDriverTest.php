<?php

namespace Spatie\Snapshots\Test\Unit\Drivers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\Drivers\JsonDriver;
use Spatie\Snapshots\Exceptions\CantBeSerialized;

class JsonDriverTest extends TestCase
{
    #[Test]
    public function it_can_serialize_a_json_string_to_pretty_json()
    {
        $driver = new JsonDriver;

        $expected = implode("\n", [
            '{',
            '    "foo": "bar/baz 😊"',
            '}',
            '',
        ]);

        $this->assertEquals($expected, $driver->serialize('{"foo":"bar\/baz \ud83d\ude0a"}'));
    }

    #[Test]
    public function it_can_serialize_a_json_hash_to_pretty_json()
    {
        $driver = new JsonDriver;

        $expected = implode("\n", [
            '{',
            '    "foo": "FOO",',
            '    "bar": "BAR",',
            '    "baz": "BAZ"',
            '}',
            '',
        ]);
        $this->assertEquals($expected, $driver->serialize([
            'foo' => 'FOO',
            'bar' => 'BAR',
            'baz' => 'BAZ',
        ]));

        $expected = implode("\n", [
            '{',
            '    "foo": "FOO",',
            '    "bar": "BAR",',
            '    "baz": {',
            '        "aaa": "AAA",',
            '        "bbb": "BBB",',
            '        "ccc": [',
            '            "xxx",',
            '            "yyy",',
            '            "zzz"',
            '        ]',
            '    }',
            '}',
            '',
        ]);
        $this->assertEquals($expected, $driver->serialize([
            'foo' => 'FOO',
            'bar' => 'BAR',
            'baz' => [
                'aaa' => 'AAA',
                'bbb' => 'BBB',
                'ccc' => ['xxx', 'yyy', 'zzz'],
            ],
        ]));
    }

    #[Test]
    public function it_can_serialize_a_json_array_to_pretty_json()
    {
        $driver = new JsonDriver;

        $expected = implode("\n", [
            '[',
            '    "foo",',
            '    "bar",',
            '    "baz"',
            ']',
            '',
        ]);

        $this->assertEquals($expected, $driver->serialize(['foo', 'bar', 'baz']));
    }

    #[Test]
    public function it_can_serialize_a_empty_json_object_to_pretty_json()
    {
        $driver = new JsonDriver;

        $expected = implode("\n", [
            '{',
            '    "foo": {',
            '        "bar": true',
            '    },',
            '    "baz": {}',
            '}',
            '',
        ]);

        $this->assertEquals($expected, $driver->serialize((object) [
            'foo' => (object) [
                'bar' => true,
            ],
            'baz' => (object) [],
        ]));
    }

    #[Test]
    public function it_can_not_serialize_resources()
    {
        $driver = new JsonDriver;

        $this->expectException(CantBeSerialized::class);

        $resource = tmpfile();

        $driver->serialize($resource);
    }

    #[Test]
    #[TestWith(['{}', '{}', true])]
    #[TestWith(['{}', '{"data":1}', false])]
    #[TestWith(['{"data":1}', '{"data":1}', true])]
    #[TestWith(['{"data":1}', '{"data":"1"}', false])]
    #[TestWith(['true', 'true', true])]
    #[TestWith(['false', 'false', true])]
    #[TestWith(['null', 'null', true])]
    #[TestWith(['1', '1', true])]
    #[TestWith(['1.1', '1.1', true])]
    #[TestWith(['{"empty": []}', '{"empty":{}}', false])]
    #[TestWith(['{"url": "foo\/bar😊"}', '{"url":"foo/bar\ud83d\ude0a"}', true])]
    public function it_can_match_json_strings(string $expected, string $actual, bool $assertion)
    {
        $driver = new JsonDriver;

        try {
            $driver->match($expected, $actual);
            $status = true;
        } catch (ExpectationFailedException $th) {
            $status = false;
        }

        $this->assertSame($assertion, $status);
    }
}
