<!-- CSS -->
<link rel="stylesheet" href="modules/servers/{$module}/templates/static/css/style.css">
<script src="modules/servers/{$module}/templates/static/js/Chart.js"></script>
<style>
.table-container
{
width: 100%;
overflow-y: auto;
_overflow: auto;
margin: 0 0 1em;
}

.table-container::-webkit-scrollbar
{
-webkit-appearance: none;
width: 14px;
height: 14px;
}

.table-container::-webkit-scrollbar-thumb
{
border-radius: 8px;
border: 3px solid #fff;
background-color: rgba(0, 0, 0, .3);
}

.table-container-outer { position: relative; }

.table-container-fade
{
	position: absolute;
	right: 0;
	width: 30px;
	height: 100%;
	background-image: -webkit-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: -moz-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: -ms-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: -o-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: linear-gradient(0deg, rgba(255,255,255,.5), #fff);
}
</style>
<div class="plugin">
    <div class="row">
        <div class="col-md-12">
            <!--widget start-->
            <aside class="profile-nav alt hidden-xs">
                <section class="panel">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="javascript:;"> <i class="fa fa-calendar-check-o"></i> {$LANG.clientareahostingregdate} : {$regdate} </a></li>
                        <li><a href="javascript:;"> <i class="fa fa-list-alt"></i> {$LANG.orderproduct} : {$groupname} - {$product} </a></li>
                        <li><a href="javascript:;"> <i class="fa fa-money"></i> {$LANG.orderpaymentmethod} : {$paymentmethod} {$LANG.firstpaymentamount}({$firstpaymentamount}) - {$LANG.recurringamount}({$recurringamount})</a></li>
                        <li><a href="javascript:;"> <i class="fa fa-spinner"></i> {$LANG.clientareahostingnextduedate} : {$nextduedate} {$LANG.orderbillingcycle}({$billingcycle}) </a></li>
                        <li><a href="javascript:;"> <i class="fa fa-check-square-o"></i> {$LANG.clientareastatus} : {$status} </a></li>
						<li><a href="javascript:;"> <i class="fa fa-check-square-o"></i> 本次数据刷新时间 : {$nowdate} </a></li>
                        {foreach from=$productcustomfields item=customfield}
							{if ($customfield.rawvalue)}
								<li><a href="javascript:;"> <i class="fa fa-file-text"></i> {$customfield.name} : {$customfield.rawvalue} </a></li>
							{/if}
						{/foreach}
                    </ul>
                </section>
            </aside>
            <!--widget end-->
            <section class="panel">
                <header class="panel-heading">
                    用户信息
                </header>
                <div class="panel-body table-container">
                    <table class="table general-table">
                        <thead>
                            <tr>
                                <th>端口</th>
                                <th>密码</th>
                                <th>协议</th>
                                <th>混淆</th>
                                <th class="hidden-xs hidden-sm">创建时间</th>
                                <th class="hidden-sm hidden-xs">上次使用</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{$usage.port}</td>
                                <td>{$usage.passwd}</td>
                                <td>{$nodes[0][3]}</td>
                                <td>{$nodes[0][5]}</td>
                                <td class="hidden-xs hidden-sm">{$usage.created_at|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                                <td class="hidden-sm hidden-xs">{$usage.t|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <!--progress bar start-->
            <section class="panel">
                <header class="panel-heading">
                    使用报表 (总流量：{$usage.transfer_enable/1048576} MB)
                </header>
                <div class="panel-body" id="plugin-usage">
                    <p>已用流量 ({($usage.sum/1048576)|round} MB)</p>
                    <div class="progress progress-striped progress-sm">
                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{($usage.sum/$usage.transfer_enable)*100}" aria-valuemin="0" aria-valuemax="100" style="width: {($usage.sum/$usage.transfer_enable)*100}%">
                            <span class="sr-only">{($usage.sum/$usage.transfer_enable)*100}% Complete</span>
                        </div>
                    </div>
                    <p>上传流量 ({($usage.u/1048576)|round} MB)</p>
                    <div class="progress progress-striped progress-sm">
                        <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="{($usage.u/$usage.transfer_enable)*100}" aria-valuemin="0" aria-valuemax="100" style="width: {($usage.u/$usage.transfer_enable)*100}%">
                            <span class="sr-only">{($usage.u/$usage.transfer_enable)*100}% Complete (warning)</span>
                        </div>
                    </div>
                    <p>下载流量 ({($usage.d/1048576)|round} MB)</p>
                    <div class="progress progress-striped progress-sm">
                        <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{($usage.d/$usage.transfer_enable)*100}" aria-valuemin="0" aria-valuemax="100" style="width: {($usage.d/$usage.transfer_enable)*100}%">
                            <span class="sr-only">{($usage.d/$usage.transfer_enable)*100}% Complete (danger)</span>
                        </div>
                    </div>
                </div>
            </section>
            
            <section class="panel">
                <header class="panel-heading">
                    节点列表
                </header>
                <div class="panel-body table-container">
                    <table class="table table-hover general-table">
                        <thead>
                            <tr>
                                <th>描述</th>
								<th class="hidden-xs hidden-sm">连接方式</th>
                                <th>地址</th>
                                <th>加密</th>
								<th class="hidden-xs hidden-sm">测试</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $nodes as $node }
                            <tr>
                                <td>{$node[0]}</td>
								<td class="hidden-xs hidden-sm">{$node[7]}</td>
                                <td>{$node[1]}</td>
                                <td>{$node[2]}</td>
								<td class="hidden-xs hidden-sm">
									<button name="ping" class="btn btn-primary btn-xs" >
                                        Ping测试
                                    </button></td>
                                <td data-hook="action">
                                    <button name="qrcode" class="btn btn-primary btn-xs" data-type="{$node[7]}" data-params="{$node[1]}:{$usage.port}:{$node[3]}:{$node[2]}:{$node[5]}:" data-params-SS="{$node[2]}:{$usage.passwd}@{$node[1]}:{$usage.port}" data-pass="{$usage.passwd}" data-obfsparam="{$node[4]}" data-protoparam="{$node[6]}" data-note="{$node[0]}">
                                        <i class="fa fa-qrcode"></i>
                                        查看二维码
                                    </button>
									<button name="url" class="btn btn-primary btn-xs" data-type="{$node[7]}" data-params="{$node[1]|trim}:{$usage.port}:{$nodes[0][3]|trim}:{$node[2]|trim}:{$nodes[0][5]|trim}:" data-pass="{$usage.passwd}" data-params-SS="{$node[2]}:{$usage.passwd}@{$node[1]}:{$usage.port}" data-obfsparam="{$node[4]|trim}"
									data-protoparam="{$node[6]|trim}" data-note="{$node[0]|trim}">
                                        <i class="fa fa-qrcode"></i>
                                        查看URL
                                    </button>
									
                                </td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </section>
			
			{if ($script)}
			<section class="panel">
                <header class="panel-heading">
                    图形报表 (显示 {$datadays} 天数据)
                </header>
                <div class="panel-body" id="chart-usage">
					
					<div class="row clearfix">
						<div class="col-xs-12">
							<h3 class="block-title text-primary">总流量使用图</h3>
							<canvas id="totalc" ></canvas>
						</div>
						<div class="col-xs-12">
							<h3 class="block-title text-primary">上传流量使用图</h3>
							<canvas id="uploadc" ></canvas>
						</div>
						<div class="col-xs-12">
							<h3 class="block-title text-primary">下载流量使用图</h3>
							<canvas id="downloadc" ></canvas>
						</div>
					</div>
				
				
					<script src="/assets/js/bootstrap-tabdrop.js"></script>
					<script type="text/javascript">
						{$script}
					</script>
                </div>
            </section>
			{/if}
			
        </div>
    </div>
</div>
<!-- JavsScript -->
<script src="modules/servers/{$module}/templates/static/layer.js"></script>
<script src="modules/servers/{$module}/templates/static/js/5SSRscript.js"></script>