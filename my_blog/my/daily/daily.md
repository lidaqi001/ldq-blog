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