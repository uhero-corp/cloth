<?php

namespace Cloth;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Cloth\OptionSet
 */
class OptionSetTest extends TestCase
{
    /**
     * @return OptionSet
     */
    private function createTestObject(): OptionSet
    {
        $options = [
            new Flag(new Specifier("force", "f"), true),
            new Flag(new Specifier("help", "h"), false),
            new Flag(new Specifier("recursive", "r"), true),
            new Flag(new Specifier("version", "v"), false),
            new Parameter(new Specifier("log-level", "l"), "all"),
            new Parameter(new Specifier("output-dir", "o"), "tmp"),
            new Parameter(new Specifier("with-sample"), "asdf"),
            new Parameter(new Specifier("with-test"), null),
        ];
        $args = ["aaaa.txt", "bbbb.html", "cccc.log"];
        return new OptionSet($options, $args);
    }

    /**
     * @covers ::__construct
     * @covers ::getOptionsAsArray
     */
    public function testGetOptionsAsArray(): void
    {
        $expected = [
            "force" => true,
            "help" => false,
            "recursive" => true,
            "version" => false,
            "log-level" => "all",
            "output-dir" => "tmp",
            "with-sample" => "asdf",
            "with-test" => null,
        ];
        $this->assertSame($expected, $this->createTestObject()->getOptionsAsArray());
    }

    /**
     * @param string $longName
     * @param Option $expected
     * @covers ::__construct
     * @covers ::getOptionByLongName
     * @dataProvider provideGetOptionByLongNameSuccess
     */
    public function testGetOptionByLongNameSuccess(string $longName, Option $expected): void
    {
        $obj = $this->createTestObject();
        $this->assertEquals($expected, $obj->getOptionByLongName($longName));
    }

    /**
     * @return array
     */
    public function provideGetOptionByLongNameSuccess(): array
    {
        return [
            ["force", new Flag(new Specifier("force", "f"), true)],
            ["help", new Flag(new Specifier("help", "h"), false)],
            ["output-dir", new Parameter(new Specifier("output-dir", "o"), "tmp")],
            ["with-test", new Parameter(new Specifier("with-test"), null)],
        ];
    }

    /**
     * @param string $longName
     * @covers ::__construct
     * @covers ::getOptionByLongName
     * @dataProvider provideGetOptionByLongNameFail
     */
    public function testGetOptionByLongNameFail(string $longName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createTestObject()->getOptionByLongName($longName);
    }

    /**
     * @return array
     */
    public function provideGetOptionByLongNameFail(): array
    {
        return [
            ["undefined-option"],
            ["--invalid format--"],
        ];
    }

    /**
     * @param string $shortName
     * @param Option $expected
     * @covers ::__construct
     * @covers ::getOptionByShortName
     * @dataProvider provideGetOptionByShortNameSuccess
     */
    public function testGetOptionByShortNameSuccess(string $shortName, Option $expected): void
    {
        $obj = $this->createTestObject();
        $this->assertEquals($expected, $obj->getOptionByShortName($shortName));
    }

    /**
     * @return array
     */
    public function provideGetOptionByShortNameSuccess(): array
    {
        return [
            ["h", new Flag(new Specifier("help", "h"), false)],
            ["f", new Flag(new Specifier("force", "f"), true)],
        ];
    }

    /**
     * @param string $shortName
     * @covers ::__construct
     * @covers ::getOptionByShortName
     * @dataProvider provideGetOptionByShortNameFail
     */
    public function testGetOptionByShortNameFail(string $shortName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createTestObject()->getOptionByShortName($shortName);
    }

    /**
     * @return array
     */
    public function provideGetOptionByShortNameFail(): array
    {
        return [
            ["x"],
            ["aaaa"],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::getArgs
     */
    public function testGetArgs(): void
    {
        $expected = ["aaaa.txt", "bbbb.html", "cccc.log"];
        $this->assertSame($expected, $this->createTestObject()->getArgs());
    }
}
