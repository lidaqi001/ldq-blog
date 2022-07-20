# linux设置虚拟内存

## 首次设置

- 一、创建swap文件
    - 进入/usr目录
    - 创建swap文件夹,并进入该文件夹
      ```shell
      $ cd /usr/
      ```
    - 创建swapfile文件,并执行

        ```shell
        // 设置4G虚拟内存
        $ dd if=/dev/zero of=/usr/swap/swapfile bs=1M count=4096
        ```

- 二、查看swap文件
    - 执行命令
        ```shell
        $ du -sh /usr/swap/swapfile
      
        4G	/usr/swap/swapfile
        ```

  可以看到我们创建的这个swap文件为4g

- 三、将目标设置为swap分区文件
    - 执行命令
        ```shell
        $ mkswap /usr/swap/swapfile
        ```
      将swapfile文件设置为swap分区文件

- 四、激活swap区，并立即启用交换区文件
    - 执行命令
        ```shell
        $ swapon /usr/swap/swapfile
        ```
      激活swap区，并立即启用交换区文件

    - free -m 来查看现在的内存

      可以看到里面的Swap分区变成了4095M，也就是4G内存
        ```shell
                      total        used        free      shared  buff/cache   available
        Mem:           7678        7199         127          28         351         132
        Swap:          4095           0        4095
        ```

- 五、设置开机自动启用虚拟内存
    - 编辑/etc/fstab文件
         ```shell
         vim /etc/fstab
         ```

    - 然后在文件中添加以下内容：
        ```text
        /usr/swap/swapfile swap swap defaults 0 0
        ```

- 六、重启服务器，查看现在的内存是否挂在上

  ```shell
  reboot
  free -m
  ```

## 修改配置

```shell
// 1-关闭swap
$ swapoff /usr/swap/swapfile
// 2-重新设置swap分区大小
$ dd if=/dev/zero of=/usr/swap/swapfile bs=1M count=8192
// 3-将目标设置为swap分区文件
$ mkswap /usr/swap/swapfile
// 4-激活swap
$ swapon /usr/swap/swapfile
```