# linux 常见面试题

## 命令

1.查看某个进程的信息

    ps -p PID
    ls /proc/PID

2.查找文件大于5M的

    查找大于5M的文件
    find . -type f -size +5m 
    查找小于5M的文件
    find . -type f -size -5m

## 文件结构

1.硬链接和软连接区别

    硬链接(Hard Link)多个文件名指向同一索引节点(Inode),计数器+1
    ln /usr/local/nginx/sbin/nginx  /usr/local/bin/nginx

    软连接(Symbolic Link)inode节点的数据项保存原文件路径
    ln -s /usr/local/nginx/sbin/nginx /usr/local/bin/nginx

## 安装Supervisor

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