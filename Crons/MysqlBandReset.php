<?
define('DB_NAME', '');//数据库名字
define('DB_USER', '');//数据库用户名
define('DB_PASS', '');//数据库密码
define('DB_HOST', 'localhost');//数据库IP/域名

define('WHMCS_DB_NAME', '');//WHMCS数据库名字
define('WHMCS_DB_USER', '');//WHMCS数据库用户名
define('WHMCS_DB_PASS', '');//WHMCS数据库密码
define('WHMCS_DB_HOST', 'localhost');//WHMCS数据库IP/域名

function resetband($id){
	$ssmysql = new mysqli(DB_HOST, DB_USER, DB_PASS , DB_NAME);
	if(!$ssmysql) {
        die('Unable to connect to database.');
	}else{
        $ssmysql->query("UPDATE `user` SET `u` = '0', `d` = '0' where `sid` = ".$id);
        $ssmysql->query("delete from `user_usage` WHERE `sid` = ".$id);
        echo("ID:".$id." Has been reset</br>");
	}
}

function daysInmonth($year='',$month=''){  
    if(empty($year)) $year = date('Y');  
    if(empty($month)) $month = date('m');  
    if (in_array($month, array(1, 3, 5, 7, 8, '01', '03', '05', '07', '08', 10, 12))) {    
            $text = '31';        //月大  
    }elseif ($month == 2 || $month == '02'){    
        if ( ($year % 400 == 0) || ( ($year % 4 == 0) && ($year % 100 !== 0) ) ) {   //判断是否是闰年    
            $text = '29';        //闰年2月  
        } else {    
            $text = '28';        //平年2月  
        }    
    } else {    
        $text = '30';            //月小  
    }  
      
    return $text;  
}  

function calcreset($product,$whmcs,$day){
    switch($product['need_reset']){
        case 0:
            break;
        case 1:
            if(date("d", strtotime($whmcs['nextduedate'])) == date('d')){
                resetband($product['sid']);
            }
            if(date('d') == $day){
                if(date("d", strtotime($whmcs['nextduedate'])) > $day){
                    resetband($product['sid']);
                } 
            }
            break;
        case 2:
            if(date('d') == 1){
                resetband($product['sid']);
            }
            break;
        case 3:
            if(date('d') == $day){
                resetband($product['sid']);
            }
            break;
    }
}

$mysql = new mysqli(WHMCS_DB_HOST, WHMCS_DB_USER, WHMCS_DB_PASS , WHMCS_DB_NAME);
if(!$mysql) {
    die("Can't connect to WHMCS Database");
}else{
      $produ = array();
      $sql = "SELECT * FROM `tblhosting` WHERE `domainstatus` = 'Active'";
      $whmcspro = mysqli_fetch_all($mysql->query($sql),MYSQLI_ASSOC);
      $mysql->close();
      if($whmcspro){
          foreach($whmcspro as $whmcs){
              $produ[$whmcs['id']] = $whmcs;
          }
      }else{
          die("Nothing Active In tblhosting in WHMCS Database");
      }
      $ssmysql = new mysqli(DB_HOST, DB_USER, DB_PASS , DB_NAME);
      $sql = "SELECT * FROM `user` WHERE `enable` = 1 order by `sid`";
      $ssacc = mysqli_fetch_all($ssmysql->query($sql),MYSQLI_ASSOC);
      $ssmysql->close();
      $days = daysInmonth(date('y'),date('m'));
      if($ssacc){
          foreach($ssacc as $ssa){
              $pro = $produ[$ssa['sid']];
              calcreset($ssa,$pro,$days);
          }
      }else{
          die("Nothing isset in SS/SSR's User Table(users)");
      }
      echo("Reset Done");
}
