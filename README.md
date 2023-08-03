# Cloth : Command-Line-Options Translator and Helper

Cloth とは、入力されたコマンドライン引数を解析するためのライブラリです。コマンドライン向け PHP アプリケーション開発における利用を想定しています。

同じ用途のツールとして、例えばビルトイン関数の [getopt](https://www.php.net/manual/ja/function.getopt.php) などが挙げられますが、
これに対して下記のような差別化ポイントがあります。

* 完全なオブジェクト指向インタフェース:
    * 可読性・保守性に優れたコードを記述することができます
* ショートオプションとロングオプションの同一視:
    * 例えば `-h` と `--help` のように同じ用途のオプションを同一として扱います
* テスタビリティの向上:
    * プログラムの参照透過性を担保することができます。
    関数 `getopt()` がグローバル変数 `$argv` を直接参照しているのに対して、このライブラリは任意の文字列配列を入力値とします

## チュートリアル

簡単なサンプルコード (sample.php) を以下に記載します。このコードはコマンドラインで実行されることを想定しています。

```php
<?php

use Cloth\Schema;

require_once("vendor/autoload.php");

$opts = (new Schema())
    ->flag("help")
    ->flag("verbose", "v")
    ->flag("recursive", "R")
    ->flag("debug")
    ->param("with-config-path")
    ->param("input", "i")
    ->param("output", "o")
    ->parse($argv);

var_dump($opts->getOptionsAsArray());
var_dump($opts->getArgs());
```

この PHP ファイルを以下の引数で実行した場合、出力はこのようになります。
コマンドラインオプション一覧を配列として出力した際に、指定されていないオプションには `false` または `null` がセットされ、
指定されたものには `true` または文字列がセットされていることがわかります。

```shell
$ php sample.php -v -i source.txt -o dest.txt xxxx yyyy

array(7) {
  ["debug"]=>
  bool(false)
  ["help"]=>
  bool(false)
  ["recursive"]=>
  bool(false)
  ["verbose"]=>
  bool(true)
  ["input"]=>
  string(10) "source.txt"
  ["output"]=>
  string(8) "dest.txt"
  ["with-config-path"]=>
  NULL
}
array(3) {
  [0]=>
  string(10) "sample.php"
  [1]=>
  string(4) "xxxx"
  [2]=>
  string(4) "yyyy"
}
```

### 主要クラスの仕様

ユーザーが直接使用するクラスは `Schema` および `OptionSet` の 2 種類です。主要なメソッド一覧を以下に記載します。

* Schema
    * `flag(string $longName, string $shortName = "")`
        * フラグ形式のオプションを定義します。このオプションは ON, OFF の 2 通りの状態を持ちます。
    * `param(string $longName, string $shortName = "")`
        * パラメータ形式のオプションを定義します。このオプションは任意の文字列を値として持ちます
    * `parse(string[] $args)`
        * 引数の文字列配列をコマンドライン引数として構文解析を行い、結果を `OptionSet` インスタンスとして返します
* OptionSet
    * `getOptionsAsArray()`
        * 指定されたコマンドラインオプションを配列として返します。各配列のキーはオプションの long name, 値はそのオプションの設定値をあらわします
    * `getArgs()`
        * コマンドラインオプション以外の引数一覧を配列として返します
    * `getOptionByLongName(string $longName)`
        * 引数に指定された long name を持つコマンドラインオプションの値を `Option` インスタンスとして返します
    * `getOptionByShortName(string $shortName)`
        * 引数に指定された short name を持つコマンドラインオプションの値を `Option` インスタンスとして返します
* Option
    * `getValue()`
        * このオプションの値を取得します。指定されていないオプションの場合は `false` (フラグ形式の場合) または `null` (パラメータ形式の場合) を返します

以下補足です。

* 各オプションは long name および short name という 2 種類の識別子を持ちます。各識別子は一意である必要があります
* `flag()` および `param()` の引数について、`$longName` は必須で `$shortName` は任意指定です。
つまり、すべてのコマンドラインオプションは必ず何かしらの long name を持つ必要があります
* 不正な引数が指定されて `parse()` が失敗した場合は `ParseException` をスローします
* `flag()` および `param()` はこのオブジェクト自身を返り値として返します。そのため一連の処理をメソッドチェーンで記述することができます
* `getOptionByLongName()` および `getOptionByShortName()` は、存在しないオプションを引数に指定された場合に
`InvalidArgumentException` をスローします

## 詳細仕様

### 識別子のフォーマット

各オプションは long name および short name を識別子として持ちます。各識別子は下記の書式を満たす必要があります。
(大文字・小文字を区別します)

* long name
    * 1 文字以上の `a-z`, `A-Z`, `0-9` から成る単語か、各単語をハイフン `-` で連結したもの
* short name
    * `a-z`, `A-Z`, `0-9` のいずれか 1 文字

以下は long name として妥当な文字列です。

* `test01`
* `some-wonderful-words`

以下は long name として使用できない例です。

* `abc%def@` (使用できない文字 `%`, `@` を含んでいる)
* `-abc-def` (ハイフンは識別子の先頭・末尾に使用することができない)
* `abc--def` (ハイフンは 2 文字以上連続してはならない)

### コマンドラインオプションの書式

このライブラリは、多くの GNU 拡張ソフトウェアでサポートされているオプションの書式に対応しています。

* short name と long name の指定方法
    * short name を指定する場合は 1 文字のハイフンに続けて記述 (例: `-a`),
    long name を指定する場合は 2 文字のハイフンに続けて記述します (例: `--with-config-path`)
* 複数のフラグオプションの指定
    * 例えば `-axF` のように 1 文字のハイフンに繋げて複数の short name を指定することで、複数のフラグオプションを一括指定することができます
* パラメータオプションの指定方法
    * パラメータの値を指定する場合、オプションの指定の後ろに値を指定します (例: `-i source.txt`, `--input source.txt`)
    * long name で指定する場合、イコール `=` に続けて値を指定することもできます (例: `--input=soruce.txt`)
* オプションの区切り
    * ハイフン 2 つ `--` を指定すると、後に続く引数すべてをオプションではなくコマンドの引数として取り扱います。
    例えば `-a --test01 -- -c --test02` という引数では `-a`, `--test01` をオプションとして、`-c`, `--test02` を引数として処理します

### このライブラリの制限

* short name のみを持つオプションを定義することはできません
* 各オプションに対して必須・任意の設定を付与することはできません。必須オプションが指定されているかどうかはプログラム側で判定する必要があります

## インストール

Composer からインストールできます。

動作要件: PHP 7.0 以上