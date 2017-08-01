# Usage|用法
* APIbandreset.php is used to reset the traffic with api|APIbandreset.php是用来根据到期时间的日(day)来重置流量的
* cron.php has been removed(Not useful)|cron.php已被移除(无用)
* ChartInfo.php is used to record the traffic|ChartInfo.php是用来记录使用流量的，可不配置。

## Suggested Usage|建议用法
0 0 * * * php -q /home/wwwroot/APIbandreset.php
1 */3 * * * php -q /home/wwwroot/ChartInfo.php