<?php

namespace Cloth;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Cloth\Parameter
 */
class ParameterTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getSpecifier
     */
    public function testGetSpecifier(): void
    {
        $s   = new Specifier("file", "f");
        $obj = new Parameter($s, "/tmp/sample.txt");
        $this->assertSame($s, $obj->getSpecifier());
    }

    /**
     * @covers ::__construct
     * @covers ::getValue
     */
    public function testGetValue(): void
    {
        $s    = new Specifier("time", "t");
        $obj1 = new Parameter($s, "2012-05-21 07:33");
        $this->assertSame("2012-05-21 07:33", $obj1->getValue());
        $obj2 = new Parameter($s);
        $this->assertNull($obj2->getValue());
    }
}
