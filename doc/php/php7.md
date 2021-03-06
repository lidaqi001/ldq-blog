

### 访问权限修饰符 

- public 公开的。任何地方都能访问
- protected 保护的、只能在本类和子类中访问
- private 私有的。只能在本类调用
- final 最终的。被修饰的方法或者类，不能被继承或者重写
- static 静态

### 接口和抽象类区别

- 接口使用interface声明，抽象类使用abstract
- 抽象类可以包含属性方法。接口不能包含成员属性、
- 接口不能包含非抽象方法


### CSRF XSS

CSRF，跨站请求伪造，攻击方伪装用户身份发送请求从而窃取信息或者破坏系统。

讲述基本原理：用户访问A网站登陆并生成了cookie，再访问B网站，如果A网站存在CSRF漏洞，此时B网站给A网站的请求（此时相当于是用户访问），A网站会认为是用户发的请求，从而B网站就成功伪装了你的身份，因此叫跨站脚本攻击。

CSRF防范：

1.合理规范api请求方式，GET，POST

2.对POST请求加token令牌验证，生成一个随机码并存入session，表单中带上这个随机码，提交的时候服务端进行验证随机码是否相同。

XSS，跨站脚本攻击。

防范：不相信任何输入，过滤输入。

### CGI、FastCGI、FPM

CGI全称是“公共网关接口”(Common Gateway Interface)，HTTP服务器与你的或其它机器上的程序进行“交谈”的一种工具，其程序须运行在网络服务器上。 

CGI是HTTP Server和一个独立的进程之间的协议，把**HTTP Request的Header设置成进程的环境变量**，HTTP Request的正文设置成进程的标准输入，而进程的标准输出就是HTTP Response包括Header和正文。  

FastCGI像是一个常驻(long-live)型的CGI，它可以一直执行着，只要激活后，不会每次都要花费时间去fork一次（这是CGI最为人诟病的fork-and-execute 模式）。它还支持分布式的运算，即 FastCGI 程序可以在网站服务器以外的主机上执行并且接受来自其它网站服务器来的请求。 

Fpm是一个实现了Fastcgi协议的程序,用来管理Fastcgi起的进程的,即能够调度php-cgi进程的程序 

#### FastCGI特点

1. FastCGI具有语言无关性.
2. FastCGI在进程中的应用程序，独立于核心web服务器运行，提供了一个比API更安全的环境。APIs把应用程序的代码与核心的web服务器链接在一起，这意味着在一个错误的API的应用程序可能会损坏其他应用程序或核心服务器。 恶意的API的应用程序代码甚至可以窃取另一个应用程序或核心服务器的密钥。
3. FastCGI技术目前支持语言有：C/C++、Java、Perl、Tcl、Python、SmallTalk、Ruby等。相关模块在Apache, ISS, Lighttpd等流行的服务器上也是可用的。
4. FastCGI的不依赖于任何Web服务器的内部架构，因此即使服务器技术的变化, FastCGI依然稳定不变。

#### FastCGI的工作原理

1. Web Server启动时载入FastCGI进程管理器（IIS ISAPI或Apache Module)
2. FastCGI进程管理器自身初始化，启动多个CGI解释器进程(可见多个php-cgi)并等待来自Web Server的连接。
3. 当客户端请求到达Web Server时，FastCGI进程管理器选择并连接到一个CGI解释器。Web server将CGI环境变量和标准输入发送到FastCGI子进程php-cgi。
4. FastCGI子进程完成处理后将标准输出和错误信息从同一连接返回Web Server。当FastCGI子进程关闭连接时，请求便告处理完成。FastCGI子进程接着等待并处理来自FastCGI进程管理器(运行在Web Server中)的下一个连接。 在CGI模式中，php-cgi在此便退出了。


PHP7 和 PHP5 的区别
- 性能提升了两倍 
    execute_data、opline直接从寄存器读取地址，在性能上大概有5%的提升
