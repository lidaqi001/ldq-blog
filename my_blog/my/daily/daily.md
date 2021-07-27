<!--
// 内容折叠显示
<details>
    <summary><mark><font color=darkred>点击查看详细内容</font></mark></summary>
    ...内容...    
</details>
-->

> ## 2021.7.27-Golang中的包循环导入错误(import cycle not allowed)
<details>
    <summary><mark><font color=darkred>点击查看详细内容</font></mark></summary>

- 在go开发中,使用import导入相应包时,有时会碰到 `import cycle not allowed` 这个错误
- 错误产生的原因在于,你导入的包和你当前的包,互相导入(依赖)
- 这里举一个例子:

`Test/A`
```
package A

import "Test/B"
```

`Test/B`
```
package B

import "Test/A"
```
- 这里发生了什么?
    - A导入B
    - B又导入A
    - 致使两个包相互依赖,互相导入,从而出现 `import cycle not allowed` 这个错误
- 解决办法
    - 做好项目的包规划,有冲突的引用方法新建一个包,解决循环导入的错误
    - 网上有通过 `interface(接口)` 的方法解决这个问题,但我认为这样增加了复杂度,将简单的问题复杂化了

</details>

> ## [2021.7.20-Hyperf框架grpc实现](./my/sourceAnalysis/hyperf-grpc)

> ## 2021.1.7-/etc/init.d和/etc/rc.d/rc.local的区别
<details>
  <summary><mark><font color=darkred>点击查看详细内容</font></mark></summary>

- /etc/init.d 是一个目录（这个目录里面用于放置shell脚本，注意是脚本）：
    - 是/etc/rc.d/init.d的软链接
    - 这些脚本是启动脚本，用于Linux中服务的启动、停止、重启
    - 比如各种Linux中的服务都会有启动脚本放在这里，像是ssh服务的文件sshd，nginx、php-fpm的启动文件
- /etc/rc.d/rc.local 是一个文件（这个文件用于用户自定义开机启动程序）
    - 也就是说用户可以把需要开机启动的命令、运行可执行脚本的命令写入这个文件，这样就可以在系统启动时自动执行这个命令
    - 比如把一个shell脚本的完整路径写入这个文件，那这个shell脚本就会在开机后自动执行
    
- 为了方便理解，这里我们放一个详细介绍Linux启动流程的链接
    - [进去看看](./my/knowledgePoint/linuxStartProcessFlow)

</details>

> ## [2021.1.6-安装supervisor（及产生的问题）](./interview/linux?id=安装Supervisor)

> ## 2020.10.29-v2rayN报错
    
<details>
  <summary><mark><font color=darkred>点击查看详细内容</font></mark></summary>

- 报错内容：

    ```
    failed to read response header > websocket: close 1000 (normal)
    ```
- 错误原因：系统时间与服务器时间不一致
- 解决办法：校准系统时间

</details>

> ## 2020.10.27-windows docker xshell 默认登录密码

<details>
  <summary><mark><font color=darkred>点击查看详细内容</font></mark></summary>

- boot2docker用户和密码

| 用户    | 密码   | 进入方式 |
|--------|--------|------|
| docker | tcuser | ssh  |
| root   |        | command：sudo -i (docker用户下执行)  |

</details>

> ## [2020.9.23-redis持久化](./interview/redis?id=持久化)

> ## [2020.9.15-redis哨兵选举master策略](./interview/redis?id=哨兵选举策略)

> ## [2020.9.14-redis哨兵模式](./interview/redis?id=哨兵配置)

> ## [2020.9.11-redis主从配置](./interview/redis?id=主从配置)

> ## [2020.9.9-docker环境下elasticsearch集群部署（单机模拟）](./interview/elasticsearch?id=docker环境下集群部署（单机模拟）)

> ## [2020.9.8-MySQLd启动命令](./interview/mysql/mysql?id=mysqld启动命令)

> ## 2020.9.7-MySQL主从

<details>
  <summary><mark><font color=darkred>点击查看详细内容</font></mark></summary>

- MySQL主从服务器配置
    - MySQL版本：8.0.21
    - [主从同步的机制](./interview/mysql/mysql?id=数据库主从复制原理)
    - [步骤](./interview/mysql/mysql?id=主从复制操作)

</details>

> ## 2020.9.3-无分类地址 CIDR 10.100.122.2/24

<!-- <details>
  <summary><mark><font color=darkred>点击查看详细内容</font></mark></summary> -->

- 无分类地址 CIDR

    正因为 IP 分类存在许多缺点，所以后面提出了无分类地址的方案，即 CIDR。

    这种方式不再有分类地址的概念，32 比特的 IP 地址被划分为两部分，前面是网络号，后面是主机号。

    “ 怎么划分网络号和主机号的呢？
    ”
    表示形式 a.b.c.d/x，其中 /x 表示前 x 位属于网络号， x 的范围是 0 ~ 32，这就使得 IP 地址更加具有灵活性。

    比如 10.100.122.2/24，这种地址表示形式就是 CIDR，/24 表示前 24 位是网络号，剩余的 8 位是主机号。

    ![cidr](/my_blog/images/cidr.jpg)

<!-- </details> -->

> ## 2020.9.2-firewalld进程不能启动

<details>
  <summary><mark><font color=darkred>点击查看详细内容</font></mark></summary>

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

</details>

> ## 2020.9.1-Temporary failure in name resolution 错误解决方法

<details>
  <summary><mark><font color=darkred>点击查看详细内容</font></mark></summary>

## 问题产生：
    DNS服务器地址失效
        
## 解决：
    更换新的DNS服务器地址
    # /etc/resolv.conf它是DNS客户机配置文件，用于设置DNS服务器的IP地址及DNS域名
    nameserver 202.102.192.68

</details>