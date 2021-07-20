# hyperf-grpc实现

> hyperf version: 2.2.0

- grpc
    - 解析类 `src/grpc/src/Parser.php`
        - 封包 / 解包
        - 编码 / 解码
    - 客户端 `src/grpc-client/src/GrpcClient.php`
        - 使用 `Swoole\Coroutine\Http2` 发起http2客户端请求
    - 服务端 `src/grpc-server/src/Server.php`
        - 运行在 `http` 服务下
        - 解析`request_uri`,匹配服务
            - 通过解析 `request_uri` , 得到如下格式的信息 `/{package}.{service}/{rpc}`
            - 调用框架封装的 `gRPC` 编解码类 `\Hyperf\Grpc\Parser::deserializeMessage`

        > gRPC server 如何对 gRPC 请求进行处理的?
        >
        > 处理函数: \Hyperf\GrpcServer\CoreMiddleware::process()( `/grpc-server/src/CoreMiddleware.php , process()函数` )
        > 
        > 流程如下: 
        >
        > 1.解析`request_uri`,匹配服务
        >
        > 2.然后调用解码类,对请求参数解码,得到明文信息
        >
        > 3.最后执行请求的服务
