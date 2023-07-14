<?php

namespace Cloth;

/**
 * ON, OFF の 2 種類の状態を持つコマンドラインオプションです。
 * ユーザーがこのクラスを直接インスタンス化する機会はありません。
 */
class Flag implements Option
{
    /**
     * @var Specifier
     */
    private $specifier;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @param Specifier $s
     * @param bool $enabled ON の場合は true, OFF (未指定) の場合は false
     */
    public function __construct(Specifier $s, bool $enabled = false)
    {
        $this->specifier = $s;
        $this->enabled   = $enabled;
    }

    /**
     * @return Specifier
     */
    public function getSpecifier(): Specifier
    {
        return $this->specifier;
    }

    /**
     * @return bool ON の場合は true, それ以外は false
     */
    public function getValue()
    {
        return $this->enabled;
    }
}
