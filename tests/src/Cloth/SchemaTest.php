<?php

namespace Cloth;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Cloth\Schema
 */
class SchemaTest extends TestCase
{
    /**
     * @return Schema
     */
    private function createTestObject(): Schema
    {
        return (new Schema())
            ->flag("list", "l")
            ->flag("version", "v")
            ->flag("help")
            ->param("username", "u")
            ->param("password", "p")
            ->param("hostname", "h");
    }

    /**
     * @covers ::__construct
     * @covers ::addFlag
     * @covers ::<private>
     */
    public function testAddFlagSuccess(): void
    {
        $obj = $this->createTestObject();
        $this->assertSame($obj, $obj->addFlag("test"));
        $this->assertSame(Schema::TYPE_FLAG, $obj->getTypeByLongName("test"));
    }

    /**
     * @covers ::__construct
     * @covers ::flag
     * @covers ::<private>
     */
    public function testFlag(): void
    {
        $obj = $this->createTestObject();
        $this->assertSame($obj, $obj->flag("test"));
        $this->assertSame(Schema::TYPE_FLAG, $obj->getTypeByLongName("test"));
    }

    /**
     * @covers ::__construct
     * @covers ::addParameter
     * @covers ::<private>
     */
    public function testAddParameterSuccess(): void
    {
        $obj = $this->createTestObject();
        $this->assertSame($obj, $obj->addParameter("test-value"));
        $this->assertSame(Schema::TYPE_PARAMETER, $obj->getTypeByLongName("test-value"));
    }

    /**
     * @covers ::__construct
     * @covers ::param
     * @covers ::<private>
     */
    public function testParam(): void
    {
        $obj = $this->createTestObject();
        $this->assertSame($obj, $obj->addParameter("test-value"));
        $this->assertSame(Schema::TYPE_PARAMETER, $obj->getTypeByLongName("test-value"));
    }

    /**
     * @param string $longName
     * @param string $shortName
     * @covers ::__construct
     * @covers ::addFlag
     * @covers ::<private>
     * @dataProvider provideAddFlagFail
     */
    public function testAddFlagFail(string $longName, string $shortName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createTestObject();
        $obj->addFlag($longName, $shortName);
    }

    /**
     * @return array
     */
    public function provideAddFlagFail(): array
    {
        return [
            ["version", ""],
            ["verbose", "v"],
            ["human-readable", "h"],
        ];
    }

    /**
     * @param string $longName
     * @param string $shortName
     * @covers ::__construct
     * @covers ::addParameter
     * @covers ::<private>
     * @dataProvider provideAddParameterFail
     */
    public function testAddParameterFail(string $longName, string $shortName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createTestObject();
        $obj->addParameter($longName, $shortName);
    }

    /**
     * @return array
     */
    public function provideAddParameterFail(): array
    {
        return [
            ["username", ""],
            ["priority", "p"],
            ["volume", "v"],
        ];
    }

    /**
     * @param string $longName
     * @param int $expected
     * @covers ::__construct
     * @covers ::getTypeByLongName
     * @covers ::<private>
     * @dataProvider provideGetTypeByLongNameSuccess
     */
    public function testGetTypeByLongNameSuccess(string $longName, int $expected): void
    {
        $obj = $this->createTestObject();
        $this->assertSame($expected, $obj->getTypeByLongName($longName));
    }

    /**
     * @return array
     */
    public function provideGetTypeByLongNameSuccess(): array
    {
        return [
            ["help", Schema::TYPE_FLAG],
            ["hostname", Schema::TYPE_PARAMETER],
            ["verbose", Schema::TYPE_UNDEFINED],
        ];
    }

    /**
     * @param string $shortName
     * @param int $expected
     * @covers ::__construct
     * @covers ::getTypeByShortName
     * @covers ::<private>
     * @dataProvider provideGetTypeByShortNameSuccess
     */
    public function testGetTypeByShortNameSuccess(string $shortName, int $expected): void
    {
        $obj = $this->createTestObject();
        $this->assertSame($expected, $obj->getTypeByShortName($shortName));
    }

    /**
     * @return array
     */
    public function provideGetTypeByShortNameSuccess(): array
    {
        return [
            ["v", Schema::TYPE_FLAG],
            ["h", Schema::TYPE_PARAMETER],
            ["t", Schema::TYPE_UNDEFINED],
        ];
    }

