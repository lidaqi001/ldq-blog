# MYSQL

> ## 数据库三范式

- 一：确保每列的原子性（不可再分）
- 二：非主键列不存在对主键的部分依赖（要求每个表只描述一件事情）
- 三：满足第二范式，并且表中的列不存在对非主键列的传递依赖

> ## mysqld启动命令
- 常用命令解析
    - --basedir：MySQL安装目录
    - --datadir：MySQL数据存放目录
    - --plugin-dir：MySQL插件目录
    - --user：指定用户运行MySQL
    - --log-error：指定错误日志地址
    - --open-files-limit：mysqld进程能使用的最大文件描述(FD)符数量
    - --pid-file：指定mysqld进程pid存放地址
    - --socket：指定socket文件存放地址
    - --port：指定进程运行端口号
    ```
    mysqld --basedir=/usr/lib/mysql --datadir=/data/mysql/data --plugin-dir=/usr/lib/mysql/plugin --user=mysql --log-error=/data/mysql/data/mysql.err --open-files-limit=65535 --pid-file=/data/mysql/data/mysql.pid --socket=/tmp/mysqld.sock --port=3306
    ```

> ## 数据库主从复制原理

![流程图](/my_blog/images/ms.jpg)
- ①：主库的更新事件（update、insert、delete）被写到binlog
- ②：主库创建一个binlog dump thread线程，把binlog的内容发送到从库
- ③：从库创建一个I/O线程，读取主库传过来的binlog内容并写入到relay log
- ④：从库还会创建一个SQL线程，从relay log里面读取内容写入到从库

> ## 主从库复制方式分类

- 一、异步复制（默认）：主库写入binlog日志后即可成功返回客户端，无需等待binlog日志传递给从库的过程，但是一旦主库宕机，就有可能出现丢失数据的情况。
- 二：半同步复制（5.5版本之后）（安装半同步复制插件）：确保从库接收完成主库传递过来的binlog内容已经写入到自己的relay log（传送log）后才会通知主库上面的等待线程。如果等待超时，则关闭半同步复制，并自动转换为异步复制模式，知道至少有一台从库通知主库已经接收到binlog信息为止。

> ## 主从复制操作

### 1、备份主服务器原有数据到从服务器（务必主从服务器同步前数据要相同）

如果在设置主从同步前，主服务器上已有大量数据，可以使用mysqldump进行数据备份并还原到从服务器以实现数据的复制。

- 主服务器，执行命令：
    ```
    /**
    * -u ：用户名
    * -p ：示密码
    * --all-databases ：导出所有数据库
    * --lock-all-tables ：执行操作时锁住所有表，防止操作时有数据修改
    * ./master_db.sql :导出的备份数据（sql文件）位置，可自己指定
    */
    mysqldump -uroot -pmysql --all-databases --lock-all-tables > ./master_db.sql
    ```
- 从服务器，执行命令：
    ```
    mysql –uroot –pmysql < master_db.sql
    ```
### 2、配置主服务器log_bin和server-id
    
- 打开配置文件
    ```
        vim /etc/mysql/mysql.conf.d/mysqld.cnf
    ```

- 加入配置项
    ```
    ···
    [mysqld]
    server-id=1
    log_bin=/data/mysql/data/mysql-bin.log
    ```

- 重启mysql
    ```
    mysqld --basedir=/data/mysql --datadir=/data/mysql/data --plugin-dir=/usr/lib/mysql/plugin --user=mysql --log-error=/data/mysql/data/mysql.err --open-files-limit=65535 --pid-file=/data/mysql/data/mysql.pid --socket=/tmp/mysql.sock --port=3306
    ```
- 主库创建一个用户slave，用于从库同步主节点数据时使用
    ```
    mysql> CREATE USER 'slave'@'%' IDENTIFIED WITH mysql_native_password BY '密码';
    mysql> GRANT REPLICATION SLAVE ON *.* TO 'slave'@'%';
    mysql> flush privileges;
    ```

- 获取主节点当前binary log(bin_log)文件名和位置（position）
    ```
    mysql> show master status;
    +---------------+----------+--------------+------------------+-------------------+
    | File          | Position | Binlog_Do_DB | Binlog_Ignore_DB | Executed_Gtid_Set |
    +---------------+----------+--------------+------------------+-------------------+
    | binlog.000002 |     2435 |              |                  |                   |
    +---------------+----------+--------------+------------------+-------------------+
    1 row in set (0.00 sec)
    ```
### 3、配置从服务器server-id
- 从库配置对应主库参数
    ```
    mysql> change master to MASTER_HOST='172.20.0.10',MASTER_USER='slave',MASTER_PASSWORD='密码',MASTER_LOG_FILE='binlog.000002',MASTER_LOG_POS=2435;

    Query OK, 0 rows affected, 2 warnings (0.08 sec)
    ```
- 查看从库server-id 配置
    ```
    mysql> show variables like 'server_id';
    +---------------+-------+
    | Variable_name | Value |
    +---------------+-------+
    | server_id     | 1     |
    +---------------+-------+
    1 row in set (0.00 sec)
    ```
    此时看到server-id与my.cnf里面配置的值不一样

    这里我们把从库的server_id改成2(my.cnf中配置的值)
    ```
    mysql> set global server_id=2; #此处的数值和my.cnf里设置的一样就行 
    mysql> start slave;
    mysql> show slave status\G;
    *************************** 1. row ***************************
               Slave_IO_State: Waiting for master to send event
                  Master_Host: 172.20.0.10
                  Master_User: slave
                  Master_Port: 3306
                  ···
             Slave_IO_Running: Yes
            Slave_SQL_Running: Yes
                  ···
    ```
    开启主从之后，如果状态如上，就配置好了，接下来测试一下在主库上创建一个数据库，然后看有没有同步到从库上。
    

> ## 存储引擎

- 一：Myisam是MySQL默认的存储引擎，不支持事务，行级锁，外键；插入更新需要锁表，效率低，查询速度快，Myisam使用的是非聚集索引
- 二：Innodb支持事务，底层为B+树实现，适合处理多重并发更新操作，普通select都是快照读，快照读不加锁。InnoDB使用的是聚集索引。

> ## 聚集索引

