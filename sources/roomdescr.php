<?PHP
/*

    This file is part of X7 chat Version 2.0.5 - RPG enhanced.
    Released March 2008. Copyright (c) 2008 by Niccolo' Cascarano.

    X7 chat Version 2.0.5 - RPG enhanced is free software:
     you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    X7 chat Version 2.0.5 - RPG enhanced is distributed 
    in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with X7 chat Version 2.0.5 - RPG enhanced.  
    If not, see <http://www.gnu.org/licenses/>


*/
?>
<?PHP

	function roomdescr_main(){
		global $x7s;
		
		$page='';
		
		$body = roomdescr();
			
			
		print_page($body);
	}
	
	
	function roomdescr(){
			global $txt, $x7c, $x7s, $print, $db, $prefix;
			if(!isset($_GET['room'])){
				die("Missing parameter");
			}

			$body ='';

			$query = $db->DoQuery("SELECT topic, background FROM {$prefix}rooms WHERE name='$_GET[room]' ");
			$row = $db->Do_Fetch_Assoc($query);

			if($row == null){
				die("Room $_GET[room] does not exists");
			}

			if($row['topic']==''){
				$body = "Non esiste la descrizione per questa stanza";
			}
			else{
				if($row['background']!=''){
					$body .= '<img src="'.$row['background'].'" />'."\n";
				}

				$body .= '<p id="descr">'.$row['topic'].'</p>';
			}

			return $body;
			
	}
	
	

	function print_page($body){
		global $print,$x7c,$x7s;
		
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} Creazione PG</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;
		
		
		$mail_style = '
		<style type="text/css">
			#roomdescr{
				position: relative;
				top: 0;
				left: 0;
				width: 100%;
			}

			#inner_roomdescr{
				position: relative;
				width: 100%;
				text-align: center;
			}
			
		</style>
		';
		
		echo $mail_style;
		
		echo '</head><body>
 			<div id="roomdescr">
 				<div id="inner_roomdescr">
 			';
 			
		
		echo $body;
		echo '
			</div>
		</div>
		</body>
			</html>';
	}


?>