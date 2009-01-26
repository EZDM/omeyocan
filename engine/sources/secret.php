<?PHP

function secret_main(){
    global $print,$x7c,$x7s,$prefix,$db;
    if(isset($_GET['code']) && $_GET['code']=="2621"){
        $body = '<img src="graphic/Giardino_dei_Suicidi.jpg">
                  <a onClick="javascript: opener.location.href=\'index.php?act=frame&room=Giardino\'; window.close(self); ">
                  
                  <div style="background-color: transparent; position: absolute; top: 380px; left: 465px; width: 100px; height: 40px;"></div>

                  </a>
        ';

        print_sheet($body);
    }

    else{
      $query = $db->DoQuery("SELECT secrets FROM {$prefix}users WHERE username='{$x7s->username}'");
      $row = $db->Do_Fetch_Assoc($query);

      $body="Non dovresti girare nei boschi... potrebbero succedere cose brutte";
      

      if($row['secrets'] >= 3){
        $db->DoQuery("UPDATE {$prefix}users SET secrets='0' WHERE username='{$x7s->username}'");
        echo  header("Location: index.php?act=logout");
        return;
      }
      
      $row['secrets']++;
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