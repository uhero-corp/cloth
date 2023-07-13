<?php

namespace Cloth;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Cloth\Specifier
 */
class SpecifierTest extends TestCase
{
    /**
     * @param string $shortName
     * @dataProvider provideValidateShortNameSuccess
     * @covers ::validateShortName
     */
    public function testValidateShortNameSuccess(string $shortName): void
    {
        $this->expectNotToPerformAssertions();
        Specifier::validateShortName($shortName);
    }

    /**
     * @return array
     */
    public function provideValidateShortNameSuccess(): array
    {
        return [
            ["a"],
            ["A"],
            ["1"],
        ];
    }

    /**
     * @param string $shortName
     * @dataProvider provideValidateShortNameFail
     * @covers ::validateShortName
     */
    public function testValidateShortNameFail(string $shortName): void
    {
       $this->expectException(InvalidArgumentException::class);
       Specifier::validateShortName($shortName);
    }

    /**
     * @return array
     */
    public function provideValidateShortNameFail(): array
    {
        return [
            [""],
            ["?"],
            ["abc"],
        ];
    }

    /**
     * @param string $longName
     * @dataProvider provideValidateLongNameSuccess
     * @covers ::validateLongName
     */
    public function testValidateLongNameSuccess(string $longName): void
    {
        $this->expectNotToPerformAssertions();
        Specifier::validateLongName($longName);
    }

    /**
     * @return array
     */
    public function provideValidateLongNameSuccess(): array
    {
        return [
            ["test"],
            ["test-data"],
            ["test-123"],
        ];
    }

    /**
     * @param string $longName
     * @dataProvider provideValidateLongNameFail
     * @covers ::validateLongName
     */
    public function testValidateLongNameFail(string $longName): void
    {
        $this->expectException(InvalidArgumentException::class);
        Specifier::validateLongName($longName);
    }

    /**
     * @return array
     */
    public function provideValidateLongNameFail(): array
    {
        return [
            [""],
            ["aaa bbb"],
            ["aaa+bbb"],
            ["a--b--c"],
            ["-test-data"],
            ["test-data-"],
        ];
    }

    /**
     * @param string $longName
     * @param string $shortName
     * @covers ::__construct
     * @dataProvider provideConstructFail
     */
    public function testConstructFail(string $longName, string $shortName): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Specifier($longName, $shortName);
    }

    /**
     * @return array
     */
    public function provideConstructFail(): array
    {
        return [
            ["", ""],
            ["", "a"],
            ["help", "aaa"],
            ["sample--word", "a"],
        ];
    }

    /**
     * @param string $longName
     * @param string $shortName
     * @covers ::__construct
     * @dataProvider provideConstructSuccess
     */
    public function provideConstructSuccess(string $longName, string $shortName): void
    {
        $this->expectNotToPerformAssertions();
        new Specifier($longName, $shortName);
    }

    /**
     * @covers ::__construct
     * @covers ::getLongName
     */
    public function testGetLongName(): void
    {
        $obj = new Specifier("version", "v");
        $this->assertSame("version", $obj->getLongName());
    }

    /**
     * @covers ::__construct
     * @covers ::getShortName
     */
    public function testGetShortName(): void
    {
        $obj = new Specifier("version", "v");
        $this->assertSame("v", $obj->getShortName());
    }

    /**
     * @param Specifier $s1
     * @param Specifier $s2
     * @param bool $expected
     * @covers ::overlaps
     * @dataProvider provideOverlaps
     */
    public function testOverlaps(Specifier $s1, Specifier $s2, bool $expected)
    {
        $this->assertSame($expected, $s1->overlaps($s2));
    }

    /**
     * @return array
     */
    public function provideOverlaps(): array
    {
        $s1 = new Specifier("version");
        $s2 = new Specifier("version", "v");
        $s3 = new Specifier("verbose", "v");
        $s4 = new Specifier("help", "h");
        return [
            [$s1, $s1, true],
            [$s2, $s2, true],
            [$s1, $s2, true],
            [$s2, $s3, true],
            [$s1, $s3, false],
            [$s2, $s4, false],
        ];
    }

    /**
     * @param Specifier $s
     * @param string $expected
     * @covers ::__toString
     * @dataProvider provideToString
     */
    public function testToString(Specifier $s, string $expected): void
    {
        $this->assertSame($expected, $s->__toString());
    }

    /**
     * @return array
     */
    public function provideToString(): array
    {
        return [
            [new Specifier("help", ""), "long name: 'help'"],
            [new Specifier("version", "v"), "long name: 'version', short name: 'v'"],
        ];
    }
}
