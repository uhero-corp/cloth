<?php

namespace Cloth;

use InvalidArgumentException;

/**
 * 各コマンドラインオプションを識別するための指示子をあらわすクラスです。
 * 各指示子は long name と short name の 2 種類の識別子を持ちます。
 * long name はどの指示子にも存在しますが short name は存在しない場合があります。
 *
 * ユーザーがこのクラスを直接インスタンス化する機会はありません。
 */
class Specifier
{
    /**
     * @var string
     */
    private $longName;

    /**
     * @var string
     */
    private $shortName;

    /**
     * @param string $longName long name
     * @param string $shortName short name
     * @throws InvalidArgumentException 引数 $longName が空文字列の場合
     * @ignore
     */
    public function __construct(string $longName, string $shortName = "")
    {
        $tLong  = trim($longName);
        $tShort = trim($shortName);
        if (!strlen($tLong)) {
            throw new InvalidArgumentException("Long name is required");
        }
        self::validateLongName($tLong);
        strlen($tShort) && self::validateShortName($tShort);

        $this->longName  = $tLong;
        $this->shortName = $tShort;
    }

    /**
     * 指定された文字列が short name として妥当かどうかを調べます。
     * 文字列が半角英数 (a-zA-Z0-9) の 1 文字からなる場合のみ OK とします。
     *
     * @param string $shortName 検査対象の文字列
     * @throws IllegalArgumentException 検査対象の文字列が short name として不適切だった場合
     */
    public static function validateShortName(string $shortName): void
    {
        if (!preg_match("/\\A[a-zA-Z0-9]\\z/", $shortName)) {
            throw new InvalidArgumentException("A short name be a single ASCII character ('{$shortName}')");
        }
    }

    /**
     * 指定された文字列が long name として妥当かどうかを調べます。
     * 文字列が複数の半角英数 (a-zA-Z0-9) からなる単語か、各単語をハイフン "-" で繋げた文字列のみ OK とします。
     *
     * @param string $longName 検査対象の文字列
     * @throws IllegalArgumentException 検査対象の文字列が long name として不適切だった場合
     */
    public static function validateLongName(string $longName): void
    {
        if (!preg_match("/\\A[a-zA-Z0-9]+(-[a-zA-Z0-9]+)*\\z/", $longName)) {
            throw new InvalidArgumentException("Invalid long name specified: ('{$longName}')");
        }
    }

    /**
     * この指示子の long name を返します。
     *
     * @return string この指示子の long name
     */
    public function getLongName(): string
    {
        return $this->longName;
    }

    /**
     * この指示子の short name を返します。
     * 存在しない場合は空文字列を返します。
     *
     * @return string この指示子の short name
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * 指定された Specifier オブジェクトとこのオブジェクトにおいて、
     * long name または short name が重複しているかどうかを調べます。
     *
     * @param Specifier $subject
     * @return bool 重複している場合は true
     */
    public function overlaps(Specifier $subject): bool
    {
        return
            ($this->longName === $subject->longName) ||
            (strlen($this->shortName) && ($this->shortName === $subject->shortName));
    }

    /**
     * このオブジェクトの文字列表現です。
     *
     * @return string "long name: 'XXX', short name: 'X'" 形式の文字列
     */
    public function __toString(): string
    {
        $result = "long name: '{$this->longName}'";
        if (strlen($this->shortName)) {
            $result .= ", short name: '{$this->shortName}'";
        }
        return $result;
    }
}
