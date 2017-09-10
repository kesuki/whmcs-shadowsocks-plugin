<?php 
if( !defined('WHMCS') ){
    exit( "This file cannot be accessed directly" );
}
if( !function_exists('getCustomFields') ){
    require(ROOTDIR . "/includes/customfieldfunctions.php");
}
if( !function_exists('getCartConfigOptions') ){
    require(ROOTDIR . "/includes/configoptionsfunctions.php");
}
global $currency;
$currency = getCurrency();
$where = array(  );
if( $pid ){
    if( is_numeric($pid) ){
        $where[] = "id=" . (int) $pid;
    }else{
        $where[] = "id IN (" . db_escape_string($pid) . ")";
    }
}
if( $gid ){
    $where[] = "gid=" . (int) $gid;
}
if( $module ){
    $where[] = "servertype='" . db_escape_string($module) . "'";
}
$result = select_query('tblproducts', '', implode(" AND ", $where));
$apiresults = array( 'result' => 'success', 'totalresults' => mysql_num_rows($result) );
while( $data = mysql_fetch_array($result) ){
    $pid = $data['id'];
    $productarray = array( 'pid' => $data['id'], 'gid' => $data['gid'], 'type' => $data['type'], 'name' => $data['name'], 'description' => $data['description'], 'module' => $data['servertype'], 'paytype' => $data['paytype'] );
    if( $data['stockcontrol'] ){
        $productarray['stockcontrol'] = 'true';
        $productarray['stocklevel'] = $data['qty'];
    }
	$configoptiondata = array(  );
	$counter = 1;
	while( $counter <= 24 )
	{
		$configoptiondata[$counter] = isset($data['configoption' . $counter]) ? $data['configoption' . $counter] : null;
		$counter += 1;
	}
    $result2 = select_query('tblpricing', "tblcurrencies.code,tblcurrencies.prefix,tblcurrencies.suffix,tblpricing.msetupfee,tblpricing.qsetupfee,tblpricing.ssetupfee,tblpricing.asetupfee,tblpricing.bsetupfee,tblpricing.tsetupfee,tblpricing.monthly,tblpricing.quarterly,tblpricing.semiannually,tblpricing.annually,tblpricing.biennially,tblpricing.triennially", array( 'type' => 'product', 'relid' => $pid ), 'code', 'ASC', '', "tblcurrencies ON tblcurrencies.id=tblpricing.currency");
	while( $data = mysql_fetch_assoc($result2) ){
        $code = $data['code'];
        unset($data['code']);
        $productarray['pricing'][$code] = $data;
    }
    $customfieldsdata = array(  );
    $customfields = getCustomFields('product', $pid, '', '', 'on');
    foreach( $customfields as $field ){
        $customfieldsdata[] = array( 'id' => $field['id'], 'name' => $field['name'], 'description' => $field['description'], 'required' => $field['required'] );
    }
    $productarray['customfields']['customfield'] = $customfieldsdata;
    $productarray['configoptions']['configoption'] = $configoptiondata;
    $apiresults['products']['product'][] = $productarray;
}