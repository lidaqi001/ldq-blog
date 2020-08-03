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