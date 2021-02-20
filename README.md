# Gogs Web Hook
> 接收`gogs`的`push`推送，自动更新项目到最新节点。

## 部署配置说明
> eg:

- 推送地址设置
```
http://xxx:888/?r=/home/www/item/path&b=master
```
**参数说明：**

| 参数名称 | 说明作用 |
| :------------ | :------------ |
| `r`  | 项目在服务器里面的部署路径 |
| `b`  | 仓库分支，默认为`master` |

- 密钥设置需要和`web钩子`的秘钥文本保持一致，
此项目的`.example.env`有示例。

- 配置文件去除前缀，命名为`.env`就可配置完毕。

**温馨提示：**
需要手动配置www用户权限
```
# su www
error: This account is currently not available.
# usermod -s /bin/bash www
# su www
$ ssh-keygen -t rsa -b 4096 -C "your_email@example.com" 
$ less ~/.ssh/id_rsa.pub
...
```

## 感谢您的使用，祝您生活愉快！
