# UnlimitedSocks
## A ShadowSocksR Seller Plugin|一个SS/SSR销售插件

* Panel 80%Done|面板 80%完成
* added support for product control|增加对产品管理的支持
* fixed QRcode support for ss&ssr|已修复SS和SSR二维码支持
* added traffic chart support|添加用户流量图表
* fixed utf-8 error|修复中文备注乱码问题
* Rewrite band reset functon|重写流量清空定时任务
* Added bandreset when unsuspend account|新增账户解除暂停时自动清空流量
* Added Announcements Support|加入公告信息支持
* Added Additional Bandwidth System|已加入额外流量包系统
* Added Support for SS&SSR compatible mode|加入SS&SSR兼容模式支持
* Fixed a bug which caused by short_arg|修复了一个由于short_arg导致的bug
* Added Band reset mode(start of month,end of month,calc by duedate)|新增流量重置模式(每月开始，每月底，由结算日计算)
* Please Refresh CDN and Empty Template Cache in Utilities->System->System Cleanup|请刷新CDN并且在其他选项->系统相关->系统清理中清除模板缓存
* To Old Users:you may need to deactivate and reactivate your module in order for WHMCS to recognise it.|你可能需要重新刷新插件(去产品/服务中找个产品不改配置直接保存即可)来让WHMCS识别

## TO-DO List
* ~~Readd custom traffic module|重新添加自定义流量系统~~
* ~~Rewrite traffic chart for mobile suppor|重写手机端流量图表支持~~
* ~~Add multi-language support|添加多语言支持~~
* ~~Add SS/SSR switch function|添加SS/SSR切换支持~~
* ~~Add Ping Test switch function|添加PING测试选项切换支持~~
* ~~Add Random Password Support|添加随机密码支持~~
* ~~Add additional bandwidth|添加额外流量包功能~~
* ~~Fix The Bug of APIBandreset|修正APIBandreset的时间bug~~
* Improve admin panel|改善管理员面板

## Attentions
* To use MysqlBandReset,You MUST edit the database info in MysqlBandReset.php|如果要使用MysqlBandReset，你必须在MysqlBandReset.php中配置数据库信息
* You must add a server and select it in Product->Module Settings|请添加服务器并在产品->模块设置中选择服务器
* Please RESET the product AFTER you update the plugin and CHECK every thing is right|请在更新插件后确认全部的产品和服务器配置正确
* Database is the same as before|数据库兼容旧版
* RouteList has changed,Please check|路线列表进行了更新，请检查然后更正
* Because of removed 'AccessHash',configoptions has some small changes so please RESET the Prooduct->ModuleSettings|因为移除了访问哈希，请重新配置产品->模块设置
* Please upload user_usage.sql if you need traffic chart function|如果你需要流量图表功能，请上传user_usage.sql
* Random Password means you can leave blank in Custom Strings|随机密码的意思是你可以在购买时不填写自定义密码(避免眼滑用户看不见自定义密码选项)
* To use APIBandReset,You MUST Add API IP in System->General->security|如果要使用APIBandReset，你必须在System->General->security中添加访问API的ip
* For More support,You can open an issue or email me|需要更多支持，请开issue或者给我发邮件

## Other
# More Info in|更多详情请访问 http://www.mak-blog.com/whmcs-shadowsocks-plugin.html
# 或者 http://www.loli.ren/index.php/2017/07/24/unlimitedsocks%E5%AE%89%E8%A3%85%E6%96%B9%E6%B3%95/

# Paypal Donate|Paypal捐赠 zzm317@outlook.com
# Alipay Donate|支付宝捐赠 admin@fdtmc.tk

* BTW I think a developer's most important ability is to obey the Open-Source Rules
* Not the coding level he can achieve
