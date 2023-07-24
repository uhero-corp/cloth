<?php

namespace Cloth;

use InvalidArgumentException;

/**
 * 指定されたコマンドラインオプションおよび引数を取得するためのクラスです。
 * このクラスはコマンドライン引数の解析実行時にインスタンス化されます。
 * ユーザーが直接このクラスをインスタンス化する機会はありません。
 */
class OptionSet
{
    /**
     * @var Option[]
     */
    private $options;

    /**
     * @var string[]
     */
    private $args;

    /**
     * @param Option[] $options
     * @param string[] $args
     * @ignore
     */
    public function __construct(array $options, array $args)
    {
        $this->options = $options;
        $this->args    = $args;
    }

    /**
     * コマンドラインオプションの一覧を配列として返します。
     * 返り値の配列は、キーが各オプションの long name, 値がそのオプションの設定値に対応します。
     *
     * 返り値の配列には未指定のオプションも含まれます。
     * 未指定のフラグでは値が false, 未指定のパラメータでは値が null となります。
     *
     * @return array
     */
    public function getOptionsAsArray(): array
    {
        $result = [];
        foreach ($this->options as $option) {
            $longName          = $option->getSpecifier()->getLongName();
            $result[$longName] = $option->getValue();
        }
        return $result;
    }

    /**
     * 指定された long name のオプションを返します。
     *
     * @param string $longName
     * @return Option
     * @throws InvalidArgumentException 指定された long name が定義されていない場合
     */
    public function getOptionByLongName(string $longName): Option
    {
        Specifier::validateLongName($longName);
        foreach  ($this->options as $option) {
            if ($longName === $option->getSpecifier()->getLongName()) {
                return $option;
            }
        }
        throw new InvalidArgumentException("Undefined option: --{$longName}");
    }

    /**
     * 指定された short name のオプションを返します。
     *
     * @param string $shortName
     * @return Option
     * @throws InvalidArgumentException 指定された short name が定義されていない場合
     */
    public function getOptionByShortName(string $shortName): Option
    {
        Specifier::validateShortName($shortName);
        foreach ($this->options as $option) {
            if ($shortName === $option->getSpecifier()->getShortName()) {
                return $option;
            }
        }
        throw new InvalidArgumentException("Undefined option: -{$shortName}");
    }

    /**
     * コマンドライン引数 (すべての引数のうちコマンドラインオプションを除いたもの)
     * の一覧を文字列配列として返します。
     *
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
}