- 增加了结合比较运算符 (<=>)
- 增加了标量类型声明、返回类型声明
- try...catch 增加多条件判断，更多 Error 错误可以进行异常处理
- 增加了匿名类，现在支持通过new class 来实例化一个匿名类，这可以用来替代一些“用后即焚”的完整类定义


php7发布已经升级到7.2.里面发生了很多的变化。本文整理php7.0至php7.2的新特性和一些变化。

参考资料：

http://php.net/manual/zh/migration70.new-features.php

http://php.net/manual/zh/migration71.new-features.php

http://php.net/manual/zh/migration72.new-features.php

https://github.com/pangudashu/php7-internal( PHP7内核剖析 )

## PHP7.0

### PHP7.0新特性

#### 1. 组合比较符 (<=>)

组合比较符号用于比较两个表达式。当$a小于、等于或大于$b时它分别返回-1、0或1，比较规则延续常规比较规则。对象不能进行比较

```php
var_dump('PHP' <=> 'Node'); // int(1)
var_dump(123 <=> 456); // int(-1)
var_dump(['a', 'b'] <=> ['a', 'b']); // int(0)
```

#### 2. null合并运算符

由于日常使用中存在大量同时使用三元表达式和isset操作。使用null合并运算符可以简化操作

```php
# php7以前
if(isset($_GET['a'])) {
  $a = $_GET['a'];
}
# php7以前
$a = isset($_GET['a']) ? $_GET['a'] : 'none';

#PHP 7
$a = isset($_GET['a']) ?? 'none';

```

#### 4. 变量类型声明

变量类型声明有两种模式。一种是强制的，和严格的。允许使用下列类型参数**int**、**string**、**float**、**bool**

同时不能再使用int、string、float、bool作为类的名字了

```php
function sumOfInts(int ...$ints)
{
    return array_sum($ints);
}
ar_dump(sumOfInts(2, '3', 4.1)); // int(9)
# 严格模式
declare(strict_types=1);

function add(int $x, int $y)
{
    return $x + $y;
}
var_dump(add('2', 3)); // Fatal error: Argument 1 passed to add() must be of the type integer
```

#### 5. 返回值类型声明

增加了返回类型声明，类似参数类型。这样更方便的控制函数的返回值.在函数定义的后面加上:类型名即可

```php
function fun(int $a): array
{
  return $a;
}
fun(3);//Fatal error
```

#### 6. 匿名类

php7允许new class {} 创建一个匿名的对象。

```php
//php7以前
class Logger
{
    public function log($msg)
    {
        echo $msg;
    }
}

$util->setLogger(new Logger());

// php7+
$util->setLogger(new class {
    public function log($msg)
    {
        echo $msg;
    }
});
```

#### 7. Unicode codepoint 转译语法

这接受一个以16进制形式的 Unicode codepoint，并打印出一个双引号或heredoc包围的 UTF-8 编码格式的字符串。 可以接受任何有效的 codepoint，并且开头的 0 是可以省略的

```PHP
echo "\u{aa}";// ª
echo "\u{0000aa}";// ª
echo "\u{9999}";// 香
```

#### 8. Closure::call

闭包绑定 简短干练的暂时绑定一个方法到对象上闭包并调用它。

```php
class A {private $x = 1;}

// PHP 7 之前版本的代码
$getXCB = function() {return $this->x;};
$getX = $getXCB->bindTo(new A, 'A'); // 中间层闭包
echo $getX();

// PHP 7+ 及更高版本的代码
$getX = function() {return $this->x;};
echo $getX->call(new A);
```

#### 9. 带过滤的unserialize

提供更安全的方式解包不可靠的数据。它通过白名单的方式来防止潜在的代码注入

```php
// 将所有的对象都转换为 __PHP_Incomplete_Class 对象
$data = unserialize($foo, ["allowed_classes" => false]);

// 将除 MyClass 和 MyClass2 之外的所有对象都转换为 __PHP_Incomplete_Class 对象
$data = unserialize($foo, ["allowed_classes" => ["MyClass", "MyClass2"]);

// 默认情况下所有的类都是可接受的，等同于省略第二个参数
$data = unserialize($foo, ["allowed_classes" => true]);
```

