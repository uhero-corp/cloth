<?php

namespace Cloth;

use InvalidArgumentException;

/**
 * コマンドラインオプションの構文を定義するクラスです。
 *
 * 各オプションは long name と short name の 2 種類の名前を持ちます。
 * long name は必須ですが short name は任意指定が可能です。
 *
 * long name として妥当なフォーマットは、半角英数 (a-zA-Z0-9) で構成される単語や、
 * 各単語をハイフンで繋げた文字列です。(例: "version", "sample-option-var1" など)
 * コマンドラインオプションでハイフン 2 つに繋げる形で指定されます。
 * (例: --sample-option-var1)
 * 値を持つオプションの場合、オプションの末尾にイコールと共に値を指定することができます。
 * (例: --sample-option-var1=test)
 *
 * short name は半角英数 (a-zA-Z0-9) 1 文字のみの文字列です。
 * 単独のハイフンに繋げる形で指定されます。(例: -h)
 *
 * オプションには、値を持たないもの (フラグ形式) と値を持つもの (パラメータ形式) があります。
 * フラグ形式のオプションの場合、ハイフンに続けて複数の short name を同時に指定することができます。(例: -abc)
 * パラメータ形式のオプションについてはこの書式で同時に指定することはできません。
 */
class Schema
{
    /**
     * 存在しないオプションをあらわす定数です
     *
     * @var int
     */
    const TYPE_UNDEFINED = 0;

    /**
     * フラグ形式のオプションをあらわす定数です
     *
     * @var int
     */
    const TYPE_FLAG = 1;

    /**
     * パラメータ形式のオプションをあらわす定数です
     *
     * @var int
     */
    const TYPE_PARAMETER = 2;

    /**
     * @var Specifier[]
     */
    private $flagNames;

    /**
     * @var Specifier[]
     */
    private $paramNames;

    /**
     * 新しい Schema インスタンスを生成します。
     */
    public function __construct()
    {
        $this->flagNames  = [];
        $this->paramNames = [];
    }

    /**
     * @param string $longName
     * @param string $shortName
     * @return Specifier
     * @throws InvalidArgumentException 引数 $longName と $shortName のいずれかが既に登録済の場合
     */
    private function createSpecifier(string $longName, string $shortName): Specifier
    {
        $s = new Specifier($longName, $shortName);
        if (!$this->checkDuplicate($s, $this->flagNames) || !$this->checkDuplicate($s, $this->paramNames)) {
            throw new InvalidArgumentException("Specified names are already registered ({$s})");
        }
        return $s;
    }

    /**
     * @param Specifier $subject
     * @param Specifier[] $sList
     * @return bool
     */
    private function checkDuplicate(Specifier $subject, array $sList)
    {
        foreach ($sList as $s) {
            if ($subject->overlaps($s)) {
                return false;
            }
        }
        return true;
    }

    /**
     * フラグ形式の構文を追加します。
     *
     * @param string $longName
     * @param string $shortName
     * @return Schema このオブジェクト
     */
    public function addFlag(string $longName, string $shortName = ""): self
    {
        $this->flagNames[] = $this->createSpecifier($longName, $shortName);
        return $this;
    }

    /**
     * addFlag() のエイリアスです。
     *
     * @param string $longName
     * @param string $shortName
     * @return Schema このオブジェクト
     */
    public function flag(string $longName, string $shortName = ""): self
    {
        return $this->addFlag($longName, $shortName);
    }

    /**
     * パラメータ形式の構文を追加します。
     *
     * @param string $longName
     * @param string $shortName
     * @return Schema このオブジェクト
     */
    public function addParameter(string $longName, string $shortName = ""): self
    {
        $this->paramNames[] = $this->createSpecifier($longName, $shortName);
        return $this;
    }

    /**
     * addParameter() のエイリアスです。
     *
     * @param string $longName
     * @param string $shortName
     * @return Schema このオブジェクト
     */
    public function param(string $longName, string $shortName = ""): self
    {
        return $this->addParameter($longName, $shortName);
    }

