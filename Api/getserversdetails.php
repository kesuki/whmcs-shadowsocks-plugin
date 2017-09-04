<?php
if( !defined('WHMCS') ){
    exit( "This file cannot be accessed directly" );
}
$result3 = select_query('tblservers', "DISTINCT type", '', 'type', 'ASC');
$servers = array();
while( $data = mysql_fetch_array($result3) )
{
	$servertype = $data['type'];
	$tabledata[] = array( 'dividingline', ucfirst($servertype) );
	$disableddata = array(  );
	$result = select_query('tblservers', '', array( 'type' => $data['type'] ), 'name', 'ASC');
	while( $data = mysql_fetch_array($result) )
	{
		$params = array();
		$params['id'] = $data['id'];
		$params['name'] = $data['name'];
		$params['serverip'] = $data['ipaddress'];
		$params['serverhostname'] = $data['hostname'];
		$params['serverusername'] = $data['username'];
		$params['serverpassword'] = decrypt($data['password']);
		$params['maxaccounts'] = $data['maxaccounts'];
		$params['active'] = $data['active'];
		$params['type'] = $data['type'];
		$params['disabled'] = $data['disabled'];
		$params['monthlycost'] = $data['monthlycost'] ? $data['monthlycost'] : "Unknown";
        $active = $data['active'] ? "*" : '';
		$result2 = select_query('tblhosting', "COUNT(*)", "server='" . $data['id'] . "' AND (domainstatus='Active' OR domainstatus='Suspended')");
		$datas = mysql_fetch_array($result2);
		$numaccounts = $datas[0];
		$percentuse = @round($numaccounts / $data['maxaccounts'] * 100, 0);
		$params['numaccounts'] = $numaccounts;
		$params['percentuse'] = $percentuse;
		$servers[$data['id']] = $params;
	}
}
$apiresults['result'] = 'success';
$apiresults['servers'] = $servers; 