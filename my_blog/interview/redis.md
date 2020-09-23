# Redis

> ## 主从配置

- 1、基础操作

    ```
    // 1、安装redis
    docker pull redis
    
    // 2、创建网络
    docker network create redis

    // 3、复制一份redis.conf文件到服务器
    ```
- 2、修改redis.conf

    - 主库
    - /root/rs/redis.conf
    ```
    #允许远程连接
    bind 0.0.0.0    

    #设置密码123456
    requirepass 123456   

    #保护模式关闭
    protected-mode no   

    #守护线程关闭
    daemonize no  

    # 持久化
    appendonly yes  
    ```

    - 从库（在主库的修改基础上添加以下配置）
    - /root/rs/redis2.conf
    ```
    # 当前为 slave 则配置
    # 设定主库的密码，用于认证，如果主库开启了requirepass选项这里就填相应的密码
    masterauth 123456

    # 当前为 slave 则配置
    # 设定master的IP和端口
    # 低版本的redis这里会是slaveof
    replicaof 172.21.0.2  6379
    ```

- 3、创建redis容器

    - 主
    ```shell
    docker run -d -p 6379:6379 --net redis --ip 172.21.0.2 -v /root/rs/redis.conf:/usr/local/etc/redis/redis.conf -v /root/rs/data:/data --name rs1 redis redis-server /usr/local/etc/redis/redis.conf
    ```

    - 从
    ```shell
    docker run -d -p 6378:6379 --net redis --ip 172.21.0.3 -v /root/rs/redis2.conf:/usr/local/etc/redis/redis.conf -v /root/rs/data2:/data --name rs2 redis redis-server /usr/local/etc/redis/redis.conf
    ```


- 4、验证

    ```
    // 进入容器
    $ docker exec -it rs1 bash

    // 查看主节点 connected_slaves 已连接的从节点
    root@003c5de4be26:/data# redis-cli -a 123456
    127.0.0.1:6379> info Replication
    # Replication
    role:master
    connected_slaves:1
    slave0:ip=172.21.0.3,port=6379,state=online,offset=28,lag=0
    ···

    // 像上面这样显示就代表连接上了
    // 向master写入数据查看slave是否同步数据
    ```

> ## 主从是如何同步数据的

```
1、启动一个slave节点
2、slave节点发送一个psync命令给master节点

如果slave节点第一次连接到master节点，会触发一个全量复制，
master就会启动一个线程，生成RDB快照，还会把新的写请求都缓存在内存中，
RDB文件生成后，master会将这个RDB发送给slave，
slave节点拿到RDB文件后险些金本地磁盘，然后加载进内存，
然后master接着把之前缓存的写请求，都发送给slave，
后面就是master每次将写请求发送给slave。
```
```
注意：
    RDB快照的数据生成的时候，缓存区也同时开始接受新请求
```

> ## 哨兵配置

- ### 注意点：
    - master节点挂掉，Sentinel（哨兵）会从slave节点选举一个来作为新的master节点
    - 挂掉的原master节点又重新上线，这时会将原master节点作为slave节点
- 1、编辑sentinel.conf

    ```
    # sentinel的固定配置格式sentinel <option_name> <master_name> <option_value>

    # 配置sentinel监控的master
    # sentinel监控的master的名字叫做mymaster，地址为127.0.0.1:6380
    # sentinel在集群式时，需要多个sentinel互相沟通来确认某个master是否真的死了；
    # 数字1代表，当集群中有1个sentinel认为master死了时，才能真正认为该master已经不可用了。
    sentinel monitor mymaster 127.0.0.1 6379 1

    # 配置sentinel监控的master节点的密码（没有密码时不用填）
    sentinel auth-pass mymaster lidaqi

    # sentinel会向master发送心跳PING来确认master是否存活
    # 如果master在“一定时间范围”内不回应PONG或者是回复了一个错误消息
    # 那么这个sentinel会主观地认为这个master已经不可用了（SDOWN）
    # 而这个down-after-milliseconds就是用来指定这个“一定时间范围”的，单位是毫秒。
    sentinel down-after-milliseconds mymaster 5000

    # 在发生failover主备切换时，这个选项指定了最多可以有多少个slave同时对新的master进行同步
    # 这个数字越小，完成failover所需的时间就越长
    # 但是如果这个数字越大，就意味着越多的slave因为replication而不可用。
    # 可以通过将这个值设为 1 来保证每次只有一个slave处于不能处理命令请求的状态。
    sentinel parallel-syncs mymaster 1

    # 实现主从切换，完成故障转移的所需要的最大时间值。
    # 若Sentinel进程在该配置值内未能完成故障转移的操作，则认为本次故障转移操作失败。
    sentinel failover-timeout mymaster 60000

    # 指定Sentinel进程检测到Master-Name所指定的“Master主服务器”的实例异常的时候，所要调用的报警脚本。
    sentinel notification-script mymaster <script-path>
    ```