#### 10. IntlChar类

这个类自身定义了许多静态方法用于操作多字符集的 unicode 字符。需要安装intl拓展

```php

printf('%x', IntlChar::CODEPOINT_MAX);
echo IntlChar::charName('@');
var_dump(IntlChar::ispunct('!'));
```

#### 11. 预期

它使得在生产环境中启用断言为零成本，并且提供当断言失败时抛出特定异常的能力。以后可以使用这个这个进行断言测试

```php
ini_set('assert.exception', 1);

class CustomError extends AssertionError {}

assert(false, new CustomError('Some error message'));
```

#### 12. 命名空间按组导入

从同一个命名空间下导入的类、函数、常量支持按组一次导入

```php
#php7以前
use app\model\A;
use app\model\B;
#php7+
use app\model{A,B}
```

#### 13.生成器支持返回表达式

 它允许在生成器函数中通过使用 *return* 语法来返回一个表达式 （但是不允许返回引用值）， 可以通过调用 *Generator::getReturn()* 方法来获取生成器的返回值， 但是这个方法只能在生成器完成产生工作以后调用一次。

```php
$gen = (function() {
    yield 1;
    yield 2;

    return 3;
})();

foreach ($gen as $val) {
    echo $val, PHP_EOL;
}

echo $gen->getReturn(), PHP_EOL;
# output
//1
//2
//3
```

#### 14.生成器委派

现在，只需在最外层生成其中使用yield from，就可以把一个生成器自动委派给其他的生成器

```php
function gen()
{
    yield 1;
    yield 2;

    yield from gen2();
}

function gen2()
{
    yield 3;
    yield 4;
}

foreach (gen() as $val)
{
    echo $val, PHP_EOL;
}
```

#### 15.整数除法函数intdiv

```php
var_dump(intdiv(10,3)) //3
```

#### 16.会话选项设置

session_start() 可以加入一个数组覆盖php.ini的配置

```php
session_start([
    'cache_limiter' => 'private',
    'read_and_close' => true,
]);
```

#### 17. preg_replace_callback_array

可以使用一个关联数组来对每个正则表达式注册回调函数， 正则表达式本身作为关联数组的键， 而对应的回调函数就是关联数组的值

```php
string preg_replace_callback_array(array $regexesAndCallbacks, string $input);
$tokenStream = []; // [tokenName, lexeme] pairs

$input = <<<'end'
$a = 3; // variable initialisation
end;

// Pre PHP 7 code
preg_replace_callback(
    [
        '~\$[a-z_][a-z\d_]*~i',
        '~=~',
        '~[\d]+~',
        '~;~',
        '~//.*~'
    ],
    function ($match) use (&$tokenStream) {
        if (strpos($match[0], '$') === 0) {
            $tokenStream[] = ['T_VARIABLE', $match[0]];
        } elseif (strpos($match[0], '=') === 0) {
            $tokenStream[] = ['T_ASSIGN', $match[0]];
        } elseif (ctype_digit($match[0])) {
            $tokenStream[] = ['T_NUM', $match[0]];
        } elseif (strpos($match[0], ';') === 0) {
            $tokenStream[] = ['T_TERMINATE_STMT', $match[0]];
        } elseif (strpos($match[0], '//') === 0) {
            $tokenStream[] = ['T_COMMENT', $match[0]];
        }
    },
    $input
);

// PHP 7+ code
preg_replace_callback_array(
    [
        '~\$[a-z_][a-z\d_]*~i' => function ($match) use (&$tokenStream) {
            $tokenStream[] = ['T_VARIABLE', $match[0]];
        },
        '~=~' => function ($match) use (&$tokenStream) {
            $tokenStream[] = ['T_ASSIGN', $match[0]];
        },
        '~[\d]+~' => function ($match) use (&$tokenStream) {
            $tokenStream[] = ['T_NUM', $match[0]];
        },
        '~;~' => function ($match) use (&$tokenStream) {
            $tokenStream[] = ['T_TERMINATE_STMT', $match[0]];
        },
        '~//.*~' => function ($match) use (&$tokenStream) {
            $tokenStream[] = ['T_COMMENT', $match[0]];
        }
    ],
    $input
);
```

