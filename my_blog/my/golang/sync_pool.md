# Golang 深入浅出 sync.Pool

# 简介
> 第一眼看到 Pool 这个名字，就让人想到池子，元素池化是常用的性能优化的手段（性能优化的几把斧头：并发，预处理，缓存）。比如，创建一个 100 个元素的池，然后就可以在池子里面直接获取到元素，免去了申请和初始化的流程，大大提高了性能。释放元素也是直接丢回池子而免去了真正释放元素带来的开销。

> sync.Pool 除了最常见的池化提升性能的思路，最重要的是减少 GC 。常用于一些对象实例创建昂贵的场景。注意，Pool 是 Goroutine 并发安全的。

## 如何使用？

> ### ***初始化 Pool 实例 New***
>> 第一个步骤就是创建一个 Pool 实例，关键一点是配置 New 方法，声明 Pool 元素创建的方法。
```go
bufferpool := &sync.Pool {
	New: func() interface {} {
		println("Create new instance")
		return struct{}{}
	}
}
```

> ### 申请对象 Get

```go
buffer := bufferPool.Get()
```
Get 方法会返回 Pool 已经存在的对象，如果没有，那么就走慢路径，也就是调用初始化的时候定义的 New 方法（也就是最开始定义的初始化行为）来初始化一个对象。



> ### 释放对象 Put
```go
bufferPool.Put(buffer)
```
使用对象之后，调用 `Put` 方法声明把对象放回池子。注意了，这个调用之后仅仅是把这个对象放回池子，池子里面的对象啥时候真正释放外界是不清楚的，是不受外部控制的。

`Pool` 的用户使用界面就这三个接口，非常简单，而且是通用型的 Pool 池模式，针对所有的对象类型都可以用。

## 完整用例

```go
package main

import (
	"fmt"
	"sync"
	"sync/atomic"
)

// 用来统计实例真正创建的次数
var numCalcsCreated int32

// 创建实例的函数
func createBuffer() interface{} {
	// 这里要注意下，非常重要的一点。这里必须使用原子加，不然有并发问题；
	atomic.AddInt32(&numCalcsCreated, 1)
	buffer := make([]byte, 1024)
	return &buffer
}

func main() {
	// 创建实例
	bufferPool := &sync.Pool{
		New: createBuffer,
	}

	// 多 goroutine 并发测试
	numWorkers := 1024 * 1024
	var wg sync.WaitGroup
	wg.Add(numWorkers)

	for i := 0; i < numWorkers; i++ {
		go func() {
			defer wg.Done()
			// 申请一个 buffer 实例
			buffer := bufferPool.Get()
			_ = buffer.(*[]byte)
			// 释放一个 buffer 实例
			defer bufferPool.Put(buffer)
		}()
	}
	wg.Wait()
	fmt.Printf("%d buffer objects were created.\n", numCalcsCreated)
}
```

## sync.Pool 是并发安全的吗？

> sync.Pool 当然是并发安全的。官方文档里明确说了：

```text
A Pool is safe for use by multiple goroutines simultaneously.
```

> 但是，为什么我这里会单独提出来呢？
>
>因为 sync.Pool 只是本身的 Pool 数据结构是并发安全的，并不是说 Pool.New 函数一定是线程安全的。Pool.New 函数可能会被并发调用 ，如果 New 函数里面的实现是非并发安全的，那就会有问题。

## 为什么 sync.Pool 不适合用于像 socket 长连接或数据库连接池?

> 因为，我们不能对 sync.Pool 中保存的元素做任何假设，以下事情是都可以发生的：

- 1、 Pool 池里的元素随时可能释放掉，释放策略完全由 runtime 内部管理；
- 2、 Get 获取到的元素对象可能是刚创建的，也可能是之前创建好 cache 住的。使用者无法区分；
- 3、 Pool 池里面的元素个数你无法知道；

> 所以，只有的你的场景满足以上的假定，才能正确的使用 Pool 。sync.Pool 本质用途是增加临时对象的重用率，减少 GC 负担。划重点：临时对象。所以说，像 socket 这种带状态的，长期有效的资源是不适合 Pool 的。

## 总结

- 1、 sync.Pool 本质用途是增加临时对象的重用率，减少 GC 负担；
- 2、 不能对 Pool.Get 出来的对象做预判，有可能是新的（新分配的），有可能是旧的（之前人用过，然后 Put 进去的）；
- 3、 不能对 Pool 池里的元素个数做假定，你不能够；
- 4、 sync.Pool 本身的 Get, Put 调用是并发安全的，sync.New 指向的初始化函数会并发调用，里面安不安全只有自己知道；
- 5、 当用完一个从 Pool 取出的实例时候，一定要记得调用 Put，否则 Pool 无法复用这个实例，通常这个用 defer 完成；