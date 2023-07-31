<?php

namespace Cloth;

use LogicException;

/**
 * 入力されたコマンドラインオプションを解析するためのクラスです。
 * このクラスは Schema クラスから参照されます。
 * ユーザーが直接このクラスをインスタンス化する機会はありません。
 *
 * @ignore
 */
class Context
{
    /**
     * @var int
     */
    const MODE_DEFAULT = -1;

    /**
     * @var int
     */
    const MODE_FINISHED = 0;

    /**
     * @var int
     */
    const MODE_PARAMETER_VALUE = 1;

    /**
     * @var int
     */
    const MODE_ARGUMENTS = 2;

    /**
     * @var Specifier
     */
    private $next;

    /**
     * @var string[]
     */
    private $inputs;

    /**
     * @var int
     */
    private $index;

    /**
     * @var int
     */
    private $mode;

    /**
     * @var Option[]
     */
    private $shortOpts;

    /**
     * @var Option[]
     */
    private $longOpts;

    /**
     * @var string[]
     */
    private $args;

    /**
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        $this->inputs    = array_values($argv);
        $this->index     = 0;
        $this->mode      = self::MODE_DEFAULT;
        $this->shortOpts = [];
        $this->longOpts  = [];
        $this->args      = [];
    }

    /**
     * @param Schema $schema
     * @return OptionSet
     */
    public function parse(Schema $schema): OptionSet
    {
        while (($mode = $this->next($schema)) !== self::MODE_FINISHED) {
            $this->mode = $mode;
            $this->index++;
        }

        $longOpts = $this->longOpts;
        $options  = [];
        foreach ($schema->getFlagNames() as $name) {
            $flag      = $longOpts[$name] ?? new Flag($schema->getSpecifierByLongName($name));
            $options[] = $flag;
        }
        foreach ($schema->getParameterNames() as $name) {
            $param     = $longOpts[$name] ?? new Parameter($schema->getSpecifierByLongName($name));
            $options[] = $param;
        }

        return new OptionSet($options, $this->args);
    }

    /**
     * @param Schema $schema
     * @return int
     * @throws ParseException
     */
    private function next(Schema $schema): int
    {
        $index = $this->index;
        $mode  = $this->mode;
        if ($index >= count($this->inputs)) {
            if ($mode === self::MODE_PARAMETER_VALUE) {
                throw new ParseException("Option value is required: ({$this->next})");
            }
            return self::MODE_FINISHED;
        }

        $str = (string) $this->inputs[$index];
        if ($mode === self::MODE_ARGUMENTS) {
            $this->args[] = $str;
            return self::MODE_ARGUMENTS;
        }

        if ($mode === self::MODE_PARAMETER_VALUE) {
            $this->registerParameter($this->next, $str);
            return self::MODE_DEFAULT;
        }

        if ($str === "--") {
            return self::MODE_ARGUMENTS;
        }

        $matched = [];
        if (preg_match("/\\A-([a-zA-Z0-9])\\z/", $str, $matched)) {
            return $this->handleSingleShortName($matched[1], $schema);
        }
        if (preg_match("/\\A-([a-zA-Z0-9]+)\\z/", $str, $matched)) {
            return $this->handleMultiShortNames($matched[1], $schema);
        }
        if (preg_match("/\\A--([a-zA-Z0-9]+(-[a-zA-Z0-9]+)*)\\z/", $str, $matched)) {
            return $this->handleLongName($matched[1], $schema);
        }
        if (preg_match("/\\A--([a-zA-Z0-9]+(-[a-zA-Z0-9]+)*)=(.*)\\z/", $str, $matched)) {
            return $this->handleParameterWithValue($matched[1], $matched[3], $schema);
        }

        $this->args[] = $str;
        return self::MODE_DEFAULT;
    }

    /**
     * @param string $shortName
     * @param Schema $schema
     * @return int
     * @throws ParseException
     */
    private function handleSingleShortName(string $shortName, Schema $schema): int
    {
        $type = $schema->getTypeByShortName($shortName);
        if ($type === Schema::TYPE_UNDEFINED) {
            throw new ParseException("Undefined option: -{$shortName}");
        }

        $s = $schema->getSpecifierByShortName($shortName);
        if ($type === Schema::TYPE_FLAG) {
            $this->registerFlag($s);
            return self::MODE_DEFAULT;
        }
        if ($type === Schema::TYPE_PARAMETER) {
            $this->next = $s;
            return self::MODE_PARAMETER_VALUE;
        }

        // @codeCoverageIgnoreStart
        throw new LogicException("Invalid schema type: {$type}");
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param string $longName
     * @param Schema $schema
     * @return int
     * @throws ParseException
     */
    private function handleLongName(string $longName, Schema $schema): int
    {
        $type = $schema->getTypeByLongName($longName);
        if ($type === Schema::TYPE_UNDEFINED) {
            throw new ParseException("Undefined option: --{$longName}");
        }

        $s = $schema->getSpecifierByLongName($longName);
        if ($type === Schema::TYPE_FLAG) {
            $this->registerFlag($s);
            return self::MODE_DEFAULT;
        }
        if ($type === Schema::TYPE_PARAMETER) {
            $this->next = $s;
            return self::MODE_PARAMETER_VALUE;
        }

        // @codeCoverageIgnoreStart
        throw new LogicException("Invalid schema type: {$type}");
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param string $shortNames
     * @param Schema $schema
     * @return int
     * @throws ParseException
     */
    private function handleMultiShortNames(string $shortNames, Schema $schema): int
    {

        if (!$schema->checkShortNames(str_split($shortNames))) {
            throw new ParseException("Invalid options: -{$shortNames}");
        }
        foreach (str_split($shortNames) as $shortName) {
            $this->registerFlag($schema->getSpecifierByShortName($shortName));
        }
        return self::MODE_DEFAULT;
    }

    /**
     * @param string $longName
     * @param string $value
     * @param Schema $schema
     * @return int
     * @throws ParseException
     */
    private function handleParameterWithValue(string $longName, string $value, Schema $schema): int
    {
        $type = $schema->getTypeByLongName($longName);
        if ($type === Schema::TYPE_UNDEFINED) {
            throw new ParseException("Undefined option: --{$longName}");
        }
        if ($type !== Schema::TYPE_PARAMETER) {
            throw new ParseException("Cannot specify values: --{$longName}");
        }

        $s = $schema->getSpecifierByLongName($longName);
        $this->registerParameter($s, $value);
        return self::MODE_DEFAULT;
    }

    /**
     * @param Option $opt
     */
    private function registerOption(Option $opt): void
    {
        $s         = $opt->getSpecifier();
        $longName  = $s->getLongName();
        $shortName = $s->getShortName();

        $this->longOpts[$longName] = $opt;
        if (strlen($shortName)) {
            $this->shortOpts[$shortName] = $opt;
        }
    }

    /**
     * @param Specifier $s
     * @param bool $enabled
     */
    private function registerFlag(Specifier $s, bool $enabled = true): void
    {
        $this->registerOption(new Flag($s, $enabled));
    }

    /**
     * @param Specifier $s
     * @param string $value
     */
    private function registerParameter(Specifier $s, string $value): void
    {
        $this->registerOption(new Parameter($s, $value));
    }
}
