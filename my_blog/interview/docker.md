# Docker

## Dockerfile命令

指令不区分大小写。但是，命名约定为全部大写。

所有Dockerfile都必须以FROM命令开始。 

`FROM`命令会指定镜像基于哪个基础镜像创建，接下来的命令也会基于这个基础镜像（译者注：CentOS和Ubuntu有些命令可是不一样的）。

FROM命令可以多次使用，表示会创建多个镜像。

具体语法如下：

    FROM <image name>

例如：

    FROM ubuntu

上面的指定告诉我们，新的镜像将基于Ubuntu的镜像来构建。

继FROM命令，DockefFile还提供了一些其它的命令以实现自动化。
在文本文件或Dockerfile文件中这些命令的顺序就是它们被执行的顺序。

让我们了解一下这些有趣的Dockerfile命令吧。
1. `MAINTAINER`：设置该镜像的作者。语法如下：

        MAINTAINER <author name>

2. `RUN`：在shell或者exec的环境下执行的命令。RUN指令会在新创建的镜像上添加新的层面，接下来提交的结果用在Dockerfile的下一条指令中。语法如下：
        
        RUN <command>

3. `ADD`：复制文件指令。它有两个参数<source>和<destination>。destination是容器内的路径。source可以是URL或者是启动配置上下文中的一个文件。语法如下：

        ADD <src> <destination>

4. `CMD`：提供了容器默认的执行命令。 Dockerfile只允许使用一次CMD指令。 使用多个CMD会抵消之前所有的指令，只有最后一个指令生效。 CMD有三种形式：

        CMD ["executable","param1","param2"]
        CMD ["param1","param2"]
        CMD command param1 param2

5. `EXPOSE`：指定容器在运行时监听的端口。语法如下：

        EXPOSE <port>;

6. `ENTRYPOINT`：配置给容器一个可执行的命令，这意味着在每次使用镜像创建容器时一个特定的应用程序可以被设置为默认程序。同时也意味着该镜像每次被调用时仅能运行指定的应用。类似于CMD，Docker只允许一个ENTRYPOINT，多个ENTRYPOINT会抵消之前所有的指令，只执行最后的ENTRYPOINT指令。语法如下：

        ENTRYPOINT ["executable", "param1","param2"]
        ENTRYPOINT command param1 param2

7. `WORKDIR`：指定RUN、CMD与ENTRYPOINT命令的工作目录。语法如下：

        WORKDIR /path/to/workdir

8. `ENV`：设置环境变量。它们使用键值对，增加运行程序的灵活性。语法如下：

        ENV <key> <value>

9. `USER`：镜像正在运行时设置一个UID。语法如下：

        USER <uid>

10. `VOLUME`：授权访问从容器内到主机上的目录。语法如下：

        VOLUME ["/data"]


## 网上收集，讲解的更全面一些

> 本文是基于dockerfile原文理解整理出来的，如果理解有出入请各路大神指点。

docker build一个镜像需要一个dockerfile和context，context可以是一个文件路径，也可以是一个git仓库路径；
docker build会运行一个docker daemon，它不是一个命令行。build过程第一步会递归地把context下面的所有内容发送到docker daemon，影响性能。

在大多情况下请将context下内容清空，只保存需要build的dockerfile文件；

