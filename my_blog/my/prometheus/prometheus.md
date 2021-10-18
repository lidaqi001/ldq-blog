> ## [**prometheus操作指南**](https://github.com/lidaqi001/prometheus-book)
---
## 导航
> 1) [简介](#简介)
> 2) [架构](#架构)
> 3) [数据模型](#数据模型)
> 4) [Metric(指标)类型](#Metric类型)
> 5) [PromQL(prometheus内置数据查询语言)](#PromQL)


---

> ## [简介](#导航)
Prometheus（普罗米修斯）是一套开源的监控&报警&时间序列数据库的组合，起始是由SoundCloud公司开发的。

`基本原理:` 是通过HTTP协议周期性抓取被监控组件的状态，这样做的好处是任意组件只要提供HTTP接口就可以接入监控系统，不需要任何SDK或者其他的集成过程。这样做非常适合虚拟化环境比如VM或者Docker。

`适用场景:` 应该是为数不多的适合Docker、Mesos、Kubernetes环境的监控系统之一。

`Exporter:` 输出被监控组件信息的HTTP接口被叫做exporter 。目前互联网公司常用的组件大部分都有exporter可以直接使用，比如Varnish、Haproxy、Nginx、MySQL、Linux 系统信息 (包括磁盘、内存、CPU、网络等等)，具体支持的源看：https://github.com/prometheus。

`优势:` 
- 易于管理
- 有很多可用的exporter
- 高效灵活的查询语句
- 支持本地和远程存储
- 采用http协议，默认pull模式拉取数据，也可以通过中间网关push数据
- 支持自动发现
- 可扩展
- 易集成

---

> ## [架构](#导航)
![image](https://note.youdao.com/yws/api/personal/file/BA6BE8F5C3014C168897A9EE75609127?method=download&shareKey=c18586542683aa7d36f1caf112763ff0)

它的服务过程是这样的 `Prometheus daemon` 负责定时去目标上抓取metrics(指标) 数据，每个抓取目标需要暴露一个http服务的接口给它定时抓取。
- `Prometheus`：支持通过配置文件、文本文件、zookeeper、Consul、DNS SRV lookup等方式指定抓取目标。支持很多方式的图表可视化，例如十分精美的Grafana，自带的Promdash，以及自身提供的模版引擎等等，还提供HTTP API的查询方式，自定义所需要的输出。
- `Alertmanager`：是独立于Prometheus的一个组件，可以支持Prometheus的查询语句，提供十分灵活的报警方式。
- `PushGateway`：这个组件是支持Client主动推送metrics到PushGateway，而Prometheus只是定时去Gateway上抓取数据。
如果有使用过statsd的用户，则会觉得这十分相似，只是statsd是直接发送给服务器端，而Prometheus主要还是靠进程主动去抓取。

想了解更多, 访问[prometheus.io](https://prometheus.io/)以获取完整的文档，示例和指南。

---

> ## [数据模型](#导航)

- 采用单值模型, 数据模型的核心概念是metric,labels和samples.
- 格式：`<metric name>{<label name>=<label value>, …}`
    - 例如：http_requests_total{method="POST",endpoint="/api/tracks"}
- metric的命名具有业务含义，比如http_request_total
    - 指标的类型分为：Counter， Gauge，Historgram，Summary
- labels用于表示维度.Samples由时间戳和数值组成
- jobs and instances
    - Prometheus 会自动生成target和instances作为标签
        - job: api-server
            - instance 1: 1.2.3.4:8080
            - instance 2: 1.2.3.4:8081

---

> ## [Metric类型](#导航)

> 从存储上来讲所有的监控指标metric都是相同的，但是在不同的场景下这些metric又有一些细微的差异。 <br><br>
例如，在Node Exporter返回的样本中指标node_load1反应的是当前系统的负载状态，随着时间的变化这个指标返回的样本数据是在不断变化的。而指标node_cpu所获取到的样本数据却不同，它是一个持续增大的值，因为其反应的是CPU的累积使用时间，从理论上讲只要系统不关机，这个值是会无限变大的。<br><br>
为了能够帮助用户理解和区分这些不同监控指标之间的差异，Prometheus定义了4种不同的指标类型(metric type)：
- <a href="#Counter">Counter（计数器）</a>
- <a href="#Gauge">Gauge（仪表盘）</a>
- <a href="#Histogram">Histogram（直方图）</a>
- <a href="#Summary">Summary（摘要）</a>

在Exporter返回的样本数据中，其注释中也包含了该样本的类型。例如：
```
# HELP node_cpu Seconds the cpus spent in each mode.
# TYPE node_cpu counter
node_cpu{cpu="cpu0",mode="idle"} 362812.7890625
```

- <a id="Counter"></a>`Counter`：只增不减的计数器

Counter类型的指标其工作方式和计数器一样，只增不减（除非系统发生重置）。常见的监控指标，如http_requests_total，node_cpu都是Counter类型的监控指标。 一般在定义Counter类型指标的名称时推荐使用_total作为后缀。

Counter是一个简单但有强大的工具，例如我们可以在应用程序中记录某些事件发生的次数，通过以时序的形式存储这些数据，我们可以轻松的了解该事件产生速率的变化。 PromQL内置的聚合操作和函数可以让用户对这些数据进行进一步的分析：

例如，通过rate()函数获取HTTP请求量的增长率：

    rate(http_requests_total[5m])

查询当前系统中，访问量前10的HTTP地址：

    topk(10, http_requests_total)

- <a id="Gauge"></a>`Gauge`：可增可减的仪表盘

与Counter不同，Gauge类型的指标侧重于反应系统的当前状态。因此这类指标的样本数据可增可减。常见指标如：node_memory_MemFree（主机当前空闲的内容大小）、node_memory_MemAvailable（可用内存大小）都是Gauge类型的监控指标。

通过Gauge指标，用户可以直接查看系统的当前状态：

node_memory_MemFree
对于Gauge类型的监控指标，通过PromQL内置函数delta()可以获取样本在一段时间返回内的变化情况。例如，计算CPU温度在两个小时内的差异：

    delta(cpu_temp_celsius{host="zeus"}[2h])

还可以使用deriv()计算样本的线性回归模型，甚至是直接使用predict_linear()对数据的变化趋势进行预测。例如，预测系统磁盘空间在4个小时之后的剩余情况：

    predict_linear(node_filesystem_free{job="node"}[1h], 4 * 3600)

- <a id="Histogram"></a><a id="Summary"></a>使用 `Histogram` 和 `Summary` 分析数据分布情况

除了Counter和Gauge类型的监控指标以外，Prometheus还定义了Histogram和Summary的指标类型。Histogram和Summary主用用于统计和分析样本的分布情况。

在大多数情况下人们都倾向于使用某些量化指标的平均值，例如CPU的平均使用率、页面的平均响应时间。这种方式的问题很明显，以系统API调用的平均响应时间为例：如果大多数API请求都维持在100ms的响应时间范围内，而个别请求的响应时间需要5s，那么就会导致某些WEB页面的响应时间落到中位数的情况，而这种现象被称为长尾问题。

为了区分是平均的慢还是长尾的慢，最简单的方式就是按照请求延迟的范围进行分组。例如，统计延迟在010ms之间的请求数有多少而1020ms之间的请求数又有多少。通过这种方式可以快速分析系统慢的原因。Histogram和Summary都是为了能够解决这样问题的存在，通过Histogram和Summary类型的监控指标，我们可以快速了解监控样本的分布情况。

例如，指标prometheus_tsdb_wal_fsync_duration_seconds的指标类型为Summary。 它记录了Prometheus Server中wal_fsync处理的处理时间，通过访问Prometheus Server的/metrics地址，可以获取到以下监控样本数据：
```
# HELP prometheus_tsdb_wal_fsync_duration_seconds Duration of WAL fsync.
# TYPE prometheus_tsdb_wal_fsync_duration_seconds summary
prometheus_tsdb_wal_fsync_duration_seconds{quantile="0.5"} 0.012352463
prometheus_tsdb_wal_fsync_duration_seconds{quantile="0.9"} 0.014458005
prometheus_tsdb_wal_fsync_duration_seconds{quantile="0.99"} 0.017316173
prometheus_tsdb_wal_fsync_duration_seconds_sum 2.888716127000002
prometheus_tsdb_wal_fsync_duration_seconds_count 216
```
从上面的样本中可以得知当前Prometheus Server进行wal_fsync操作的总次数为216次，耗时2.888716127000002s。其中中位数（quantile=0.5）的耗时为0.012352463，9分位数（quantile=0.9）的耗时为0.014458005s。

在Prometheus Server自身返回的样本数据中，我们还能找到类型为Histogram的监控指标prometheus_tsdb_compaction_chunk_range_bucket。
```
# HELP prometheus_tsdb_compaction_chunk_range Final time range of chunks on their first compaction
# TYPE prometheus_tsdb_compaction_chunk_range histogram
prometheus_tsdb_compaction_chunk_range_bucket{le="100"} 0
prometheus_tsdb_compaction_chunk_range_bucket{le="400"} 0
prometheus_tsdb_compaction_chunk_range_bucket{le="1600"} 0
prometheus_tsdb_compaction_chunk_range_bucket{le="6400"} 0
prometheus_tsdb_compaction_chunk_range_bucket{le="25600"} 0
prometheus_tsdb_compaction_chunk_range_bucket{le="102400"} 0
prometheus_tsdb_compaction_chunk_range_bucket{le="409600"} 0
prometheus_tsdb_compaction_chunk_range_bucket{le="1.6384e+06"} 260
prometheus_tsdb_compaction_chunk_range_bucket{le="6.5536e+06"} 780
prometheus_tsdb_compaction_chunk_range_bucket{le="2.62144e+07"} 780
prometheus_tsdb_compaction_chunk_range_bucket{le="+Inf"} 780
prometheus_tsdb_compaction_chunk_range_sum 1.1540798e+09
prometheus_tsdb_compaction_chunk_range_count 780
```
与`Summary`类型的指标相似之处在于`Histogram`类型的样本同样会反应当前指标的记录的总数(以`_count`作为后缀)以及其值的总量（以`_sum`作为后缀）。不同在于`Histogram`指标直接反应了在不同区间内样本的个数，区间通过标签`len`进行定义。同时对于`Histogram`的指标，我们还可以通过`histogram_quantile()`函数计算出其值的分位数。不同在于 `Histogram`通过`histogram_quantile`函数是在**服务器端**计算的分位数。而`Sumamry`的分位数则是直接在**客户端**计算完成。<br>

因此对于分位数的计算而言，`Summary`在通过`PromQL`进行查询时有更好的性能表现，而`Histogram`则会消耗更多的资源。反之对于客户端而言`Histogram`消耗的资源更少。在选择这两种方式时用户应该按照自己的实际场景进行选择。

---

> ## [PromQL](#导航)
> prometheus内置数据查询语言
- [初识PromQL](https://github.com/lidaqi001/prometheus-book/blob/master/promql/prometheus-query-language.md)
- [PromQL操作符](https://github.com/lidaqi001/prometheus-book/blob/master/promql/prometheus-promql-operators-v2.md)
- [PromQL聚合操作](https://github.com/lidaqi001/prometheus-book/blob/master/promql/prometheus-aggr-ops.md)
- [PromQL内置函数](https://github.com/lidaqi001/prometheus-book/blob/master/promql/prometheus-promql-functions.md)

---
> 参考:<br>

### prometheus
- [Prometheus操作指南](https://github.com/lidaqi001/prometheus-book)
- [PROMETHEUS METRIC TYPES](https://prometheus.io/docs/concepts/metric_types/)
- [PROMETHEUS QUERY FUNCTIONS](https://prometheus.io/docs/prometheus/latest/querying/functions/)
- [指标届的独角兽Prometheus](https://www.cnblogs.com/yunqishequ/p/10438485.html)
- [summary和histogram指标的简单理解](https://blog.csdn.net/wtan825/article/details/94616813)
### pushgateway
- [prometheus数据上报方式-pushgateway](https://www.cnblogs.com/xiaobaozi-95/p/10684524.html)
### 微服务应用
- [go-micro框架整合prometheus监控](https://blog.csdn.net/qq_39199351/article/details/105181285)
- [使用Prometheus搞定微服务监控](https://www.cnblogs.com/kevinwan/p/14463445.html)
- [go micro metrics 接入Prometheus、Grafana](https://segmentfault.com/a/1190000023530052)
### Kubernetes(k8s)
- [Prometheus监控Kubernetes集群](https://www.cnblogs.com/rexcheny/p/10675891.html)
- [Prometheus在Kubernetes下的服务发现机制](https://www.cnblogs.com/YaoDD/p/11391310.html)
---

# `扩展`

> ## 整体设计思路
![image](https://note.youdao.com/yws/api/personal/file/2779FCD11B4A473EB911D2A80D363EB9?method=download&shareKey=09e0d538dcab3afec3abeb5bdc97889c)
Prometheus的整体技术架构可以分为几个重要模块：
- Main function：作为入口承担着各个组件的启动，连接，管理。以Actor-Like的模式协调组件的运行
- Configuration：配置项的解析，验证，加载
- Scrape discovery manager：服务发现管理器同抓取服务器通过同步channel通信，当配置改变时需要重启服务生效。
- Scrape manager：抓取指标并发送到存储组件
- Storage：
    - Fanout Storage：存储的代理抽象层，屏蔽底层local storage和remote storage细节，samples向下双写，合并读取。
    - Remote Storage：Remote Storage创建了一个Queue管理器，基于负载轮流发送，读取客户端merge来自远端的数据。
    - Local Storage：基于本地磁盘的轻量级时序数据库。
- PromQL engine：查询表达式解析为抽象语法树和可执行查询，以Lazy Load的方式加载数据。
- Rule manager：告警规则管理
- Notifier：通知派发管理器
- Notifier discovery：通知服务发现
- Web UI and API：内嵌的管控界面，可运行查询表达式解析，结果展示。