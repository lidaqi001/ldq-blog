# Mysql优化

> 2020.03.26 11:18


> ### 查询优化
- 不要select*（用什么查什么）

- 不要在多表关联查询时返回全部的列

- 不要重复查询，应当写入缓存

- 尽量使用关联查询来替代子查询

- 尽量使用索引优化。如果不使用索引，mysql则会使用临时表或者文件排序。

-  优化分页查询，最简单的就是利用*覆盖索引*扫描。而不是查询所有的列
```
覆盖索引：即所查询的列都是索引的列，不需要回表
```

- 应避免全表扫描的查询，首先应该考虑在 where 及 order by 设计的列上建立索引。

- 尽量不要使用前缀%
```
select * from user where name like '%a'
```

> ##### 以下情况会造成引擎放弃使用索引，而进行全表扫描

1、在 where 子句中使用 != 或 <> 操作符

2、在 where 子句中对字段进行 null 值判断

3、在 where 子句中对字段进行表达式操作

4、在 where 子句中对字段进行函数操作
```
select * from user where name is null
```

> ### 索引优化

1、使用独立的列，而不是计算的列
```
where num+1 =10 //bad
where num = 9 //good
```
2、使用前缀索引
3、多列索引，应该保证左序优先
4、覆盖索引
5、选择合适的索引顺序

> ##### 不能使用索引的情况
1、查询使用了两种排序方向
```
select * from user where login_time > '2018-01-01' order by id des ,username asc #
```
2、order by中含有了一个没有 索引的列
```
select * from user where name = '11' order by age desc; //age 没有索引
```
3、where 和 order by 无法形成最左前缀（*对于联合索引*）
