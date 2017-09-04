# Usage|用法
* MysqlBandReset.php is used to reset the traffic with api|MysqlBandReset.php是用来根据到期时间的日(day)来重置流量的
* cron.php has been removed(Not useful)|cron.php已被移除(无用)
* ChartInfo.php is used to record the traffic|ChartInfo.php是用来记录使用流量的，可不配置。
* ResetCard.php is used to Disable the outdated Bandwidth|ResetCard.php是用来停用过期的额外流量包的，可不配置。

## Suggested Usage|建议用法
0 0 * * * php -q /home/wwwroot/MysqlBandReset.php
1 */3 * * * php -q /home/wwwroot/ChartInfo.php
0 0 * * * php -q /home/wwwroot/ResetCard.php