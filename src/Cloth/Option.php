<?php

namespace Cloth;

/**
 * 各種コマンドラインオプションの共通インタフェースです。
 */
interface Option
{
    /**
     * このコマンドラインオプションの指示子を返します。
     *
     * @return Specifier
     */
    public function getSpecifier(): Specifier;

    /**
     * このコマンドラインオプションの値を返します。
     * 返り値の型はオプションの種類によって異なります。
     *
     * return mixed
     */
    public function getValue();
}
