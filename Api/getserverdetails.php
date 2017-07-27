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
		$servers[$data['id']] = $data;
	}
}
$apiresults['products'] = $servers; 