#### 18. 随机数、随机字符函数

```php
string random_bytes(int length);
int random_int(int min, int max);
```

#### 19. define 支持定义数组

```php
#php7+
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'gif', 'png']);
```

### PHP7.0 变化

#### 1. 错误和异常处理相关变更

PHP 7 改变了大多数错误的报告方式。不同于传统（PHP 5）的错误报告机制，现在大多数错误被作为 **Error** 异常抛出。

这也意味着，当发生错误的时候，以前代码中的一些错误处理的代码将无法被触发。 因为在 PHP 7 版本中，已经使用抛出异常的错误处理机制了。 （如果代码中没有捕获 **Error** 异常，那么会引发致命错误）。set_error_handle不一定接收的是异常，有可能是错误。

ERROR层级结构

```
interface Throwable
    |- Exception implements Throwable
        |- ...
    |- Error implements Throwable
        |- TypeError extends Error
        |- ParseError extends Error
        |- AssertionError extends Error
        |- ArithmeticError extends Error
            |- DivisionByZeroError extends ArithmeticError
```

```php
function handler(Exception $e) { ... }
set_exception_handler('handler');

// 兼容 PHP 5 和 7
function handler($e) { ... }

// 仅支持 PHP 7
function handler(Throwable $e) { ... }
```

#### 2. list

list 会按照原来的顺序进行赋值。不再是逆序了

```php
list($a,$b,$c) = [1,2,3];
var_dump($a);//1
var_dump($b);//2
var_dump($c);//3
```

list不再支持解开字符串、

#### 3. foreach不再改变内部数组指针

```php
<?php
$array = [0, 1, 2];
foreach ($array as &$val) {
    var_dump(current($array));
}
?>
#php 5
int(1)
int(2)
bool(false)
#php7
int(0)
int(0)
int(0)
```

#### 4. 十六进制字符串不再被认为是数字

```php
var_dump("0x123" == "291");
#php5
true
#php7
false
  
```

#### 5.$HTTP_RAW_POST_DATA 被移 

$HTTP_RAW_POST_DATA 被移 使用`php://input`代替

#### 6. 移除了 ASP 和 script PHP 标签

| 开标签                       | 闭标签         |
| ------------------------- | ----------- |
| `<%`                      | `%>`        |
| `<%=`                     | `%>`        |
| `<script language="php">` | `</script>` |

## PHP7.1

### PHP7.1新特性

#### 1. 可为空（Nullable）类型

参数以及返回值的类型现在可以通过在类型前加上一个问号使之允许为空。当启用这个特性时，传入的参数或者函数返回的结果要么是给定的类型，要么是null

```php
#php5
function($a = null){
  if($a===null) {
    return null;
  }
  return $a;
}
#php7+
function fun() :?string
{
  return null;
}

function fun1(?$a)
{
  var_dump($a);
}
fun1(null);//null
fun1('1');//1

```

#### 2. void 类型

返回值声明为 void 类型的方法要么干脆省去 return 语句。对于 void来说，**NULL** 不是一个合法的返回值。

```php
function fun() :void
{
  echo "hello world";
}
```

#### 3. 类常量可见性

```php
class Something
{
    const PUBLIC_CONST_A = 1;
    public const PUBLIC_CONST_B = 2;
    protected const PROTECTED_CONST = 3;
    private const PRIVATE_CONST = 4;
}
```

#### 4. iterable 伪类

这可以被用在参数或者返回值类型中，它代表接受数组或者实现了**Traversable**接口的对象.

```php
function iterator(iterable $iter)
{
    foreach ($iter as $val) {
        //
    }
}
```

#### 5. 多异常捕获处理

一个catch语句块现在可以通过管道字符(*|*)来实现多个异常的捕获。 这对于需要同时处理来自不同类的不同异常时很有用

