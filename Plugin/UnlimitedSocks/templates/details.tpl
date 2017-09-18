<!-- CSS -->
<link rel="stylesheet" href="modules/servers/{$module}/templates/static/css/style.css">
<script src="modules/servers/{$module}/templates/static/js/Chart.js"></script>
<script src="modules/servers/{$module}/templates/static/js/qrcode.js"></script>
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
{if ($infos)}
	<div class="alert alert-success">
		<p>{$infos}</p>
	</div>
{/if}
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
						<li><a href="javascript:;"> <i class="fa fa-check-square-o"></i> {get_lang('data_update_at')} : {$nowdate} </a></li>
                    </ul>
                </section>
            </aside>
            <!--widget end-->
            <section class="panel">
                <header class="panel-heading">
                    {get_lang('user_info')}
                </header>
                <div class="panel-body table-container">
                    <table class="table general-table">
                        <thead>
                            <tr>
                                <th>{get_lang('port')}</th>
                                <th>{get_lang('password')}</th>
                                <th class="hidden-xs hidden-sm">{get_lang('created_at')}</th>
                                <th class="hidden-sm hidden-xs">{get_lang('last_use_time')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{$usage.port}</td>
                                <td>{$usage.passwd}</td>
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
                    {get_lang('usage_chart')} ({get_lang('bandwidth')}：{$usage.tr_MB_GB})
                </header>
                <div class="panel-body" id="plugin-usage">
                    <p>{get_lang('used')} ({$usage.s_MB_GB})</p>
                    <div class="progress progress-striped progress-sm">
                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{($usage.sum/$usage.transfer_enable)*100}" aria-valuemin="0" aria-valuemax="100" style="width: {($usage.sum/$usage.transfer_enable)*100}%">
                            <span class="sr-only">{($usage.sum/$usage.transfer_enable)*100}% Complete</span>
                        </div>
                    </div>
                    <p>{get_lang('upload')} ({$usage.u_MB_GB})</p>
                    <div class="progress progress-striped progress-sm">
                        <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="{($usage.u/$usage.transfer_enable)*100}" aria-valuemin="0" aria-valuemax="100" style="width: {($usage.u/$usage.transfer_enable)*100}%">
                            <span class="sr-only">{($usage.u/$usage.transfer_enable)*100}% Complete (warning)</span>
                        </div>
                    </div>
                    <p>{get_lang('download')} ({$usage.d_MB_GB})</p>
                    <div class="progress progress-striped progress-sm">
                        <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{($usage.d/$usage.transfer_enable)*100}" aria-valuemin="0" aria-valuemax="100" style="width: {($usage.d/$usage.transfer_enable)*100}%">
                            <span class="sr-only">{($usage.d/$usage.transfer_enable)*100}% Complete (danger)</span>
                        </div>
                    </div>
                </div>
            </section>
            
            <section class="panel">
                <header class="panel-heading">
                    {get_lang('routelist')}
                </header>
                <div class="panel-body table-container">
                    <table class="table table-hover general-table">
                        <thead>
                            <tr>
                                <th>{get_lang('name')}</th>
								<th>{get_lang('connect_type')}</th>
                                <th class="hidden-xs hidden-sm">{get_lang('address')}</th>
                                <th class="hidden-xs hidden-sm">{get_lang('method')}</th>
                                <th class="hidden-xs hidden-sm">{get_lang('protocol')}</th>
                                <th class="hidden-xs hidden-sm">{get_lang('obfuscation')}</th>
								{if ($pingoption != 0)}
									<th class="hidden-xs hidden-sm">{get_lang('test')}</th>
								{/if}
                                <th>{get_lang('action')}</th>
                            </tr>
                        </thead>
                        <tbody>
							{$yy = 0}
                            {foreach $nodes as $node }
                            <tr>
                                <td>{$node[0]}</td>
								<td>{$node[7]}</td>
                                <td class="hidden-xs hidden-sm">{$node[1]}</td>
                                <td class="hidden-xs hidden-sm">{$node[2]}</td>
                                <td class="hidden-xs hidden-sm">{$node[3]}</td>
                                <td class="hidden-xs hidden-sm">{$node[5]}</td>
								{if ($pingoption == 1)}
									<td class="hidden-xs hidden-sm">
										<button class="btn btn-primary btn-xs" >
											{$pings[$yy]}
										</button>
									</td>	
								{/if}
								{if ($pingoption == 2)}
									<td class="hidden-xs hidden-sm">
										<button name="ping" class="btn btn-primary btn-xs" >
											{get_lang('ping_test')}
										</button>
									</td>
								{/if}
                                <td data-hook="action">
                                    {if is_array($node[8])}
                                        <button name="qrcode" class="btn btn-primary btn-xs" data-type="SS(IOS,Mac)" data-params="{$node[8]['ss']}">
                                            <i class="fa fa-qrcode"></i>
                                            {get_lang('show_QRcode')}(SS(IOS,Mac))
                                        </button>
                                        <button name="qrcode" class="btn btn-primary btn-xs" data-type="SS(Android,Win)" data-params="{$node[8]['ss1']}">
                                            <i class="fa fa-qrcode"></i>
                                            {get_lang('show_QRcode')}(SS(Android,Win))
                                        </button>
                                        <button name="url" class="btn btn-primary btn-xs" data-params="{$node[8]['ss']}">
                                            <i class="fa fa-qrcode"></i>
                                            {get_lang('show_URL')}(SS)
                                        </button>
                                        <button name="qrcode" class="btn btn-primary btn-xs" data-type="SSR" data-params="{$node[8]['ssr']}">
                                            <i class="fa fa-qrcode"></i>
                                            {get_lang('show_QRcode')}(SSR)
                                        </button>
                                        <button name="url" class="btn btn-primary btn-xs" data-params="{$node[8]['ssr']}">
                                            <i class="fa fa-qrcode"></i>
                                            {get_lang('show_URL')}(SSR)
                                        </button>
                                    {else}
                                        <button name="qrcode" class="btn btn-primary btn-xs" data-type="{$node[7]} "data-params="{$node[8]}">
                                            <i class="fa fa-qrcode"></i>
                                            {get_lang('show_QRcode')}
                                        </button>
                                        <button name="url" class="btn btn-primary btn-xs" data-params="{$node[8]}">
                                            <i class="fa fa-qrcode"></i>
                                            {get_lang('show_URL')}
                                        </button>
                                    {/if}
                                    {$yy = $yy + 1}
                                </td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </section>
            
            {if ($usingcards)}
                <section class="panel">
                    <header class="panel-heading">
                        {get_lang('card_info')}
                    </header>
                    <div class="panel-body table-container">
                        <table class="table general-table">
                            <thead>
                                <tr>
                                    <th>{get_lang('bandwidth')}</th>
                                    <th>{get_lang('duedate')}</th>
                                    <th class="hidden-xs hidden-sm">{get_lang('card_number')}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach $usingcards as $usedcard}
                                <tr>
                                    <td>{$usedcard['traffic']}</td>
                                    <td>{$usedcard['duedate']}</td>
                                    <td class="hidden-xs hidden-sm">{$usedcard['card']}</td>
                                </tr>
                            {/foreach}    
                            </tbody>
                        </table>
                    </div>
                </section>
            {/if}
            
            {if ($usedcards)}
                <section class="panel">
                    <header class="panel-heading">
                        {get_lang('used_card_info')}
                    </header>
                    <div class="panel-body table-container">
                        <table class="table general-table">
                            <thead>
                                <tr>
                                    <th>{get_lang('bandwidth')}</th>
                                    <th>{get_lang('duedate')}</th>
                                    <th class="hidden-xs hidden-sm">{get_lang('card_number')}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach $usedcards as $usedcard}
                                <tr>
                                    <td>{$usedcard['traffic']}</td>
                                    <td>{$usedcard['duedate']}</td>
                                    <td class="hidden-xs hidden-sm">{$usedcard['card']}</td>
                                </tr>
                            {/foreach}    
                            </tbody>
                        </table>
                    </div>
                </section>
            {/if}
			
			{if ($script)}
			<section class="panel">
                <header class="panel-heading">
                    {get_lang('traffic_chart')} ({$datadays} {get_lang('days')})
                </header>
                <div class="panel-body" id="chart-usage">
					
					<div class="row clearfix">
						<div class="col-xs-12">
							<h3 class="block-title text-primary">{get_lang('all_traffic_chart')}</h3>
							<canvas id="totalc" ></canvas>
						</div>
						<div class="col-xs-12">
							<h3 class="block-title text-primary">{get_lang('upload_traffic_chart')}</h3>
							<canvas id="uploadc" ></canvas>
						</div>
						<div class="col-xs-12">
							<h3 class="block-title text-primary">{get_lang('download_traffic_chart')}</h3>
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
<script src="modules/servers/{$module}/templates/static/js/SSRscript.js" charset="utf-8"></script>