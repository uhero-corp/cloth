<?php

namespace Cloth;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Cloth\Context
 */
class ContextTest extends TestCase
{
    /**
     * @return Schema
     */
    private function createTestSchema(): Schema
    {
        return (new Schema())
            ->flag("list", "l")
            ->flag("quiet", "q")
            ->flag("dry-run")
            ->flag("version", "v")
            ->flag("help")
            ->param("config", "c")
            ->param("output-dir", "d")
            ->param("username", "u")
            ->param("password", "p")
            ->param("hostname", "h");
    }

    /**
     * @param string[] $args
     * @param array $expectedValues
     * @param string[] $expectedArgs
     * @covers ::__construct
     * @covers ::parse
     * @covers ::<private>
     * @dataProvider provideParseSuccess
     */
    public function testParseSuccess(array $args, array $expectedValues, array $expectedArgs): void
    {
        $expectedOpts = array_combine(["dry-run", "help", "list", "quiet", "version", "config", "hostname", "output-dir", "password", "username"], $expectedValues);
        $result       = (new Context($args))->parse($this->createTestSchema());
        $this->assertSame($expectedOpts, $result->getOptionsAsArray());
        $this->assertSame($expectedArgs, $result->getArgs());
    }

    /**
     * @return array
     */
    public function provideParseSuccess(): array
    {
        return [
            [[], [false, false, false, false, false, null, null, null, null, null], []],
            [["-v"], [false, false, false, false, true, null, null, null, null, null], []],
            [["-ql", "xxx", "yyy"], [false, false, true, true, false, null, null, null, null, null], ["xxx", "yyy"]],
            [["--dry-run", "--hostname=localhost", "--username=root", "--password="], [true, false, false, false, false, null, "localhost", null, "", "root"], []],
            [["-l", "aaa", "-h", "192.168.1.2", "-p", "xxxx", "-u", "devuser"], [false, false, true, false, false, null, "192.168.1.2", null, "xxxx", "devuser"], ["aaa"]],
            // "--" の後ろにある引数はすべて、オプションではなく値として扱います
            [["-q", "--config", "settings.ini", "--", "aaa", "--dry-run", "-h", "localhost"], [false, false, false, true, false, "settings.ini", null, null, null, null], ["aaa", "--dry-run", "-h", "localhost"]],
        ];
    }

    /**
     * @param string[] $args
     * @covers ::__construct
     * @covers ::parse
     * @covers ::<private>
     * @dataProvider provideParseFail
     */
    public function testParseFail(array $args): void
    {
        $this->expectException(ParseException::class);
        (new Context($args))->parse($this->createTestSchema());
    }

    /**
     * @return array
     */
    public function provideParseFail(): array
    {
        return [
            [["-x"]],
            [["-lqh"]],
            [["--hogehoge"]],
            [["xxx", "yyy", "-h"]],
            [["--username=root", "--host=localhost"]],
            [["--version=1.0.0"]],
        ];
    }
}
