<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
	
function UnlimitedSocksCardGenerator_config(){
    $configarray = array(
    "name" => "UnlimitedSocksCardGenerator",
    "description" => 'UnlimitedSocks Card Generator',
    "version" => "1.1",
    "author" => "Zzm317",
    "language" => "english",
    "fields" => array(
        "option1" => array ("FriendlyName" => "DB_Host", "Type" => "text", "Size" => "25",
                              "Description" => "Database Host", "Default" => "None", ),
        "option2" => array ("FriendlyName" => "DB_Name", "Type" => "text", "Size" => "25",
                              "Description" => "Database Name", "Default" => "None", ),
        "option3" => array ("FriendlyName" => "DB_User", "Type" => "text", "Size" => "25",
                              "Description" => "Database User", "Default" => "None", ),
        "option4" => array ("FriendlyName" => "DB_Pass", "Type" => "text", "Size" => "25",
                              "Description" => "Database Password", "Default" => "None", )                      
    ));
    return $configarray;
}

function UnlimitedSocksCardGenerator_activate() {
    return array('status'=>'success');
}

function UnlimitedSocksCardGenerator_deactivate() {
    return array('status'=>'success');
}
    
function UnlimitedSocksCardGenerator_output($vars) {
    $LANG = $vars['_lang'];
    $version = $vars['version'];
    $host = $vars['option1'];
    $db = $vars['option2'];
    $user = $vars['option3'];
    $pass = $vars['option4'];
    $cards = get_cards($host,$db,$user,$pass);
    if(is_numeric($_REQUEST['traffic']) and is_numeric($_REQUEST['amount'])){
        if($_REQUEST['amount'] > 0 and $_REQUEST['traffic'] > 0){
            $x = 0;
            $cardarray = parse_card($cards);
            $id = get_id($host,$db,$user,$pass);
            $availabletime = $_REQUEST['availabletime'];
            if(!is_numeric($availabletime)){
               $availabletime = 30; 
            }
            $mysqly = "INSERT INTO `cards`(`cardid`, `number`, `traffic`, `cardstatus`, `availabletime`) VALUES";
            while($x < $_REQUEST['amount']){
                $card = RandomPass($length = 15);
                if(!in_array($card,$cardarray)){
                    $id ++;
                    $mysql = "('".$id."','".$card."','".$_REQUEST['traffic']."','1','".$availabletime."') ,";
                    $mysqly .= $mysql;
                    $x ++;
                }
            }
            $mysqly = substr($mysqly,0,strlen($mysqly)-1);
            try {
                $dbb = new PDO('mysql:host=' . $host . ';dbname=' . $db, $user, $pass);
                $usage = $dbb->query($mysqly);
            } catch (PDOException $e) {
                die ("Error!: " . $e->getMessage() . "<br/>");
            }
            echo('<div class="alert alert-success">
                    <p>'.$LANG['create_success'].'</p>
                </div>');
            $cards = get_cards($host,$db,$user,$pass);    
        }else{
           echo('<div class="alert alert-danger">
                    <p>'.$LANG['create_failed'].'</p>
                </div>'); 
        }
    }
    ?>
    <form method="post" action="">
        <h3 class="block-title text-primary"><?echo $LANG['create_card']?></h3>
        <input type="text" class="form-control" name="traffic" /required>(<?echo $LANG['create_card_traffic']?>)</br>
        <input type="text" class="form-control" name="amount" /required>(<?echo $LANG['create_card_amount']?>)</br>
        <input type="text" class="form-control" name="availabletime" />(<?echo $LANG['card_available_time']?>)
        <input class="form-control" type="submit"/>
    </form>
    </hr>
    <?
    if(empty($cards)){
        echo('<p>'.$LANG['no_card'].'</p>');
    }else{
        $traffic = "";
        $time = "";
        foreach($cards as $card){
            if($traffic == $card['traffic'] and $time == $card['availabletime']){
                echo '<p>'.$card['number'].'</p>'; 
            }else{
                echo '</hr>';
                echo '<h3 class="block-title text-primary">'.$LANG['traffic_amount'].$card['traffic'].'MB|'.$LANG['card_available_time'].$card['availabletime'].'</h3>';
                echo '<p>'.$card['number'].'</p>'; 
                $traffic = $card['traffic'];
                $time = $card['availabletime'];
            }
           
            
        }
    }
}   

function get_cards($host,$db,$user,$pass){
    $mysql = "SELECT * FROM cards WHERE cardstatus = 1 ORDER BY traffic";
    $db = new PDO('mysql:host=' . $host . ';dbname=' . $db, $user, $pass);
    $usage = $db->query($mysql);
    $usage = $usage->fetchAll();
    return $usage;
}     

function parse_card($card){
    if(!empty($card)){
        $array = array();
        foreach($card as $car){
           $array[] = $car['number']; 
        }
        return $array;
    }
    return array();
}    

function RandomPass($length = 8){
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; 
	$password = ''; 
	for ( $i = 0; $i < $length; $i++ ) 
	{ 
		$password .= $chars[ mt_rand(0, strlen($chars) - 1) ]; 
	} 
	return $password; 
}

function get_id($host,$db,$user,$pass){
    $mysql = "SELECT * FROM `cards` ORDER BY cardid desc LIMIT 1";
    $db = new PDO('mysql:host=' . $host . ';dbname=' . $db, $user, $pass);
    $usage = $db->query($mysql);
    $usage = $usage->fetch();
    if(empty($usage)){
        return 0;
    }else{
        return $usage['cardid'];
    }
}














