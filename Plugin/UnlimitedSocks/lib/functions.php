<?

function multi_language_support(){
	global $_LANG;
	$dir = realpath(dirname(dirname(__FILE__)) . "/lang");
	if(isset($GLOBALS['CONFIG']['Language']) ){
		$language = $GLOBALS['CONFIG']['Language'];
	}
	if(isset($_SESSION['adminid'])){
		$language = _getUserLanguage('tbladmins', 'adminid');
	}elseif( $_SESSION['uid'] ){
		$language = _getUserLanguage('tblclients', 'uid');
	}
	if(!$language){
		$language = Default_Lang;
	}
	$file = $dir.'/'.$language.".php";
	if(file_exists($file)){
		include($file);
	}else{
        $file = $dir.'/'.Default_Lang.'.php';
        include($file);
    }
	return $file;
}

function _getUserLanguage($table, $field){
    try{
        $sqlresult = select_query($table, 'language', array( 'id' => mysql_real_escape_string($_SESSION[$field])));
        if($data = mysql_fetch_row($sqlresult)){
            return reset($data);
        }
        return false;
    }catch(Exception $e){
        logModuleCall('UnlimitedSocks', 'UnlimitedSocks_MultiLanguageSupport', $field, $e->getMessage(), $e->getTraceAsString());
        return false;
    }
}

function get_lang($var){
	global $_LANG;
	return isset($_LANG[$var]) ? $_LANG[$var] : $var . '(Missing Language)' ;
}
