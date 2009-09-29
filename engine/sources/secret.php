<?PHP

function secret_main(){
    global $print,$x7c,$x7s,$prefix,$db;


    if($_GET['secret']=='')
        die("Empty secret");

    
        
    $query = $db->DoQuery("SELECT name FROM {$prefix}rooms WHERE long_name='{$_GET['secret']}'");
    $row = $db->Do_Fetch_Assoc($query);


    include_once("./lib/secrets/{$_GET['secret']}.php");

    //It changes each 65535 seconds ~18h
    $today_secret=$secrets[(time()>>16)%$secret_lenght];

    if($row['name'] != $today_secret){
          $db->DoQuery("UPDATE {$prefix}rooms SET name='$today_secret' WHERE long_name='{$_GET['secret']}'");
          $db->DoQuery("UPDATE {$prefix}users SET position='$today_secret' WHERE position='{$row['name']}'");
          $db->DoQuery("UPDATE {$prefix}messages SET room='$today_secret' WHERE room='{$row['name']}'");
    }



    
    if(isset($_GET['code']) && $_GET['code']==$today_secret){
        $db->DoQuery("UPDATE {$prefix}users SET secrets='0' WHERE username='{$x7s->username}'");
        $body = '<img src="graphic/Giardino_dei_Suicidi.jpg">
                  <a onClick="javascript: opener.location.href=\'index.php?act=frame&room='.$today_secret.'\'; window.close(self); ">
                  
                  <div style="background-color: transparent; position: absolute; top: 380px; left: 465px; width: 100px; height: 40px;"></div>

                  </a>
        ';

        print_sheet($body);
    }

    else{
      $query = $db->DoQuery("SELECT secrets FROM {$prefix}users WHERE username='{$x7s->username}'");
      $row = $db->Do_Fetch_Assoc($query);

      $body="<img src=\"graphic/Smarrirsi-nei-boschi.jpg\">";
      $row['secrets']++;

      
      //We ban for 5 minutes after two attempts
      if($row['secrets'] > 2){
        echo  header("Location: index.php?act=logout&secret");
        new_ban2($x7s->username,600,"lo smarrimento alla ricerca di un luogo segreto. Per 10 minuti non potrai ricollegarti","*");
        $db->DoQuery("UPDATE {$prefix}users SET secrets='0' WHERE username='{$x7s->username}'");
        return;
      }

      $db->DoQuery("UPDATE {$prefix}users SET secrets='{$row['secrets']}' WHERE username='{$x7s->username}'");
      

      print_sheet($body);
    }
}


function print_sheet($body){
		global $print,$x7c,$x7s;

		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']}</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;
		
		echo '
		<style type="text/css">
			INPUT{
				height: 21px;
                        }
			a:hover{
				color: red;
                        }

		</style>
		';
		

		
		echo '</head><body>
 			<div>
 			';
 			
		
		echo $body;
		
		echo '

		</div>
		</body>
			</html>';
}

?>