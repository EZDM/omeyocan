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
			global $txt, $x7c, $x7s, $print, $db, $prefix, $x7p;
			$sys_msg="";
			include_once("./lib/message.php");


			if(isset($_GET['ok']))
				$body = "<div id=\"sysmsg\">Messaggio inviato</div>";
			elseif(isset($_POST['to']) && isset($_POST['subject']) && isset($_POST['body'])){
				

				// Make sure the subject isn't null
				if($_POST['subject'] == "")
					$_POST['subject'] = $txt[173];

				// Send the msg
				$_POST['body'] = eregi_replace("\n","<Br>",$_POST['body']);
				
				if(!isset($_POST['group'])){
					$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$_POST[to]'");
					$row = $db->Do_Fetch_Row($query);
					if($row[0] == "")
						$person_error = true;
					else
						$person_error = false;
				}
				else{
					$person_error = false;
				}
				
				//Group send
				if(isset($_POST['group'])){
					if(!checkIfMaster() && !in_array($_POST['to'], $x7p->profile['usergroup'])){
						$body = "<div id=\"sysmsg\">Non sei autorizzato a inviare a questo gruppo</div>";
						$_POST['msg'] = $_POST['body'];
					}
					else{
						if(!checkIfMaster() && $_POST['to'] == "all"){
							$body = "<div id=\"sysmsg\">Non sei autorizzato a inviare a questo gruppo</div>";
							$_POST['msg'] = $_POST['body'];
						}
						else{
							if($_POST['to'] == "all")
								$query = "SELECT username FROM {$prefix}users WHERE sheet_ok = 1";
							else
								$query = "SELECT username FROM {$prefix}groups WHERE usergroup = '$_POST[to]'";
								
							$result = $db->DoQuery($query);
							
							//Do the real send
							while($row = $db->Do_Fetch_Assoc($result)){
								send_offline_msg($row['username'],$_POST['subject'],$_POST['body']);
							}
							
							// Reset values
							$_POST['subject'] = "";
							$_POST['to'] = "";
							$_GET['ok'] = 1;
							header("Location: index.php?act=mail&ok=1");
						
						}
						
					}

				}
				
				//Single person send
				elseif(count_offline($_POST['to']) >= $x7c->settings['max_offline_msgs'] && $x7c->settings['max_offline_msgs'] != 0){
					$body = "<div id=\"sysmsg\">".$txt[184]."</div>";
					$_POST['msg'] = $_POST['body'];
				}elseif($person_error){
					// Person doesn't exist
					$body = "<div id=\"sysmsg\">".$txt[610]."</div>";
					$_POST['msg'] = $_POST['body'];
				}else{
					send_offline_msg($_POST['to'],$_POST['subject'],$_POST['body']);
					// Reset values
					$_POST['subject'] = "";
					$_POST['to'] = "";
					$_GET['ok'] = 1;
					header("Location: index.php?act=mail&ok=1");
					
				}

				if(isset($_POST['msg']))
					$_POST['msg'] = eregi_replace("<Br>","\n",$_POST['msg']);

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
				$time = $nb[2];

				// Set default values for reply form
				$_POST['to'] = $author;

				$_POST['subject'] = $subject;

				$replybody = remove_chattags($msgbody);
				$replybody = eregi_replace("<br>","\n",$replybody);
				$_POST['msg'] = " \n\n$txt[174]\n\n".$replybody;

				//$msgbody = parse_message($msgbody);

				$body .= "
						<div> 
						<table class=\"inside_table\" width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
						<Tr>
							<td class=\"dark_row\"><B>Mittente:</b> $author</td>
						</tr>
						<Tr>
							<td class=\"dark_row\"><b>Oggetto:</b> $subject</td>
						</tr>
						<Tr>
							<td class=\"dark_row\"><b>Data ricezione:</b> $time<hr></td>
						</tr>
						</table>
						</div>
						
						
						<div id=\"msg_body\">
						<table class=\"inside_table\" width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
						<Tr>
							<td class=\"msg_txt\">$msgbody</td>
						</tr>
						</table>
						</div>
						
						<br>
						<div id=\"menu\">
						<a href=\"./index.php?act=mail&delete=$mid\">[$txt[175]]</a>
						<a href=\"index.php?act=mail&write&back={$_GET['read']}&subject=Re: {$_POST['subject']}&to={$_POST['to']}\">[Rispondi]</a>
						<a href=\"index.php?act=mail&write&back={$_GET['read']}&subject=I: {$_POST['subject']}\">[Inoltra]</a>
					
					<Br><Br><div align=\"center\">
					<div align=\"left\">
					<a href=\"index.php?act=mail\">[Elenco]</a>
					</div
					
					</div>";

			}else if(!isset($_GET['write'])){
				// Display a table of all messages

				$body .= "
                                        <script>
                                        function do_delete(){
							url = './index.php?act=mail&delete=_all_';
							if(!confirm('vuoi davvero cancellare tutti i messaggi?'))
									return;
                                                        window.location.href=url;
                                        }
                                        </script>
                                        
                                        <div id=\"message_tbl\">
						<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"inside_table\">
						<tr>
							<th>&nbsp;</td>
							<th>$txt[178]</td>
							<th>$txt[179]</td>
							<th>Data</td>
							<th>&nbsp;</td>
						</tr>

						";

				foreach($msgs as $id=>$val){
					$mid = $id;
					$author = $val[1];

					$nb = offline_msg_split($val[2]);
					$msgbody = $nb[0];
					$subject = $nb[1];
					$time = $nb[2];

					if($val[3] == 0)
						$img = "<img src=\"{$print->image_path}new_mail.gif\">";
					else
						$img = "<img src=\"{$print->image_path}old_mail.gif\">";

					$body .= "<tr>
								<td class=\"dark_row\">$img</td>
								<td class=\"dark_row\"><a href=\"./index.php?act=mail&read=$mid\">$subject</a></td>
								<td class=\"dark_row\">$author</td>
								<td class=\"dark_row\">$time</td>
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
				$body .= '<div id="menu"><a href="./index.php?act=mail&write">[Scrivi]</a>';
				 
				if(checkIfMaster() || $x7s->user_group != $x7c->settings['usergroup_default']){
					$body .= '<a href="./index.php?act=mail&write&group">[Mail di gruppo]</a>';
				} 
				else{
					$body .= '[Mail di gruppo]';
				}
				$body .= '<a href="#" onClick="do_delete()">[Cancella tutti]</a>';
				 
				 $body .= "\n</div>";

			}

			// DO send form
			
			if(isset($_GET['write'])){
				// These three isset() things are checking for default field values
				
				if(!isset($_GET['subject']))
					$_GET['subject'] = "";
	
				if(!isset($_GET['to']))
					$_GET['to'] = "";
	
				if(!isset($_POST['msg']))
					$_POST['msg'] = "";
	
	
				$back='';
				$replybody='';
				if(isset($_GET['back'])){
					$back="&read=".$_GET['back'];
					$nb = offline_msg_split($msgs[$_GET['back']][2]);
					$msgbody = $nb[0];
					$subject = $nb[1];
					
					$replybody = remove_chattags($msgbody);
					$replybody = eregi_replace("<br>","\n",$replybody);
					$replybody = " \n\n$txt[174]\n\n".$replybody;
				}
				
				$to = "<p style=\"text-align: center\">
					<input type=\"hidden\" name=\"act\" value=\"mail\">
					$txt[182]: 
					<br><input class=\"wickEnabled\" type=\"text\" name=\"to\" autocomplete=\"off\" value=\"$_GET[to]\">
					<br>";
				
				if(isset($_GET['group'])){
				
					
					if(checkIfMaster()){
						$elenco = '<option value="all">Tutti</option>';
						$query = "SELECT DISTINCT usergroup FROM {$prefix}groups";
						$result = $db->DoQuery($query);
						
						while($row = $db->Do_Fetch_Assoc($result)){
							$elenco .= "<option value=\"{$row['usergroup']}\"> {$row['usergroup']} </option>\n";
						}
						
					}

					else if($x7s->user_group != '' && $x7s->user_group != $x7c->settings['usergroup_default']){
						$elenco .= "<option value=\"{$x7s->user_group}\"> {$x7s->user_group} </option>\n";	
					}
					
					$to = "<p style=\"text-align: center\">
					<input type=\"hidden\" name=\"act\" value=\"mail\">
					<input type=\"hidden\" name=\"group\">
					$txt[182]: 
					<br><select class=\"text_input\" name=\"to\" style=\"background: white;\">
						$elenco	
					</select>
					<br>";
				}

				$accounts='';
				$query = "SELECT username FROM {$prefix}users WHERE sheet_ok = 1";
				$result = $db->DoQuery($query);

				while($row = $db->Do_Fetch_Assoc($result)){
					$accounts .="'$row[username]',";
				}
				$accounts.="''";
				
				$body .= "
					<script type=\"text/javascript\" language=\"JavaScript\">
					collection =
					[".
						$accounts
					."
					];
					</script>
					<script type=\"text/javascript\" language=\"JavaScript\" src=\"./lib/wick.js\"></script>
					
					<div align=\"center\">
					<form action=\"./index.php?act=mail\" method=\"post\">
					
					$to
					
					$txt[183]: 
					<br><input class=\"text_input\" type=\"text\" name=\"subject\" value=\"$_GET[subject]\">
					</p>
					
					<textarea htmlconv=yes name=\"body\" class=\"text_input\" cols=\"40\" rows=\"15\">$replybody</textarea><Br>
					<input type=\"submit\" value=\"$txt[181]\" class=\"button\">
					</form></div>
					<p style=\"text-align: center\">
					<a href=\"./index.php?act=mail\">[Elenco]</a>
					</p>
					";
			}
				
			return $body;
	}
	
	
	//This function returns true if the user is an admin or a master
	function checkIfMaster(){
		global $x7s, $x7c;
		
		$value = $x7c->permissions['write_master'];
		
		return $value;
	}

	function print_sheet($body,$bg){
		global $print,$x7c,$x7s;
		
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} -- Posta</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;
		
		$sfondo="./graphic/sfondoposta.jpg";
		
		/*if($x7c->settings['panic'])
	 		$sfondo="./graphic/sfondopostaobscure.jpg";*/
		
		$mail_style = '
		<LINK REL="SHORTCUT ICON" HREF="./favicon.ico">
		<style type="text/css">

			/*
			WICK: Web Input Completion Kit
			http://wick.sourceforge.net/
			Copyright (c) 2004, Christopher T. Holland,
			All rights reserved.

			Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

			Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
			Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
			Neither the name of the Christopher T. Holland, nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
			THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

					*/

			.floater {
			position: absolute;
			z-index:2;
			left: 125px;
			top: 45px;
			margin-top: 0;
			margin-left: 0;
			display:none;
			padding:0;
			}

			.floater {
			font-family: Gill, Helvetica, sans-serif;
			background-color:white;
			border:1px inset #979797;
			color:black;
			}

			.matchedSmartInputItem {
			font-size:0.8em;
			padding: 5px 10px 1px 5px;
			margin:0;
			cursor:pointer;
			}

			.selectedSmartInputItem {
			color:white;
			background-color:#3875D7;
			}

			#smartInputResults {
			padding:0;margin:0;
			}

			.siwCredit {
			margin:0;padding:0;margin-top:10px;font-size:0.7em;color:black;
			}


			a{
				color: #660000;
				font-weight: bold;
			}
			a:hover{
				color: white;
			}
			th{
				color: #660000;
				font-size: 8pt;
				text-align: left;
				border-bottom: solid 2px gray;
			}
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
				font-size: 8pt;
				background-image:url('.$sfondo.');
			}
			
			#inner_mail {
				width: 408px; 
				height: 330px;
				position: absolute;
				left: 45px;
				top: 140px;
				color: #660000;
			}
			
			#message_tbl {
				width: 400px; 
				height: 380px;
				overflow: auto;
			}
			
			#msg_body {
				width: 400px; 
				height: 270px;
				overflow: auto;
			}
     .msg_txt{
        color: #660000;
        font-size: 8pt;
        font-weight: bold;
     }
			.dark_row{
				background: transparent;
				font-size: 8pt;
			}
		
			.chatmsg{
				color: #660000;
				font-size: 8pt;
			}
			
			.text_input, .wickEnabled{
				color: #660000;
				font-weight: bold;
				background: transparent;
				border: 1px solid black;
			}
			
			.indiv{
				position: absolute;	
			}
			
			#sysmsg{
				position: absolute;
				left: 0px;
				top: 380px;
				color: red;
			}
			#menu{
				position: absolute;
				left: 0px;
				top: 340px;			
			}
			
		</style>
		';
		
		echo $mail_style;
		
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
