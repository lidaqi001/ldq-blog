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

> ## 主从是如何同步数据的