    /**
     * @param string $longName
     * @param array $sList
     * @return bool
     */
    private function matchLongName(string $longName, array $sList): bool
    {
        foreach ($sList as $s) {
            if ($s->getLongName() === $longName) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $shortName
     * @param Specifier[] $sList
     * @return bool
     */
    private function matchShortName(string $shortName, array $sList): bool
    {
        foreach ($sList as $s) {
            if ($s->getShortName() === $shortName) {
                return true;
            }
        }
        return false;
    }

    /**
     * 指定された long name の種類を調べます。
     *
     * @param string $longName
     * @return int オプションの種類をあらわす定数
     */
    public function getTypeByLongName(string $longName): int
    {
        Specifier::validateLongName($longName);
        if ($this->matchLongName($longName, $this->flagNames)) {
            return self::TYPE_FLAG;
        }
        if ($this->matchLongName($longName, $this->paramNames)) {
            return self::TYPE_PARAMETER;
        }
        return self::TYPE_UNDEFINED;
    }

    /**
     * 指定された short name の種類を調べます。
     *
     * @param string $shortName
     * @return int オプションの種類をあらわす定数
     */
    public function getTypeByShortName(string $shortName): int
    {
        Specifier::validateShortName($shortName);
        if ($this->matchShortName($shortName, $this->flagNames)) {
            return self::TYPE_FLAG;
        }
        if ($this->matchShortName($shortName, $this->paramNames)) {
            return self::TYPE_PARAMETER;
        }
        return self::TYPE_UNDEFINED;
    }

    /**
     * 指定された long name を持つ Specifier オブジェクトを返します。
     * 存在しない場合は InvalidArgumentException をスローします。
     *
     * @param string $longName
     * @return Specifier
     * @throws InvalidArgumentException
     */
    public function getSpecifierByLongName(string $longName): Specifier
    {
        Specifier::validateLongName($longName);
        foreach ($this->flagNames as $s) {
            if ($s->getLongName() === $longName) {
                return $s;
            }
        }
        foreach ($this->paramNames as $s) {
            if ($s->getLongName() === $longName) {
                return $s;
            }
        }
        throw new InvalidArgumentException("This long name is not registered ('{$longName}')");
    }

    /**
     * 指定された short name を持つ Specifier オブジェクトを返します。
     * 存在しない場合は InvalidArgumentException をスローします。
     *
     * @param string $shortName
     * @return Specifier
     * @throws InvalidArgumentException
     */
    public function getSpecifierByShortName(string $shortName): Specifier
    {
        Specifier::validateShortName($shortName);
        foreach ($this->flagNames as $s) {
            if ($s->getShortName() === $shortName) {
                return $s;
            }
        }
        foreach ($this->paramNames as $s) {
            if ($s->getShortName() === $shortName) {
                return $s;
            }
        }
        throw new InvalidArgumentException("This short name is not registered: '{$shortName}'");
    }

    /**
     * 指定された文字列配列について、それぞれフラグ形式のオプションの short name として有効かどうかを調べます。
     * 引数が空配列の場合と、無効な short name が 1 つ以上あった場合は false を返します。
     *
     * @param string[] $shortNames short name の一覧
     * @return bool すべての short name がフラグ形式として有効の場合のみ true
     */
    public function checkShortNames(array $shortNames): bool
    {
        if (!count($shortNames)) {
            return false;
        }

        $getShortName = function (Specifier $s): string {
            return $s->getShortName();
        };
        $validList = array_map($getShortName, $this->flagNames);
        foreach ($shortNames as $name) {
            if (!in_array($name, $validList, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Specifier[] $arr
     * @return string[]
     */
    private function getNamesByArray(array $arr): array
    {
        $getLongName = function (Specifier $s): string {
            return $s->getLongName();
        };
        $result = array_map($getLongName, $arr);
        sort($result, SORT_STRING);
        return $result;
    }

    /**
     * このオブジェクトに設定されているフラグ形式のオプションの一覧を
     * long name の配列として返します。
     *
     * @return string[]
     */
    public function getFlagNames(): array
    {
        return $this->getNamesByArray($this->flagNames);
    }

    /**
     * このオブジェクトに設定されているパラメータ形式のオプションの一覧を
     * long name の配列として返します。
     *
     * @return string[]
     */
    public function getParameterNames(): array
    {
        return $this->getNamesByArray($this->paramNames);
    }
}
