> ## 2021.1.7-/etc/init.d和/etc/rc.d/rc.local的区别
- /etc/init.d：是/etc/rc.d/init.d的软链接、
- /etc/init.d是个目录（这个目录里面用于放置shell脚本，注意是脚本）
    - 这些脚本是启动脚本，用于Linux中服务的启动、停止、重启
    - 比如各种Linux中的服务都会有启动脚本放在这里，像是ssh服务的文件sshd，nginx、php-fpm的启动文件
- /etc/rc.d/rc.local 是一个文件（这个文件用于用户自定义开机启动程序）
    - 也就是说用户可以把需要开机启动的命令、运行可执行脚本的命令写入这个文件，这样就可以在系统启动时自动执行这个命令
    - 比如把一个shell脚本的完整路径写入这个文件，那这个shell脚本就会在开机后自动执行
- 为了方便理解，这里我们放一个详细介绍Linux启动流程的链接
    - [进去看看](./my/knowledgePoint/linuxStartProcessFlow)

> ## 2021.1.6-安装supervisor（及产生的问题）
- 安装：
    
    ```
    # 安装Supervisor
    $ yum install python-setuptools 
    $ easy_install supervisor

    $ # 自定义配置Supervisor
    $ mkdir -vp /etc/supervisor/supervisord.d
    $ cd /etc/supervisor
    $ touch supervisord.conf
    $ echo_supervisord_conf > /etc/supervisor/supervisord.conf
    ```

- 配置：
    - 打开supervisord.conf
    - 文件最后面的位置，改为： [include] files = supervisord.d/*.ini

- 配置守护进程的配置文件
    ```
    [program:项目名称]
    command=bash 脚本.sh 
    directory=/root
    autostart=true
    autorestart=true
    stderr_logfile=/var/log/日志.err.log
    stdout_logfile=/var/log/日志.out.log 
    environment=ASPNETCORE_ENVIRONMENT=Production 
    user=root
    stopsignal=INT
    startsecs=1
    ```
- 运行 

    ```
    $ supervisord -c /etc/supervisor/supervisord.conf
    ```
- 检查运行情况
    ```
    // 查看守护进程状态
    $ supervisorctl status
    // 查看supervisor进程是否启动
    $ ps -ef | grep supervisor
    ```
- 问题：
    - 解决 unix:///tmp/supervisor.sock no such file 问题
    ```
    // 打开配置文件
    $ vim /etc/supervisord.conf
    
    // 这里把所有的/tmp开头的路径改掉
    // tmp文件夹下容易被linux自动清掉
    // 例子：/tmp/supervisor.sock 改成 /var/run/supervisor.sock
    ```
    
> ## 2020.10.29-v2rayN报错
- 报错内容：

    ```
    failed to read response header > websocket: close 1000 (normal)
    ```
- 错误原因：系统时间与服务器时间不一致
- 解决办法：校准系统时间

> ## 2020.10.27-windows docker xshell 默认登录密码
- boot2docker用户和密码

| 用户    | 密码   | 进入方式 |
|--------|--------|------|
| docker | tcuser | ssh  |
| root   |        | command：sudo -i (docker用户下执行)  |


> ## 2020.9.23-redis持久化
- [进去看看](../../interview/redis?id=持久化)

> ## 2020.9.15-redis哨兵选举master策略
- [进去看看](../../interview/redis?id=哨兵选举策略)

> ## 2020.9.14-redis哨兵模式
- [进去看看](../../interview/redis?id=哨兵配置)

> ## 2020.9.11-redis主从配置
- [进去看看](../../interview/redis?id=主从配置)

> ## 2020.9.9-docker环境下elasticsearch集群部署（单机模拟）
- [进去看看](../../interview/elasticsearch?id=docker环境下集群部署（单机模拟）)

> ## 2020.9.8-MySQLd启动命令
- [启动命令](../../interview/mysql/mysql?id=mysqld启动命令)

> ## 2020.9.7-MySQL主从

- MySQL主从服务器配置
    - MySQL版本：8.0.21
    - [主从同步的机制](../../interview/mysql/mysql?id=数据库主从复制原理)
    - [步骤](../../interview/mysql/mysql?id=主从复制操作)

> ## 2020.9.3-10.100.122.2/24

- 无分类地址 CIDR

    正因为 IP 分类存在许多缺点，所以后面提出了无分类地址的方案，即 CIDR。

    这种方式不再有分类地址的概念，32 比特的 IP 地址被划分为两部分，前面是网络号，后面是主机号。

    “ 怎么划分网络号和主机号的呢？
    ”
    表示形式 a.b.c.d/x，其中 /x 表示前 x 位属于网络号， x 的范围是 0 ~ 32，这就使得 IP 地址更加具有灵活性。

    比如 10.100.122.2/24，这种地址表示形式就是 CIDR，/24 表示前 24 位是网络号，剩余的 8 位是主机号。

    ![cidr](./images/cidr.jpg)

> ## 2020.9.2-firewalld进程不能启动

- firewalld进程启动不了（报错超时）[参考链接](https://blog.csdn.net/crynono/article/details/76132611)
    
    - 报错信息如下
    ```
    [root@VM_0_6_centos ~]#  systemctl status firewalld 
    ● firewalld.service - firewalld - dynamic firewall daemon
    Loaded: loaded (/usr/lib/systemd/system/firewalld.service; disabled; vendor preset: enabled)
    Active: failed (Result: timeout) since Wed 2020-09-02 10:19:58 CST; 4s ago
        Docs: man:firewalld(1)
    Process: 31626 ExecStart=/usr/sbin/firewalld --nofork --nopid $FIREWALLD_ARGS (code=exited, status=0/SUCCESS)
    Main PID: 31626 (code=exited, status=0/SUCCESS)

    Sep 02 10:18:28 VM_0_6_centos systemd[1]: Starting firewalld - dynamic firewall daemon...
    Sep 02 10:18:28 VM_0_6_centos firewalld[31626]: WARNING: AllowZoneDrifting is enabled. This is considered an insecure configuration option. It will be removed in a future release. Please consider disabling it now.
    Sep 02 10:19:58 VM_0_6_centos systemd[1]: firewalld.service start operation timed out. Terminating.
    Sep 02 10:19:58 VM_0_6_centos systemd[1]: Failed to start firewalld - dynamic firewall daemon.
    Sep 02 10:19:58 VM_0_6_centos systemd[1]: Unit firewalld.service entered failed state.
    Sep 02 10:19:58 VM_0_6_centos systemd[1]: firewalld.service failed.

    ```
- 执行以下命令后，恢复running：
    ```
    systemctl stop firewalld;pkill -f firewalld;systemctl start firewalld
    ```
    - 对于该解决方法，网上的解释
    ```
    该方法参考来源：

    http://centosfaq.org/centos/centos-7-firewalldservice-operation-time-out-systemctl-firewalld-issues/

    来自官方的faq,给了一些解释： systemd didn’t know about the process that it didn’t start in the first place of course
    ```

> ## 2020.9.1

- Temporary failure in name resolution 错误解决方法

    ```
    # 问题产生：
        DNS服务器地址失效
        
    # 解决：
        更换新的DNS服务器地址

        # /etc/resolv.conf它是DNS客户机配置文件，用于设置DNS服务器的IP地址及DNS域名
        nameserver 202.102.192.68
    ```
