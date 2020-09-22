# Elasticsearch(You Know, for Search)

> ## docker环境下集群部署（单机模拟）

- 1、安装elasticsearch镜像

        docker pull elasticsearch:7.9.0
- 2、创建data目录，yml配置文件

    - 创建数据存放目录
        ```
        mkdir data1 data2 data3
        ```
    - 创建3个elasticsearch配置文件:es1.yml,es2.yml,es3.yml
    
        es1.yml（master）
        ```
        cluster.name: elasticsearch-cluster
        node.name: es1
        network.host: 0.0.0.0
        http.port: 9200
        transport.tcp.port: 9300
        http.cors.enabled: true
        http.cors.allow-origin: "*"
        node.master: true
        node.data: true
        // 这里的IP填的是docker的内网IP
        discovery.zen.ping.unicast.hosts: ["172.21.0.11:9300","172.21.0.12:9300","172.21.0.13:9300"]
        discovery.zen.minimum_master_nodes: 2
        // es7新增的配置
        cluster.initial_master_nodes: ["es1"]
        ```
        es2.yml（slave）
        ```
        cluster.name: elasticsearch-cluster
        node.name: es2
        network.host: 0.0.0.0
        http.port: 9200
        transport.tcp.port: 9300
        http.cors.enabled: true
        http.cors.allow-origin: "*"
        node.master: true
        node.data: true
        // 这里的IP填的是docker的内网IP
        discovery.zen.ping.unicast.hosts: ["172.21.0.11:9300","172.21.0.12:9300","172.21.0.13:9300"]
        discovery.zen.minimum_master_nodes: 2
        cluster.initial_master_nodes: ["es1"]
        ```
        es3.yml（slave）
        ```
        cluster.name: elasticsearch-cluster
        node.name: es3
        network.host: 0.0.0.0
        http.port: 9200
        transport.tcp.port: 9300
        http.cors.enabled: true
        http.cors.allow-origin: "*"
        node.master: true
        node.data: true
        // 这里的IP填的是docker的内网IP
        discovery.zen.ping.unicast.hosts: ["172.21.0.11:9300","172.21.0.12:9300","172.21.0.13:9300"]
        discovery.zen.minimum_master_nodes: 2
        cluster.initial_master_nodes: ["es1"]
        ```
    注：上面配置的IP是docker中的内网IP，读者请自行更改，也可以映射宿主机的不同端口，也是一样的

- 3、调高宿主机JVM线程数限制数量

    - 修改宿主机sysctl.conf
        ```
        vim /etc/sysctl.conf
        ```
    - 加入以下内容：
        ```
        vm.max_map_count=262144 
        ```
    - 启用配置：
        ```
        sysctl -p
        ```
    注：这一步是为了防止启动容器时，报出如下错误：
    bootstrap checks failed max virtual memory areas vm.max_map_count [65530] likely too low, increase to at least [262144]

- 4、创建集群容器
    - 参数解释：
        - -e ES_JAVA_OPTS="-Xms256m -Xmx256m" ：设置jvm最大最小内存为256m（默认是1g，因测试机配置有限，内存足够大的可以不设置）
        - -v /root/es/es1.yml:/usr/share/elasticsearch/config/elasticsearch.yml ：映射配置文件
        - -v /root/es/data1:/usr/share/elasticsearch/data ：映射数据存放目录
        - --net ：指定容器网络
        - --ip ：指定容器IP

    ```
    // master
    docker run -d --name es1 --net es --ip 172.21.0.11 -e ES_JAVA_OPTS="-Xms256m -Xmx256m" -v /root/es/es1.yml:/usr/share/elasticsearch/config/elasticsearch.yml -v /root/es/data1:/usr/share/elasticsearch/data -p 9200:9200 -p 9300:9300 docker.io/elasticsearch:7.9.0

    // slave1
    docker run -d --name es2 --net es --ip 172.21.0.12 -e ES_JAVA_OPTS="-Xms256m -Xmx256m" -v /root/es/es2.yml:/usr/share/elasticsearch/config/elasticsearch.yml -v /root/es/data2:/usr/share/elasticsearch/data -p 9201:9201 -p 9301:9301 docker.io/elasticsearch:7.9.0

    // slave2
    docker run -d --name es3 --net es --ip 172.21.0.13 -e ES_JAVA_OPTS="-Xms256m -Xmx256m" -v /root/es/es3.yml:/usr/share/elasticsearch/config/elasticsearch.yml -v /root/es/data3:/usr/share/elasticsearch/data -p 9202:9202 -p 9302:9302 docker.io/elasticsearch:7.9.0
    ```

