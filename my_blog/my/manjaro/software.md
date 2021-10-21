# manjaro常用软件安装

目录
> 1) [准备工作](#准备工作)
> 2) [安装搜狗输入法](#安装搜狗输入法)
> 3) [安装wps](#安装wps)
> 4) [安装v2ray(科学上网)](#安装v2ray(科学上网))
> 5) [安装VScode](#安装VScode)
> 6) [安装网易云音乐](#安装网易云音乐)
> 7) [安装有道词典](#安装有道词典)
> 8) [安装Docker](#安装Docker)
> <br><br>
> [问题](#问题)<br>
> [参考](#参考)

--- 

- ## 准备工作
    1) 切换软件源
        ```
        # 在弹出的窗口中选择想要的镜像源。
        #（结果会自动导入/etc/pacman.d/mirrorlist 配置文件中）
        sudo pacman-mirrors -i -c China -m rank
        ```
    2) 增加archlinuxcn软件仓库<br>
        > Arch Linux中文社区仓库是由Arch Linux中文社区驱动的非官方用户仓库。<br>
        > 包含中文用户常用软件、工具、字体/美化包等。
        ```
        echo -e "\n[archlinuxcn]\nSigLevel = TrustAll\nServer = https://mirrors.tuna.tsinghua.edu.cn/archlinuxcn/\$arch\n" | sudo tee -a /etc/pacman.conf
        ```
        或手动修改 `/etc/pacman.conf` 文件, 增加如下内容:
        ```
        [archlinuxcn]
        SigLevel = TrustAll
        Server = https://mirrors.tuna.tsinghua.edu.cn/archlinuxcn/$arch
        ```
    3) 更新软件包数据库
        ```
        sudo pacman -Syy
        ```
    4) 升级所有已安装软件包
        ```
        sudo pacman -Syyu
        ```
    5) 安装archlinuxcn-keyring
        ```
        sudo pacman -S --noconfirm archlinuxcn-keyring
        ```
    6) 安装AUR助手yay
        > `AUR?`<br>
        > AUR是Arch Linux/Manjaro用户的社区驱动存储库，创建AUR的目的是使共享社区包的过程更容易和有条理，它包含包描述（PKGBUILDs），允许使用makepkg从源代码编译包，然后通过pacman安装它。<br>
        > `yay?`<br>
        > Yay是用Go编写的Arch Linux AUR帮助工具，它可以帮助你以自动方式从PKGBUILD安装软件包

        ```
        sudo pacman -S yay
        ```
    7) 安装base-devel 和 binutils, 不装这俩, 后面安装软件包会报错
        ```
        sudo pacman -S base-devel binutils
        ```

- ## 安装搜狗输入法
    ```
    # 以下四步是fcitx和搜狗拼音的配置
    sudo pacman -Sy fcitx
    sudo pacman -Sy fcitx-configtool
    yay fcitx-sogoupinyin
    yay fcitx-qt4

    sudo echo -e "export GTK_IM_MODULE=fcitx\nexport QT_IM_MODULE=fcitx\nexport XMODIFIERS=@im=fcitx" >> ~/.xprofile
    ```
    > 完成上面的步骤后, 重启电脑, 搜狗输入法就可以使用了

- ## 安装wps
    ```
    #安装中文版的wps
    yay -S wps-office-cn wps-office-mime-cn wps-office-mui-zh-cn
    #安装wps的字体
    yay -S ttf-wps-fonts
    ```

- ## 安装v2ray(科学上网)
    > 参考: https://www.buptstu.cn/2021/02/07/Manjaro%E9%85%8D%E7%BD%AEQv2ray-SSR/
    - 1) 安装 `v2ray Core`
        ```
        yay -S v2ray
        ```
    - 2) 安装图形化工具 `Qv2ray`
        > Qv2ray官方github仓库：https://github.com/Qv2ray/Qv2ray<br>
        > 官方网站：https://qv2ray.net/<br>
        - 下载
        ```
        wget https://github.com/Qv2ray/Qv2ray/releases/download/v2.7.0/Qv2ray-v2.7.0-linux-x64.AppImage
        ```
        - 赋予可执行权限
        ```
        chmod +x Qv2ray-v2.7.0-linux-x64.AppImage
        ```
        - 直接运行
        ```
        ./Qv2ray-v2.7.0-linux-x64.AppImage
        ```
        - 修改配置
            - 打开 `首选项 > 内核设置`
            ```
            # 修改配置项如下
            V2Ray 核心可执行文件路径:    /usr/bin/v2ray
            V2Ray 资源目录:             /usr/share/v2ray/
            ```
            - 打开 `分组 > 订阅设置`
                - 勾选 ✔ `此分组是一个订阅`
                - 输入`订阅地址`
                - 点击`更新订阅`(如果没有更新成功, 更换下`订阅类型`, 然后再试一下)

- ## 安装VScode
    ```
    yay -S visual-studio-code-bin
    ```
    > 打开vscode安装插件时, 如果有这样的报错: `Error while fetching extensions.XHR failed`<br>
    > 检查两个地方:
    > 1) 系统时间改成自动设置
    > 2) 关闭网络代理

- ## 安装网易云音乐
    ```
    yay -S netease-cloud-music
    ```

- ## 安装有道词典
    > 非常之好用, 比windows版本牛逼100倍~~
    ```
    yay -S youdao-dict
    ```

- ## 安装Docker
    ```
    # yay安装
    yay -S docker
    # 启动docker
    sudo systemctl start docker
    # 设置开机启动
    sudo systemctl enable docker
    ```

- ## 问题
    - gnome桌面默认不显示图标
        - 找到`gnome扩展`, 将 `Desktop Icons` 打开

- ## 参考
    - [Manjaro常用软件列表](https://blog.csdn.net/fan_xiao_hui/article/details/107809611)
    - [Manjaro配置Qv2ray&SSR](https://www.buptstu.cn/2021/02/07/Manjaro%E9%85%8D%E7%BD%AEQv2ray-SSR/)