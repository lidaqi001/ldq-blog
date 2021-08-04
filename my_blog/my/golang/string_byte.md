# Golang string类型和[]byte类型的对比

## **string 标准概念？**

Go标准库builtin给出了所有内置类型的定义。 

源代码位于 `src/builtin/builtin.go`，其中关于string的描述如下:
```
// string is the set of all strings of 8-bit bytes, conventionally but not
// necessarily representing UTF-8-encoded text. A string may be empty, but
// not nil. Values of string type are immutable.
type string string
```
所以string是8比特字节的集合，通常但并不一定是UTF-8编码的文本。

另外，还提到了两点，非常重要：
- string可以为空（长度为0），但不会是nil；
- string对象不可以修改。

## **string 数据结构**
go的源码中 `src/runtime/string.go:stringStruct` ，定义了string的数据结构：

```
type stringStruct struct {
    str unsafe.Pointer
    len int
}
```

- `str`: 是个指针，指向字符串的首地址
- `len`: 字符串的长度

## **string 操作**

如下代码所示，可以声明一个string变量变赋予初值：
```
var str string
str = "Hello World"
```
字符串构建过程是先跟据字符串构建stringStruct，再转换成string。转换的源码如下：
```
func gostringnocopy(str *byte) string { // 跟据字符串地址构建string
	ss := stringStruct{str: unsafe.Pointer(str), len: findnull(str)} // 先构造stringStruct
	s := *(*string)(unsafe.Pointer(&ss))                             // 再将stringStruct转换成string
	return s
}
```
而且要注意`string`其实就是个`struct`, 对外呈现叫做`string`。

---

## **byte 标准概念**
```
// byte is an alias for uint8 and is equivalent to uint8 in all ways. It is
// used, by convention, to distinguish byte values from 8-bit unsigned
// integer values.
type byte = uint8
```
简单来说, 在go里面, `byte`是`uint8`的别名
## **byte 数据结构**

而slice结构在go的源码中src/runtime/slice.go定义：

```
type slice struct {
	array unsafe.Pointer
	len   int
	cap   int
}
```
- `array`: 是数组的指针
- `len`: 表示长度
- `cap`: 表示容量


## **string []byte 对比**
在前面说到了字符串的值是不能改变的，这句话其实不完整，应该说字符串的值不能被更改，但可以被替换。

 还是以string的结构体来解释吧，所有的string在底层都是这样的一个结构体 `stringStruct{str: str_point, len: str_len}`
 
 string结构体的 `str指针` 指向的是一个字符常量的地址， 这个地址里面的内容是不可以被改变的，因为它是`只读`的，但是这个指针可以`指向不同的地址`，我们来对比一下string、[]byte类型重新赋值的区别：

```
s := "A1" // 分配存储"A1"的内存空间，s结构体里的str指针指向这快内存
s = "A2"  // 重新给"A2"的分配内存空间，s结构体里的str指针指向这快内存
```

其实[]byte和string的差别是更改变量的时候array的内容可以被更改。

```
s := []byte{1} // 分配存储1数组的内存空间，s结构体的array指针指向这个数组。
s = []byte{2}  // 将array的内容改为2
```

因为 `string` 的指针指向的内容是不可以更改的，所以`每更改一次字符串`，就得`重新分配一次内存`，`之前分配的空间`还得由`gc回收`，这是导致string`操作低效`的根本原因。

## **如何取舍？**

> 既然string就是一系列字节，而[]byte也可以表达一系列字节，那么实际运用中应当如何取舍？
> 
> 脱离实际场景谈性能都是耍流氓，需要根据实际场景来抉择。

string 擅长的场景：

- 需要字符串比较的场景；
    - string可以直接比较，而[]byte不可以，所以[]byte不可以当map的key值
- 不需要nil字符串的场景；
    - string值不可为nil，所以如果你想要通过返回nil表达额外的含义，就用[]byte

[]byte擅长的场景：

- 修改字符串的场景，尤其是修改粒度为1个字节；
- 返回值可以为nil，需要返回nil表达额外含义的场景；
- []byte切片这么灵活, 需要使用切片操作的场景；
- 需要大量字符串处理的时候用[]byte，性能好很多。

虽然看起来string适用的场景不如[]byte多，但因为string直观，在实际应用中还是大量存在，在偏底层的实现中[]byte使用更多。

---

# QA
- ## 为什么字符串不允许修改？

像C++语言中的string，其本身拥有内存空间，修改string是支持的。但Go的实现中，string不包含内存空间，只有一个内存的指针，这样做的好处是string变得非常轻量，可以很方便的进行传递而不用担心内存拷贝。

因为string通常指向字符串字面量，而字符串字面量存储位置是只读段，而不是堆或栈上，所以才有了string不可修改的约定。

- ## []byte转换成string一定会拷贝内存吗？

byte切片转换成string的场景很多，为了性能上的考虑，有时候只是临时需要字符串的场景下，byte切片转换成string时并不会拷贝内存，而是直接返回一个string，这个string的指针(string.str)指向切片的内存。

比如，编译器会识别如下临时场景：

- 使用m[string(b)]来查找map（map是string为key，临时把切片b转成string）；
- 字符串拼接，如"<" + "string(b)" + ">"；
- 字符串比较：string(b) == "foo"

因为是临时把byte切片转换成string，也就避免了因byte切片同容改成而导致string引用失败的情况，所以此时可以不必拷贝内存新建一个string。

---

# 参考链接:
- [golang string和[]byte的对比
](https://www.cnblogs.com/zhangboyu/p/7623712.html)
- [Go string 实现原理剖析（你真的了解string吗）](https://my.oschina.net/renhc/blog/3019849)