- 5、测试

    1.在浏览器地址栏访问http://127.0.0.1:9200/_cat/nodes?pretty 查看节点状态

    正常情况下应该会出现类似这样格式的内容，带*的为主节点
    ```
    172.21.0.12 46 96 99 6.53 3.73 2.32 dilmrt - es2
    172.21.0.13 67 96 99 6.53 3.73 2.32 dilmrt - es3
    172.21.0.11 63 96 99 6.53 3.73 2.32 dilmrt * es1
    ```

- 扩展-安装IK分词器
    ```
    // 在elasticsearch的bin目录下执行下面的命令

    ./elasticsearch-plugin install https://github.com/medcl/elasticsearch-analysis-ik/releases/download/v7.9.0/elasticsearch-analysis-ik-7.9.0.zip
    ```
    ```
    // 最终提示：

    ···
    Installed analysis-ik

    // 就表示安装成功了
    ```

> ## elasticsearch.yml配置参数解析

- 示例
    ```
    cluster.name: elasticsearch-cluster
    node.name: es-node1
    #index.number_of_shards: 2
    #index.number_of_replicas: 1
    network.bind_host: 0.0.0.0
    network.publish_host: 192.168.9.219
    http.port: 9200
    transport.tcp.port: 9300
    http.cors.enabled: true
    http.cors.allow-origin: "*"
    node.master: true 
    node.data: true  
    discovery.zen.ping.unicast.hosts: ["es-node1:9300","es-node2:9301","es-node3:9302"]
    discovery.zen.minimum_master_nodes: 2
    cluster.initial_master_nodes: ["es-node1"]
    ```

- 参数解析

```
cluster.name：用于唯一标识一个集群，不同的集群，其 cluster.name 不同，集群名字相同的所有节点自动组成一个集群。如果不配置改属性，默认值是：elasticsearch。

node.name：节点名，默认随机指定一个name列表中名字。集群中node名字不能重复

index.number_of_shards: 默认的配置是把索引分为5个分片

index.number_of_replicas:设置每个index的默认的冗余备份的分片数，默认是1

    - 通过 index.number_of_shards，index.number_of_replicas默认设置索引将分为5个分片，每个分片1个副本，共10个结点。
    - 禁用索引的分布式特性，使索引只创建在本地主机上：
    - index.number_of_shards: 1
    - index.number_of_replicas: 0
    - 但随着版本的升级 将不在配置文件中配置而实启动ES后，再进行配置
```

```
bootstrap.memory_lock: true 当JVM做分页切换（swapping）时，ElasticSearch执行的效率会降低，推荐把ES_MIN_MEM和ES_MAX_MEM两个环境变量设置成同一个值，并且保证机器有足够的物理内存分配给ES，同时允许ElasticSearch进程锁住内存

network.bind_host: 设置可以访问的ip,可以是ipv4或ipv6的，默认为0.0.0.0，这里全部设置通过

network.publish_host:设置其它结点和该结点交互的ip地址，如果不设置它会自动判断，值必须是个真实的ip地址

    - 同时设置bind_host和publish_host两个参数可以替换成network.host
    - network.bind_host: 192.168.9.219
    - network.publish_host: 192.168.9.219
    - =>network.host: 192.168.9.219
```

```
http.port:设置对外服务的http端口，默认为9200

transport.tcp.port: 设置节点之间交互的tcp端口，默认是9300

http.cors.enabled: 是否允许跨域REST请求

http.cors.allow-origin: 允许 REST 请求来自何处

node.master: true 配置该结点有资格被选举为主结点（候选主结点），用于处理请求和管理集群。如果结点没有资格成为主结点，那么该结点永远不可能成为主结点；如果结点有资格成为主结点，只有在被其他候选主结点认可和被选举为主结点之后，才真正成为主结点。

node.data: true 配置该结点是数据结点，用于保存数据，执行数据相关的操作（CRUD，Aggregation）；

discovery.zen.minimum_master_nodes: //自动发现master节点的最小数，如果这个集群中配置进来的master节点少于这个数目，es的日志会一直报master节点数目不足。（默认为1）为了避免脑裂，个数请遵从该公式 => (totalnumber of master-eligible nodes / 2 + 1)。 * 脑裂是指在主备切换时，由于切换不彻底或其他原因，导致客户端和Slave误以为出现两个active master，最终使得整个集群处于混乱状态*

discovery.zen.ping.unicast.hosts： 集群各个节点IP地址，也可以使用es-node等名称，需要各节点能够解析
    
cluster.initial_master_nodes: ["node-1"]
参数设置一系列符合主节点条件的节点的主机名或 IP 地址来引导启动集群。手动指定可以成为 mater 的所有节点的 name 或者 ip，这些配置将会在第一次选举中进行计算
```