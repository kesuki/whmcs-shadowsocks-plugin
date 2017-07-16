# Usage|用法
* cron.php is used to reset all the traffic(Used to reset bandwidth)|cron.php是用来清除使用流量的(搭配重置流量功能)
* ChartInfo.php is used to record the traffic|ChartInfo.php是用来记录使用流量的，可不配置。

## Suggested Usage|建议用法
0 0 1 * * php -q /home/wwwroot/cron.php
1 */3 * * * php -q /home/wwwroot/ChartInfo.php