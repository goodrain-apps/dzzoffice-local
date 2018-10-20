| 应用名称 | DzzOffice                                          |
| -------- | -------------------------------------------------- |
| 简述     | Dzzoffice是一套开源办公套件，企业协同办公平台      |
| 版本列表 | V2.0                                               |
| 源码地址 | <https://github.com/goodrain-apps/dzzoffice-local> |
| 社区地址 | http://t.goodrain.com/t/dzzoffice/496              |

## 关于DzzOffice

   Dzzoffice是一套开源办公套件，适用于企业、团队搭建自己的 类似“Google企业应用套件”、“微软Office365”的企业协同办公平台。套件由多个工具组成，包含但不限于如：

**网盘**: 企业、团队文件集中管理。主要体现的功能是支持企业部门的组织架构建立共享目录，也支持组的方式灵活建立共享目录。支持文件标签，多版本，评论，详细的目录权限等协作功能。

**文档**: 在线 Word 文档协作工具。前端做了一套模板管理，用于企业添加自己的常用文档模板，如空白合同。后端支持 office online server，onlyoffice，collaboraoffice 来实现文档预览与协同编辑。

**表格**: 在线 Excel 协作工具。同上

**演示文稿**: 在线 PPT 文档浏览、编辑工具。同上

**记录**: 多人参与协作的记录本，主要体现协作记录内容。

**新闻**: 文章系统，可用于企业新闻，通知等用途

**通讯录**: 企业人员联系方式查询

**文集**: 通过树形目录有序管理文档。支持 Markdown 编辑，支持导入导出 txt，epub、mobi、azw3

**相册**： 企业，团队图片管理

**任务板**: 任务管理、团队协作

**讨论板**: 内部论坛设置

**表单**: 表单，问卷工具

企业根据需要可以只使用一款工具，也可以多款工具组合使用。例如团队需要一个任务管理工具，可以只安装一个任务板，登陆系统会直接进入任务板工具，没有其他工具的干扰。如果多个工具组合使用，可以设置默认登陆到哪个工具里。

除了以上自己开发了一些工具，套件里还集成了大量的其他开源工具，如网盘里用到的在线压缩、解压，各类媒体文件预览，各类文档预览与编辑的支持，是各类开源程序的综合利用。

官网地址：<http://dzzoffice.com/index.html>

演示地址：<http://demo.dzzoffice.com/>

开源地址：<https://github.com/zyx0814/dzzoffice>



## 关于配置

**Mysql环境变量配置：**

修改环境变量`MYSQL_ROOT_PASSWORD`来修改root用户密码

**Collabora环境变量配置：**

`domain`允许请求的域名，这里需要填写你dzzoffice对外访问地址的域名，`.`需要用两个`\`来转义。例如：

```
5000\\.gr20c7b4\\.zcvts804\\.ali-sh\\.goodrain\\.net
```



## 关于使用

如果你需要多人在线编辑文档，则需要一个后端支持 office online server，onlyoffice，collaboraoffice 来实现文档预览与协同编辑。我已经默认安装了一个collaboraoffice，下面说一下如何配置。

1. 首先进入dzzoffice，根据页面引导完成初始化和管理员账户设置。
2. 登陆管理员账号，进入管理页面点击应用市场
3. 在应用市场一件安装`collabora office`，然后在`已安装`一栏中找到它
4. 点击`设置`把``collabora`应用tcp端口的对外地址填写到这里，例如`http://ali-sh-s1.goodrain.net:21164/`
5. 点击`开启`，开启`collabora office`，在`打开方式`一栏中将需要文档类型的打开方式设置为`collabora office`即可。
6. 设置完成后就可以多人在线打开编辑文档了。

> 关于DzzOffice的具体使用方法，请参考官方文档和演示地址，欢迎使用分享本应用。



## 关于制作过程中遇到的问题

* 关于部署时的问题

  因为是PHP项目，可以直接在云帮源码创建应用而且比较简单随意选择源码部署，首次创建时代码检测成功但是应用部署失败，查看报错信息提示缺少一个识别文件，于是去文档中找PHP源码部署的[文档](https://www.rainbond.com/docs/stable/user-manual/language-support/php.html)，原来是PHP版本的问题，按照文档提示在代码的根目录创建`composer.json`文件，指定使用`PHP `版本，再次构建应用，Dzzoffice成功跑了起来！

* 关于持久化的问题

  愉快的进入dzzoffice，经过漫长的引导安装、初始化数据库、创建管理员账号等一系列操作后进入平台，在应用市场安装了很多应用，开启并配置好。但是有一次重启应用之后再次进入dzzoffice发现又回到最初的页面，又开始提示安装初始化等等，原来费了半天力气安装的应用全都没有了。由于没有挂载存储，导致每次重启或重新部署应用原来的数据及状态全部丢失。

  在翻阅了dzzoffice的开发手册后发现，dzzoffice在平台安装使用后会在`/config`和`/data`目录下生成相应的文件来标示是否已经完成安装和存储相应的配置应用模版等。于是尝试给dzzoffice应用添加存储，将容器内`/app/config`和`/app/data`两个目录设置为`共享存储`，然后重启应用，重启应用失败！查看应用日志发现提示对这该文件夹内文件操作时遇到了没有权限的问题。后来经过咨询和查找问题得出结论，挂载的目录原来不是空的，目录内有文件导致挂载持久化一个非空的目录引发的问题。于是想方法来成功挂载持久化这两个目录。

  经过设计我的方法是先挂载持久化目录，挂载成功后再将原来的文件移动到已持久化的目录中。首先将原来的`/config`和`/data`目录重名为`/config2`和`/data2`，写了一个启动脚本`docker-entrypoint.sh`如下：

  ```
  #!/bin/bash
  
  DIRECTORY="/app/data"
  if [ "`ls -A $DIRECTORY`" = "" ]; then
   mv /app/data2/* /app/data
  fi
  
  CONFIG="/app/config"
  if [ "`ls -A $CONFIG`" = "" ]; then
   mv /app/config2/* /app/config
  fi
  echo "complete init data and config"
  sleep $PAUSE
  exec vendor/bin/heroku-php-apache2
  ```

  脚本的大概内容是判断这两个目录是否为空，如果为空则将`/config2`和`/data2`中的数据分别再移动回`/config`和`/data`中去。然后执行启动命令将代码跑起来。

  将**Procfile**文件中定义的启动命令修改为脚本`docker-entrypoint.sh`。这样应用每次启动时都会先执行一下该脚本，创建应用时就把`/config`和`/data`目录挂载持久化，由于把源码中的这两个目录修改成了`/config2`和`/data2`，找不到这两个目录系统就会自动创建这两个空目录并挂载持久化，这时挂载持久化目录已经成功，应用启动时执行脚本，将原来目录中项目所需的数据再移动回`/config`和`/data`目录，项目正常运行并可以持久化数据，再次重启dzzoffice，发现问题解决了。

* 关于Collabora Office安装中遇到的问题

  Collabora应用对外端口的协议是`TCP`而不是`HTTP`

  如果使用Collabora打开文档时提示`主机的访问被拒绝`·，那是由于没有在Collabora应用的配置中将dzzoffice的域名添加到允许规则中。在Collabora应用将环境变量`domain`修改为dzzoffice的域名即可。具体Collabora应用及部署相关的问题可以参考[官方文档](https://www.collaboraoffice.com/code/)。

* 欢迎大家在云市下载分享我的应用哦～ 