    /**
     * @param string $longName
     * @covers ::__construct
     * @covers ::getTypeByLongName
     * @covers ::<private>
     * @dataProvider provideGetTypeByLongNameFail
     */
    public function testGetTypeByLongNameFail(string $longName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createTestObject();
        $obj->getTypeByLongName($longName);
    }

    /**
     * @return array
     */
    public function provideGetTypeByLongNameFail(): array
    {
        return [
            [""],
            ["test/key"],
            ["-test-key-"],
        ];
    }

    /**
     * @param string $shortName
     * @covers ::__construct
     * @covers ::getTypeByShortName
     * @covers ::<private>
     * @dataProvider provideGetTypeByShortNameFail
     */
    public function testGetTypeByShortNameFail(string $shortName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createTestObject();
        $obj->getTypeByShortName($shortName);
    }

    /**
     * @return array
     */
    public function provideGetTypeByShortNameFail(): array
    {
        return [
            [""],
            ["help"],
            ["$"],
        ];
    }

    /**
     * @param string $longName
     * @param Specifier $expected
     * @covers ::__construct
     * @covers ::getSpecifierByLongName
     * @covers ::<private>
     * @dataProvider provideGetSpecifierByLongName
     */
    public function testGetSpecifierByLongName(string $longName, Specifier $expected): void
    {
        $obj = $this->createTestObject();
        $this->assertEquals($expected, $obj->getSpecifierByLongName($longName));
    }

    /**
     * @return array
     */
    public function provideGetSpecifierByLongName(): array
    {
        return [
            ["help", new Specifier("help")],
            ["version", new Specifier("version", "v")],
            ["username", new Specifier("username", "u")],
        ];
    }

    /**
     * @param string $shortName
     * @param Specifier $expected
     * @covers ::__construct
     * @covers ::getSpecifierByShortName
     * @covers ::<private>
     * @dataProvider provideGetSpecifierByShortName
     */
    public function testGetSpecifierByShortName(string $shortName, Specifier $expected): void
    {
        $obj = $this->createTestObject();
        $this->assertEquals($expected, $obj->getSpecifierByShortName($shortName));
    }

    /**
     * @return array
     */
    public function provideGetSpecifierByShortName(): array
    {
        return [
            ["v", new Specifier("version", "v")],
            ["h", new Specifier("hostname", "h")],
        ];
    }

    /**
     * @param string $longName
     * @covers ::__construct
     * @covers ::getSpecifierByLongName
     * @covers ::<private>
     * @dataProvider provideGetSpecifierByLongNameFail
     */
    public function testGetSpecifierByLongNameFail(string $longName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createTestObject();
        $obj->getSpecifierByLongName($longName);
    }

    /**
     * @return array
     */
    public function provideGetSpecifierByLongNameFail(): array
    {
        return [
            [""],
            ["invalid--name"],
            ["undefined-name"],
        ];
    }

    /**
     * @param string $shortName
     * @covers ::__construct
     * @covers ::getSpecifierByShortName
     * @covers ::<private>
     * @dataProvider provideGetSpecifierByShortNameFail
     */
    public function testGetSpecifierByShortNameFail(string $shortName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createTestObject();
        $obj->getSpecifierByShortName($shortName);
    }

    /**
     * @return array
     */
    public function provideGetSpecifierByShortNameFail(): array
    {
        return [
            [""],
            ["invalid"],
            ["n"],
        ];
    }

    /**
     * @param array $shortNames
     * @param bool $expected
     * @covers ::__construct
     * @covers ::checkShortNames
     * @dataProvider provideCheckShortNames
     */
    public function testCheckShortNames(array $shortNames, bool $expected): void
    {
        $obj = $this->createTestObject();
        $this->assertSame($expected, $obj->checkShortNames($shortNames));
    }

    /**
     * @return array
     */
    public function provideCheckShortNames(): array
    {
        return [
            [[], false],
            [["v", "l"], true],
            [["v", "x", "l"], false],
            [["h", "u"], false],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::getFlagNames
     * @covers ::<private>
     */
    public function testGetFlagNames(): void
    {
        $obj      = $this->createTestObject();
        $expected = ["help", "list", "version"];
        $this->assertSame($expected, $obj->getFlagNames());
    }

    /**
     * @covers ::__construct
     * @covers ::getParameterNames
     * @covers ::<private>
     */
    public function testGetParameterNames(): void
    {
        $obj      = $this->createTestObject();
        $expected = ["hostname", "password", "username"];
        $this->assertSame($expected, $obj->getParameterNames());
    }
}
