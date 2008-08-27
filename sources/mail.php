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

	function mail_main(){
		global $x7s;
		
		$page='';
		
		$body = perfom_mail();
			
			
		print_sheet($body,$page);
	}
	
	
	function perfom_mail(){
			global $txt, $x7c, $x7s, $print, $db, $prefix;
			$sys_msg="";
			include("./lib/message.php");


			if(isset($_GET['ok']))
				$body = "<div id=\"sysmsg\">Messaggio inviato</div>";
			elseif(isset($_GET['to']) && isset($_GET['subject']) && isset($_GET['body'])){
				

				// Make sure the subject isn't null
				if($_GET['subject'] == "")
					$_GET['subject'] = $txt[173];

				// Send the msg
				$_GET['body'] = eregi_replace("\n","<Br>",$_GET['body']);

				$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$_GET[to]'");
				$row = $db->Do_Fetch_Row($query);
				if($row[0] == "")
					$person_error = true;
				else
					$person_error = false;

				if(count_offline($_GET['to']) >= $x7c->settings['max_offline_msgs'] && $x7c->settings['max_offline_msgs'] != 0){
					$body = "<div id=\"sysmsg\">".$txt[184]."</div>";
					$_GET['msg'] = $_GET['body'];
				}elseif($person_error){
					// Person doesn't exist
					$body = "<div id=\"sysmsg\">".$txt[610]."</div>";
					$_GET['msg'] = $_GET['body'];
				}else{
					send_offline_msg($_GET['to'],$_GET['subject'],$_GET['body']);
					// Reset values
					$_GET['subject'] = "";
					$_GET['to'] = "";
					$_GET['ok'] = 1;
					header("Location: index.php?act=mail&ok=1");
					
				}

				if(isset($_GET['msg']))
					$_GET['msg'] = eregi_replace("<Br>","\n",$_GET['msg']);

			}elseif(isset($_GET['delete'])){
				$body = "<div id=\"sysmsg\">Messaggio cancellato</div>";
				offline_delete($_GET['delete']);
			}else{
				$body = "";
			}

			$msgs = get_offline_msgs();

			if(isset($_GET['read'])){
				// Print an individual message

				offline_markasread($_GET['read']);

				$mid = $_GET['read'];
				$author = $msgs[$mid][1];

				$nb = offline_msg_split($msgs[$mid][2]);
				$msgbody = $nb[0];
				$subject = $nb[1];

				// Set default values for reply form
				$_GET['to'] = $author;

				if(!eregi("^$txt[176]",$subject))
					$_GET['subject'] = "$txt[176]$subject";
				else
					$_GET['subject'] = $subject;

				$replybody = remove_chattags($msgbody);
				$replybody = eregi_replace("<br>","\n",$replybody);
				$_GET['msg'] = "\n\n$txt[174]\n\n".$replybody;

				$msgbody = parse_message($msgbody);

				$body .= "<Br><Br><table width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
						<Tr>
							<td class=\"col_header\">&nbsp;$subject</td>
						</tr>
						</table>
						<div id=\"msg_body\">
						<table class=\"inside_table\" width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
						<Tr>
							<td class=\"dark_row\"><B>$txt[179]: $author</b><hr><br></td>
						</tr>
						<Tr>
							<td class=\"dark_row\">$msgbody</td>
						</tr>
						</table>
						</div>
						<a href=\"./index.php?act=mail&delete=$mid\">[$txt[175]]</a>
						<a href=\"index.php?act=mail&write&back={$_GET['read']}&subject={$_GET['subject']}&to={$_GET['to']}&msg={$_GET['msg']}\">[Rispondi]</a>
					
					<Br><Br><div align=\"center\">
					<div align=\"center\">
					<a href=\"index.php?act=mail\">[$txt[77]]</a>
					
					</div>";

			}else if(!isset($_GET['write'])){
				// Display a table of all messages

				$body .= "<table width=\"96%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"col_header\">
						<tr>
							<td width=\"30\"></td>
							<td width=\"100\">$txt[178]</td>
							<td width=\"100\">$txt[179]</td>
							<td>&nbsp;</td>
						</tr>
						</table>
						<div id=\"message_tbl\">
						<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"inside_table\">
						";

				foreach($msgs as $id=>$val){
					$mid = $id;
					$author = $val[1];

					$nb = offline_msg_split($val[2]);
					$msgbody = $nb[0];
					$subject = $nb[1];

					if($val[3] == 0)
						$img = "<img src=\"{$print->image_path}new_mail.gif\">";
					else
						$img = "<img src=\"{$print->image_path}old_mail.gif\">";

					$body .= "<tr>
								<td width=\"30\" class=\"dark_row\">$img</td>
								<td width=\"100\" class=\"dark_row\"><a href=\"./index.php?act=mail&read=$mid\">$subject</a></td>
								<td width=\"100\" class=\"dark_row\">$author</td>
								<td class=\"dark_row\"><a href=\"./index.php?act=mail&delete=$mid\">[$txt[175]]</a></td>
							</tr>";
				}

				$body .= "</table>
					</div>";

				// Display Inbox totals
				if($x7c->settings['max_offline_msgs'] != 0){
					$number = count_offline($x7s->username);
					$percentage = ($number/$x7c->settings['max_offline_msgs'])*100;
					$percentage .= "%";

					$number = $x7c->settings['max_offline_msgs']-$number;

					$txt[185] = eregi_replace("_p",$percentage,$txt[185]);
					$txt[185] = eregi_replace("_n","$number",$txt[185]);

					$body .= "<Br><br>$txt[185]";

				}
				$body .= '<div id="menu"><a href="./index.php?act=mail&write">[Scrivi]</a> [Mail di gruppo]</div>';

			}

			// DO send form
			
			if(isset($_GET['write'])){
				// These three isset() things are checking for default field values
				if(!isset($_GET['subject']))
					$_GET['subject'] = "";
	
				if(!isset($_GET['to']))
					$_GET['to'] = "";
	
				if(!isset($_GET['msg']))
					$_GET['msg'] = "";
	
	
				$back='';
				if(isset($_GET['back']))
					$back="&read=".$_GET['back'];
					
				$body .= "<br><br><div align=\"center\">
					<form action=\"./index.php\" method=\"get\">
					<b>$txt[181]</b><Br>
					<input type=\"hidden\" name=\"act\" value=\"mail\">
					$txt[182]: <input class=\"text_input\" type=\"text\" name=\"to\" value=\"$_GET[to]\"><Br>
					$txt[183]: <input class=\"text_input\" type=\"text\" name=\"subject\" value=\"$_GET[subject]\"><br><textarea name=\"body\" class=\"text_input\" cols=\"40\" rows=\"7\">$_GET[msg]</textarea><Br>
					<input type=\"submit\" value=\"$txt[181]\" class=\"button\">
					</form></div>
					<a href=\"./index.php?act=mail$back\">[Indietro]</a>
					";
			}
				
			return $body;
	}
	

	function print_sheet($body,$bg){
		global $print,$x7c,$x7s;
		
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} -- Posta</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;
		
		echo '
		<style type="text/css">
			INPUT{
				height: 21px;
			}
			#mail {
				width: 488px; 
				height: 650px;
				position: absolute;
				left: 0px;
				top: 0px;
				color: black;
				font-weight: bold;
				font-size: 11pt;
				background-image:url(./graphic/sfondoposta.jpg);
			}
			
			#inner_mail {
				width: 408px; 
				height: 580px;
				position: absolute;
				left: 45px;
				top: 60px;
				color: white;
			}
			
			#message_tbl {
				width: 400px; 
				height: 380px;
				overflow: auto;

			}
			#msg_body {
				width: 400px; 
				height: 200px;
				overflow: auto;
			}
			.dark_row{
				background: transparent;
			}
			.chatmsg{
				color: white;
			}
			
			.text_input{
				color: white;
				font-weight: bold;
			}
			
			.indiv{
				position: absolute;	
			}
			
			#sysmsg{
				position: absolute;
				left: 20px;
				top: 520px;
				color: red;
			}
			#menu{
				position: absolute;
				left: 0px;
				top: 400px;			
			}
			
		</style>
		';
		
		echo '</head><body>
 			<div class="mail" id="mail">
 				<div id="inner_mail">
 			';
 			
		
		echo $body;
		echo '
			</div>
		</div>
		</body>
			</html>';
	}


?>