- 2、运行sentinel
    ```
    redis-sentinel ./sentinel.conf
    ```

- 3、监控节点
    ```
    每个哨兵会向其他哨兵定时发送消息，确认对方还“活着”。
    如果一个哨兵发现master节点挂掉，那么作为当前哨兵的角度看，这叫主观宕机。
    如果多个哨兵，都报告master节点没响应，那么系统会认为master客观宕机（真正挂掉），
    这时会触发哨兵选举投票协议，来决定是否执行自动鼓掌迁移，以及选择哪个slave作为新的master节点。
    ```
    ```
    当选举出新master节点后，原来挂掉的master节点（后面称old）又可用了，那么会把old作为当前master节点的slave节点，重新上线，自动替换配置。
    ```
    ```
    因为都有机会作为master节点（sentinel主节点故障重新选举），所以应该将密码都写到配置文件中，
    否则可能会出现，old master节点重新上线时，因为没有配置密码，连接不上当前master节点。
    ···
    // 为保证统一性，建议密码都一样
    masterauth 123456
    ```

> ## 哨兵选举策略

- 参考链接：
    - [https://zhuanlan.zhihu.com/p/95678924](https://zhuanlan.zhihu.com/p/95678924)
    - [https://blog.csdn.net/xujiamin0022016/article/details/93876870](https://blog.csdn.net/xujiamin0022016/article/details/93876870)

- 两个基本概念
    - S_DOWN（subjectively down）
    ```
    即主观宕机，如果一个哨兵它自己觉得master宕机了，就是主观宕机
    ```
    - O_DOWN（objectively down）
    ```
    即客观宕机，如果多个sentinel实例都认为一个master宕机了，则为客观宕机。

    即多个sentinel实例都认为master处于"SDOWN"状态，
    那么此时master将处于ODOWN，
    ODOWN可以简单理解为master已经被集群确定为"不可用",
    将会开启failover.
    ```

- 步骤
    - 1、使用下面条件筛选备选node

        - slave节点状态不处于（S_DOWN，O_DOWN，DISCONNECTED）
        - 最近一次ping应答时间不超过5倍ping的间隔（假如ping的间隔为1秒，则最近一次应答延迟不应超过5秒，sentinel默认为1秒）
        - info_refresh应答不超过3倍info_refresh的间隔（原理同2,redis sentinel默认为10秒）
        - slave节点与master节点失去联系的时间不能超过（ (now - master->s_down_since_time) + (master->down_after_period * 10)）。总体意思是说，slave节点与master同步太不及时的（比如新启动的节点），不应该参与被选举
        - Slave priority不等于0（这个是在配置文件中指定，默认配置为100）。

    - 2、从备选node中，按照如下顺序选择新的master

        - 按照slave优先级进行排序，slave priority越低，优先级就越高。
        - 看replica offset，哪个slave复制了越多的数据，offset越靠后，优先级就越高。
        - 较小的runid（每个redis实例，都会有一个runid，通常是一个40为的随机字符串，在启动时设置，重复概率非常小）
        - 以上条件都不足以区分出唯一的节点，则会看那个slave节点处理之前master发送的command多，就选谁

> ## 持久化

- RDB（将当前进程中的数据生成快照保存到硬盘，也叫快照持久化，冷备）

    - 手动触发
        - save（阻塞redis进程，直到RDB文件创建完毕）
        - bgsave（fork子进程，创建RDB文件，不阻塞redis进程）

    - 自动触发
        - save m n（在配置文件中配置save m n，指定当m秒内发生n次变化时，会触发bgsave。）
            ```
            ################################ SNAPSHOTTING  ################################
            #
            # Save the DB on disk:
            #
            #   save <seconds> <changes>
            #
            #   Will save the DB if both the given number of seconds and the given
            #   number of write operations against the DB occurred.
            #
            #   In the example below the behaviour will be to save:
            #   在900秒(15分钟)后，如果至少有一个键被改变
            #   300秒后(5分钟)如果至少有10个键被改变
            #   60秒后，如果至少改变了10000个键
            #
            #   Note: you can disable saving completely by commenting out all "save" lines.
            #
            #   It is also possible to remove all the previously configured save
            #   points by adding a save directive with a single empty string argument
            #   like in the following example:
            #
            #   save ""

            save 900 1
            save 300 10
            save 60 10000
            ```

    -  启动时加载
        
        - RDB文件的载入工作是在服务器启动时自动执行的，并没有专门的命令。
        - 但是由于**AOF的优先级更高**，因此当AOF开启时，Redis会优先载入AOF文件来恢复数据；
        - 只有当**AOF关闭时**，才会在Redis服务器启动时检测RDB文件，并自动载入。
    
    - RDB常用配置总结

        ```
        save m n：
        bgsave自动触发的条件；如果没有save m n配置，相当于自动的RDB持久化关闭，不过此时仍可以通过其他方式触发

        stop-writes-on-bgsave-error yes：
        当bgsave出现错误时，Redis是否停止执行写命令；设置为yes，则当硬盘出现问题时，可以及时发现，避免数据的大量丢失；设置为no，则Redis无视bgsave的错误继续执行写命令，当对Redis服务器的系统(尤其是硬盘)使用了监控时，该选项考虑设置为no

        rdbcompression yes：
        是否开启RDB文件压缩

        rdbchecksum yes：
        是否开启RDB文件的校验，在写入文件和读取文件时都起作用；关闭checksum在写入文件和启动文件时大约能带来10%的性能提升，但是数据损坏时无法发现

        dbfilename dump.rdb：
        RDB文件名

        dir ./：
        RDB文件和AOF文件所在目录
        ```

- AOF

    - 开启AOF
        ```
        # Redis服务器默认开启RDB，关闭AOF；要开启AOF，需要在配置文件中配置：

        appendonly yes
        ```

    - 执行流程（由于需要记录Redis的每条写命令，因此AOF不需要触发，下面介绍AOF的执行流程）
        ```
        1、命令追加(append)：将Redis的写命令追加到缓冲区aof_buf。
        2、文件写入(write)和文件同步(sync)：根据不同的同步策略将aof_buf中的内容同步到硬盘。
        3、文件重写(rewrite)：定期重写AOF文件，达到压缩的目的。
        ```

    - 启动时加载
        ```
        前面提到过，当AOF开启时，Redis启动时会优先载入AOF文件来恢复数据；只有当AOF关闭时，才会载入RDB文件恢复数据。
        ```

    - AOF常用配置总结
        ```
        下面是AOF常用的配置项，以及默认值；前面介绍过的这里不再详细介绍。

        appendonly no：
        是否开启AOF

        appendfilename "appendonly.aof"：
        AOF文件名

        dir ./：
        RDB文件和AOF文件所在目录

        appendfsync everysec：
        fsync持久化策略
        # always #每次有数据修改发生时都会写入AOF文件。
        # everysec #每秒钟同步一次，该策略为AOF的默认策略。
        # no #从不同步。高效但是数据不会被持久化。

        no-appendfsync-on-rewrite no：
        AOF重写期间是否禁止fsync；如果开启该选项，可以减轻文件重写时CPU和硬盘的负载（尤其是硬盘），但是可能会丢失AOF重写期间的数据；需要在负载和安全性之间进行平衡

        auto-aof-rewrite-percentage 100：
        文件重写触发条件之一

        auto-aof-rewrite-min-size 64mb：
        文件重写触发提交之一

        aof-load-truncated yes：
        如果AOF文件结尾损坏，Redis启动时是否仍载入AOF文件
        ```
- RDB 和 AOF 优缺点对比：

    - RDB持久化：
        - 优点
            ```
            RDB文件紧凑，体积小，网络传输快，适合全量复制；恢复速度比AOF快很多。
            当然，与AOF相比，RDB最重要的优点之一是对性能的影响相对较小。
            ```

        - 缺点
            ```
            致命缺点在于其数据快照的持久化方式注定了必然做不到实时持久化，
            而在数据越来越重要的今天，数据的大量丢失是无法接收的，因此AOF持久化成为主流。
            此外，RDB文件需要满足特定格式，兼容性差（如老版本的Redis不兼容新版本的RDB文件）。
            ```

    - AOF持久化：
        - 优点
            ```
            支持秒级持久化、兼容性好。
            ```

        - 缺点
            ```
            文件大，恢复速度慢，对性能影响大。
            ```