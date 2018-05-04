# UnlimitedSocks
## 一个WHMCS的SS/SSR销售插件
* 当前版本 2.1.0Beta5

## 支持的功能：
* 流量图表
* 流量重置(每月开始，每月底，由结算日计算)
* 额外流量包
* 公告信息
* 服务器ping测试
* SS与SSR二维码
* 随机密码
* SSR订阅
* 管理面板(基本完成，想到了其他的再更新)

## 管理面板功能
* 修改产品描述以及公告
* 查看用户流量使用情况
* 暂停/取消暂停客户的服务
* 重置客户流量
* 重置所有端口(慎用)

## 开发中的功能  
* 管理面板 95%

## 注意事项
* 如果要使用MysqlBandReset，你必须在MysqlBandReset.php中配置数据库信息
* 请添加服务器并在产品->模块设置中选择服务器
* 请在更新插件后确认全部的产品和服务器配置正确
* 数据库兼容旧版
* 路线列表进行了更新，请检查然后更正
* 因为移除了访问哈希，请重新配置产品->模块设置
* 如果你需要流量图表功能，请上传user_usage.sql
* 随机密码的意思是你可以在购买时不填写自定义密码(避免眼滑用户看不见自定义密码选项)
* 如果要使用APIBandReset，你必须在System->General->security中添加访问API的ip
* 本插件已移除对以前的API的需求。
* 需要更多支持，请开issue或者给我发邮件

## 其他
* 更多详情请访问 [whmcs模块的shadowsocks插件](http://www.mak-blog.com/whmcs-shadowsocks-plugin.html)
* 或者 [UnlimitedSocks安装教程](https://www.loli.ren/archives/48/)

## 如果感觉插件好用，嘘寒问暖不如打笔巨款～
* Paypal捐赠 zzm317@outlook.com
* 支付宝捐赠 admin@loli.ren
