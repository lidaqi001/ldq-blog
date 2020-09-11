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
    ```