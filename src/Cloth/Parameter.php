<?php

namespace Cloth;

/**
 * 任意の文字列を値に持つコマンドラインオプションです。
 * ユーザーがこのクラスを直接インスタンス化する機会はありません。
 */
class Parameter implements Option
{
    /**
     * @var Specifier
     */
    private $specifier;

    /**
     * @var string
     */
    private $value;

    /**
     * @param Specifier $specifier
     * @param string $value このパラメータの値。未指定の場合は null
     */
    public function __construct(Specifier $specifier, string $value = null)
    {
        $this->specifier = $specifier;
        $this->value     = $value;
    }

    /**
     * @return Specifier
     */
    public function getSpecifier(): Specifier
    {
        return $this->specifier;
    }

    /**
     * このパラメータの値を返します。
     * このメソッドは空文字列と null を厳密に区別します。
     * パラメータの値として明示的に空文字列が指定されている場合は空文字列を返しますが、
     * パラメータそのものが未指定の場合は null を返します。
     *
     * @return string 値が設定されている場合はその文字列。それ以外は null
     */
    public function getValue()
    {
        return $this->value;
    }
}