```php
try {
    // some code
} catch (FirstException | SecondException $e) {
    // handle first and second exceptions
}
```

#### 6. list支持键名

```php
$data = [
    ["id" => 1, "name" => 'Tom'],
    ["id" => 2, "name" => 'Fred'],
];

// list() style
list("id" => $id1, "name" => $name1) = $data[0];
var_dump($id1);//1
```

#### 7. 字符串支持负向

```php
$a= "hello";
$a[-2];//l
```

#### 8. 将callback 转闭包

Closure新增了一个静态方法，用于将callable快速地 转为一个Closure 对象。

```php
<?php
class Test
{
    public function exposeFunction()
    {
        return Closure::fromCallable([$this, 'privateFunction']);
    }

    private function privateFunction($param)
    {
        var_dump($param);
    }
}

$privFunc = (new Test)->exposeFunction();
$privFunc('some value');
```

#### 9. http2 服务推送

对http2服务器推送的支持现在已经被加入到 CURL 扩展

### PHP7.1变更

#### 1. 传递参数过少时将抛出错误

过去我们传递参数过少 会产生warning。php7.1开始会抛出error

#### 2. 移除了ext/mcrypt拓展



## PHP7.2

### PHP7.2新特性

#### 1. 增加新的类型object

```php
function test(object $obj) : object
{
    return new SplQueue();
}

test(new StdClass());
```

#### 2. 通过名称加载扩展

扩展文件不再需要通过文件加载 (Unix下以*.so*为文件扩展名，在Windows下以 *.dll* 为文件扩展名) 进行指定。可以在php.ini配置文件进行启用

```ini
; ini file
extension=php-ast
zend_extension=opcache
```

#### 3.允许重写抽象方法

当一个抽象类继承于另外一个抽象类的时候，继承后的抽象类可以重写被继承的抽象类的抽象方法。

```php
<?php

abstract class A
{
    abstract function test(string $s);
}
abstract class B extends A
{
    // overridden - still maintaining contravariance for parameters and covariance for return
    abstract function test($s) : int;
}
```

#### 4. 使用Argon2算法生成密码散列

Argon2 已经被加入到密码散列（password hashing） API (这些函数以 *password_* 开头), 以下是暴露出来的常量

#### 5. 新增 PDO 字符串扩展类型

当你准备支持多语言字符集，PDO的字符串类型已经扩展支持国际化的字符集。以下是扩展的常量：

- **PDO::PARAM_STR_NATL**
- **PDO::PARAM_STR_CHAR**
- **PDO::ATTR_DEFAULT_STR_PARAM**

```php
$db->quote('über', PDO::PARAM_STR | PDO::PARAM_STR_NATL);
```

#### 6. 命名分组命名空间支持尾部逗号

```php
use Foo\Bar\{
    Foo,
    Bar,
    Baz,
};
```

### PHP7.2 变更

#### 1. number_format 返回值

```php
var_dump(number_format(-0.01)); // now outputs string(1) "0" instead of string(2) "-0"
```

#### 2. get_class()不再允许null。

```php
var_dump(get_class(null))// warning
```

#### 4. count 作用在不是 Countable Types 将发生warning

```php
count(1), // integers are not countable
```

#### 5. 不带引号的字符串

在之前不带引号的字符串是不存在的全局常量，转化成他们自身的字符串。现在将会产生waring。

```php
var_dump(HEELLO);
```

#### 6. __autoload 被废弃

__autoload方法已被废弃

#### 7. each 被废弃

使用此函数遍历时，比普通的 *foreach* 更慢， 并且给新语法的变化带来实现问题。因此它被废弃了。

#### 8. is_object、gettype修正

is_object 作用在**__PHP_Incomplete_Class** 将反正 true

gettype作用在闭包在将正确返回resource

#### 9. Convert Numeric Keys in Object/Array Casts

把数组转对象的时候，可以访问到整型键的值。

```php
// array to object
$arr = [0 => 1];
$obj = (object)$arr;
var_dump(
    $obj,
    $obj->{'0'}, // now accessible
    $obj->{0} // now accessible
);
```