```
.dockerignore 
#为避免copy指令拷贝一些没必要的文件影响build性能

-f #指定dockerfile的路径
-t #指定构建成功后保存的位置，可以为一个仓库加上标签，可以指定多个仓库，docker build -t shykes/myapp:1.0.2 -t shykes/myapp:latest .

# escape=\        #docker默认的escape字符
# escape=`        #这条是解析指令
```

- 环境替代
    ```
    ${variable_name}也支持标准的bash修改器，如下bash运行：
    [root@node2 ~]# echo ${a:+test}

    [root@node2 ~]# echo ${a:-test}
    test
    [root@node2 ~]# a="1"
    [root@node2 ~]# echo ${a:+test}
    test
    [root@node2 ~]# echo ${a:-test}
    1
    ```
    ```
    ${variable:-word} 当variable被设置则返回它的value,当variable没有设值则结果为word
    ${variable:+word} 当variable被设置返回word结果，当variable没有被设置则返回为空
    ```
    ```
    ENV abc=hello
    ENV abc=bye def=$abc
    ENV ghi=$abc
    ```
    上面def结果为hello,ghi结果为bye,【注】：环境变量会在整行中生效

- .dockerignore file

    为了避免docker daemon读取无用文件，影响性能，需要忽略一些文件。只能通过'*'去匹配，使用'.'不起作用。'!'是排除匹配。
    ```
    # comment
    */temp*
    */*/temp*
    temp?
    !README.md
    ```

- FROM

    FROM指令初始化一个新的build stage,并且是后面指令的基础镜像。
    ```
    FROM <image>[:<tag>] [AS <name>]
    ```

- 理解ARG和FROM的交互

    FROM支持ARG指令申明的变量，ARG要在FROM之前申明，
    ```
    ARG  CODE_VERSION=latest
    FROM base:${CODE_VERSION}
    CMD  /code/run-app

    FROM extras:${CODE_VERSION}
    CMD  /code/run-extras
    ```

- RUN

    RUN支持2种格式：
    ```
    RUN <command> #shell格式，在linux上是 /bin/sh -c ，在Windows上是 cmd /S /C
    RUN ["executable", "param1", "param2"] #exec格式，必须使用“”，不能使用单引号
    ```

- CMD

    CMD指令有3种格式：
    ```
    CMD ["executable","param1","param2"] #exec格式
    CMD ["param1","param2"] #为为ENTRYPOINT的默认参数
    CMD command param1 param2 #shell格式
    ```
    CMD指令在dockerfile只可以有一个，假如存在多个，最后一个生效。CDM的主要作用是提供一个默认执行中的容器。它可以为可执行的，也可为非执行的，后者情况需要指定ENTRYPOINT指令，被当作为参数。

    RUN和CMD的区别：RUN会在build过程中执行并提交结果；CDM在build过程中不执行。

- LABEL

    LABEL指令添加metedata为镜像。一个LABEL是一对key-value。LABEL一般 位于FROM之后，不然会报错。
    ```
    LABEL "com.example.vendor"="ACME Incorporated"
    LABEL version="1.0"
    LABEL description="This text illustrates \
    that label-values can span multiple lines."
    LABEL maintainer="SvenDowideit@home.org.au"
    ```

- MAINTAINER

    指令代表生成镜像的作者。使用LABEL指令替代会更加灵活，可以使用LABEL替代。
    MAINTAINER指令代表生成镜像的作者。使用LABEL指令替代会更加灵活，可以使用LABEL替代。
    ```
    MAINTAINER <name>
    ```

- EXPOSE

    EXPOSE指令是为了通知docker,容器在运行时监听指定的端口。你能指定是tcp还是udp协议，默认是tcp协议。
    ```
    EXPOSE 80/tcp
    EXPOSE 80/udp
    ```
    在使用docker run -P时，将暴露所有EXPOSE port到主机的随机端口；

- ENV

    ENV指令设置环境变量，有2种格式：
    ```
    ENV <key> <value>
    ENV <key>=<value> ... #一次可以设置多个变量
    ```

- ADD

    ADD指令有2种格式：
    ```
    ADD [--chown=<user>:<group>] <src>... <dest>
    ADD [--chown=<user>:<group>] ["<src>",... "<dest>"] #这种格式适合包含有空格的路径
    ```
    上面的--chown只对Linux生效，Windows不起作用

    上面的src是基于context目录的相对路径【可以理解为context为根目录】

    上面的dest目录是绝对路径或者是想对于WORKDIR的相对路径。

    ```
    ADD hom* /mydir/               # 添加所有以"hom"开头的文件
    ADD hom?.txt /mydir/           # ?匹配单个字符,如 "home.txt"
    ADD test relativeDir/          # 添加test到 `WORKDIR`/relativeDir/
    ADD test /absoluteDir/         # 添加test到/absoluteDir/
    ```

    所有add的文件和目录UID和GID都为0.除非自己使用--chown指定。如下

    ```
    ADD --chown=55:mygroup files* /somedir/
    ADD --chown=10:11 files* /somedir/
    ```
    【注】：dokcer build支持stdin，如：(docker build - < archive.tar.gz)，dockerfile必须包含在归档文件的根目录。

    ADD遵守下面的规则：
    ```
    <src>必须在context目录下，不能ADD ../something /something，因为第一步docker build已经把context的内容发送到docker daemon了。
    假如<src>是URL且<dest>没有/结尾,src的文件直接拷贝到dest
    假如<src>是URL且<dest>有/结尾,src的文件拷贝到dest/filename
    ```

- COPY

    有2种格式
    ```
    COPY [--chown=<user>:<group>] <src>... <dest>
    COPY [--chown=<user>:<group>] ["<src>",... "<dest>"] #这种适合有空格的路径
    【注】：dokcer build如果使用stdin,没有build context，因此COPY不能使用。
    ```

- ENTRYPOINT

    ENTRYPOINT可以配置一个可以执行的容器。

    有2种格式：
    ```
    ENTRYPOINT ["executable", "param1", "param2"] #exec格式
        -- 这种格式可以接受docker run的参数并添加在后面，且会覆盖CMD指定的参数；
    ENTRYPOINT command param1 param2 #shell格式
        -- shell格式的将会执行为：/bin/sh -c command param1 param2，这就会导致command param1 param2非pid 1，在docker stop时并不能立即杀掉，而是等超时后发送SIGKILL杀掉。
        -- shell格式将会忽略任何CMD和docker run的命令行参数
        -- 如果保障pid 1，且开头不使用/bin/sh -c，可以使用下面方式在ENTRYPOINT后加exec,如下：
        FROM ubuntu
        ENTRYPOINT exec top -b
    ```

    - 理解CMD和ENTRYPOINT怎样互相作用

    ```
    dockerfile至少有一个CMD或者ENTRYPOINT命令
    CMD应该做为一个默认参数为ENTRYPOINT
    ```
    下面显示CMD和ENTRYPOINT的相互作用

    ![123](./docker/dockerfile-cmd-entry.png)

    compare

    【注】：假如CMD指令在基础镜像定义，配置ENTRYPOINT将会重置CMD为空。在这个场景，需要在当前镜像为CMD设置一个值。

- VOLUME

    ```
    VOLUME指令用指定名称创建一个挂载点，并且能持有来自本地主机或者其他容器挂载的volume。详细请见：https://docs.docker.com/storage/volumes/
    ```

- USER

    ```
    USER <user>[:<group>] 
    USER <UID>[:<GID>]
    ```

    USER指令用于设置用户名、组或者uid\gid为dockerfile随后要执行的RUN\CMD\ENTRYPOINT指令。

    在Windows假如没有内置帐号，必须先创建一个帐号
    ```
    FROM microsoft/windowsservercore
    # Create Windows user in the container
    RUN net user /add patrick
    # Set it for subsequent commands
    USER patrick
    ```

- WORKDIR

    WORKDIR指令是为了设置dockerfile后续执行RUN\CMD\ENTRYPOINT,COPY和ADD指令的工作路径。
    ```
    WORKDIR /path/to/workdir
    ```

    WORKDIR能够解析前面设置的ENV,如下：

    ```
    ENV DIRPATH /path
    WORKDIR $DIRPATH/$DIRNAME
    RUN pwd
    ```
    pwd的结果为：/path/$DIRNAME

- ARG

    ARG指令能定义一个变量，用户能在build过程中传递参数通过docker build --build-arg <varname>=<value>。一个dockerfile可以包含一到多个ARG。如下：

    ```
    FROM busybox
    ARG user1
    ARG buildno
    ```

    【注】:不建议在build过程中传递密码等信息，这些内容能通过docker history查看到当build时没有传递参数的值，可以给ARG提供默认值

    ```
    FROM busybox
    ARG user1=someuser
    ARG buildno=1
    ```

    当ENV和ARG指令同时存在时，ENV会覆盖ARG指令。
    ARG指令有预置的变量，如下

    ```
    HTTP_PROXY
    http_proxy
    HTTPS_PROXY
    https_proxy
    FTP_PROXY
    ftp_proxy
    NO_PROXY
    no_proxy
    ```

    可以通过命令行传递参数如：--build-arg HTTP_PROXY=http://user:pass@proxy.lon.example.com，预置的变量考虑到认证信息泄露，不会显示在docker history中。假如你需要覆盖内置变量可以通过下面指令：

    ```
    FROM ubuntu
    ARG HTTP_PROXY
    RUN echo "Hello World"
    ```

    ARG含有全局参数如下：

    ```
    TARGETPLATFORM - 构建结果的平台，如:linux/amd64, linux/arm/v7, windows/amd64.
    ```

    怎样使用，如下：

    ```
    FROM alpine
    ARG TARGETPLATFORM
    RUN echo "I'm building for $TARGETPLATFORM"
    ```

- ONBUILD

    ONBUIL指令用于作为一个触发器，当被做为基础镜像时会执行触发器指令。可以用到下面场景，一个镜像作为后面镜像的基础镜像，前面的镜像提供环境（如：python运行环境），后面的镜像可以通过使用ADD、RUN等指令完成代码的添加和执行构建脚本等，如下使用：

    ```
    ONBUILD ADD . /app/src
    ONBUILD RUN /usr/local/bin/python-build --dir /app/src
    ```

- STOPSIGNAL

    STOPSIGNAL指令是设置一个信号用于发送给容器并退出;

    ```
    STOPSIGNAL 9
    STOPSIGNAL SIGKILL
    ```

- HEALTHCHECK

    HEALTHCHECK有2种格式：

    ```
    HEALTHCHECK [OPTIONS] CMD command       #通过在容器内运行一个命令检查容器健康
    HEALTHCHECK NONE                        #禁用任何健康检查
    ```

    健康检查提供的选项有：

    ```
    --interval=DURATION            #默认为0s，代表在容器启动后多少秒运行首次检查，然后在前一次检查后的多少间隔执行下次检查
    --timeout=DURATION             #默认为30s，代表一次检查任务超过多少秒将认为本次检查失败
    --start-period=DURATION        #默认为0s，代表容器正常启动需要的时间。如果在这个启动时间内检查到失败将不计算到最大重试次数，如果在这个启动时间内检查到成功则认为容器启动成功。
    --retries=N                    #默认为3，代表连续的N次将认为容器是不监控的
    ```

    一个容器内只能存在一条健康检查，如果存在多条则最后一条是有效的。
    command的退出状态决定着容器的监控状态，如下值：

    ```
    0: success - 这个容器是健康的并且早已就绪
    1: unhealthy - 这个容器运行不正常
    2: reserved - 不能使用这个退出码
    ```

    例如：每5分钟检查一次，要在3s内返回网站主页面。如果需要debug，可以通过docker inspect查询。

    ```
    HEALTHCHECK --interval=5m --timeout=3s \
    CMD curl -f http://localhost/ || exit 1
    ```

- SHELL

    SHELL指令用于重写默认的shell格式的命令。如在linux是["/bin/sh", "-c"]，在Windows是["cmd", "/S", "/C"]。SHELL指令可以覆盖之前的SHELL指令，影响后续执行的指令。

    ```
    SHELL ["executable", "parameters"]
    ```

    如下案例：

    ```
    FROM microsoft/windowsservercore

    # 执行为 cmd /S /C echo default
    RUN echo default

    # 执行为 cmd /S /C powershell -command Write-Host default
    RUN powershell -command Write-Host default

    # 执行为 powershell -command Write-Host hello
    SHELL ["powershell", "-command"]
    RUN Write-Host hello

    # 执行为 cmd /S /C echo hello
    SHELL ["cmd", "/S", "/C"]
    RUN echo hello
    ```

原文参考链接

https://docs.docker.com/engine/reference/builder/

https://docs.docker.com/compose/django/

```
作者：大鹏之动
链接：https://www.jianshu.com/p/e37225134adf
来源：简书
著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。
```