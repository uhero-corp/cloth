<?php

namespace Cloth;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Cloth\Flag
 */
class FlagTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getSpecifier
     */
    public function testGetSpecifier(): void
    {
        $s   = new Specifier("help", "h");
        $obj = new Flag($s, true);
        $this->assertSame($s, $obj->getSpecifier());
    }

    /**
     * @param bool $value
     * @covers ::__construct
     * @covers ::getValue
     * @dataProvider provideTestGetValue
     */
    public function testGetValue(bool $value): void
    {
        $s   = new Specifier("version", "v");
        $obj = new Flag($s, $value);
        $this->assertSame($value, $obj->getValue());
    }

    /**
     * @return array
     */
    public function provideTestGetValue(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
