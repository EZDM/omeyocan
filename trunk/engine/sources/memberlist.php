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

/////////////////////////////////////////////////////////////// 
//
//		X7 Chat Version 2.0.4
//		Released June 16, 2006
//		Copyright (c) 2004-2006 By the X7 Group
//		Website: http://www.x7chat.com
//
//		This program is free software.  You may
//		modify and/or redistribute it under the
//		terms of the included license as written  
//		and published by the X7 Group.
//  
//		By using this software you agree to the	     
//		terms and conditions set forth in the
//		enclosed file "license.txt".  If you did
//		not recieve the file "license.txt" please
//		visit our website and obtain an official
//		copy of X7 Chat.
//
//		Removing this copyright and/or any other
//		X7 Group or X7 Chat copyright from any
//		of the files included in this distribution
//		is forbidden and doing so will terminate
//		your right to use this software.
//	
////////////////////////////////////////////////////////////////EOH
?><?PHP

	// This function displays a list of all members
	function memberlist(){
		global $db, $prefix, $txt, $print, $x7c, $x7s;
		
		$room='';
		
		if(isset($_GET['room'])){
			$query = $db->DoQuery("SELECT position FROM {$prefix}users WHERE username='$x7s->username'");
			$row = $db->Do_Fetch_Assoc($query);
			$room =$row['position'];

			//Se sono qui devo per forza essere in land... per lo meno in mappa
			//Non dovrebbe mai essere vero il branch che segue
			if($room==''){
				$room='Mappa';
			}
		}
			
		// See if the user wants the data sorted in anyway
		$order = " ORDER BY username ASC";
		$sort_order_1 = 1;
		$sort_order_2 = 3;
		if(isset($_GET['sort'])){
			if($_GET['sort'] == "1"){
				$order = " ORDER BY username ASC";
				$sort_order_1 = 2;
			}elseif($_GET['sort'] == "2"){
				$order = " ORDER BY username DESC";
			}elseif($_GET['sort'] == "3"){
				$order = " ORDER BY position ASC";
				$sort_order_2 = 4;
			}elseif($_GET['sort'] == "4"){
				$order = " ORDER BY position DESC";
			}
		}

		//Toggle permission to talk
		if((isset($_GET['mute']) || isset($_GET['unmute']))&& isset($_GET['user'])){
			if($x7c->permissions['admin_panic']){
				$value=1;
				if(isset($_GET['mute']))
					$value=0;

				$db->DoQuery("UPDATE {$prefix}users SET talk='$value' WHERE username='$_GET[user]'");
					
			}
		}
		
		//Verifica che tutti gli utenti con position settato siano veramente online
		$query = $db->DoQuery("SELECT username FROM {$prefix}users WHERE position<>''");
		while($row = $db->Do_Fetch_Assoc($query)){
			$query2 = $db->DoQuery("SELECT count(*) AS num FROM {$prefix}online WHERE name='{$row['username']}'");
			$row2 = $db->Do_Fetch_Assoc($query2);
			
			if($row2['num'] == 0){
				$db->DoQuery("UPDATE {$prefix}users SET position='' WHERE username='{$row['username']}'");
			}
		
		}
		
		// Get the userlist and online data
		// we force a fake join with Mappa
		$more_query="";
                if(isset($_GET['dead'])){
                    $more_query = " AND u.info='Morto'";
                }
		
		$query = $db->DoQuery("SELECT username, position,talk,long_name,type FROM {$prefix}users u,
                                            {$prefix}rooms r
                                            WHERE (r.name = u.position
                                            OR (u.position='' AND r.name='Mappa'))
                                            {$more_query}
                                            {$order}");
		
		
		$body = "<table align=\"center\" cellspacing=\"0\" cellpadding=\"2\">
			<tr>
				<td class=\"col_header\" height=\"25\">&nbsp;<a class=\"dark_link\" href=\"index.php?act=memberlist&sort={$sort_order_1}&room=$room\">$txt[2]</a></td>
				<td class=\"col_header\" height=\"25\"><a class=\"dark_link\" href=\"index.php?act=memberlist&sort={$sort_order_2}&room=$room\">$txt[560]</td>";
		if($room!='' && $room!="Mappa")
			$body.="<td class=\"col_header\" height=\"25\">Sussurra</td>";
				
		if($x7c->permissions['admin_panic']){
			if($room!='' && $room!="Mappa")
				$body .= "<td class=\"col_header\" height=\"25\">Dadi</td>";

			$body.="<td class=\"col_header\" height=\"25\">Mute / Unmute</td>";
		}
				
		$body.=	"</tr>";
		
		while($row = $db->Do_Fetch_Assoc($query)){
			
			if(($room!='' && $row['position']!='') ||$room==''){
				// Output this entry
				$position='';
				if($row['long_name']!="Mappa" && $row['long_name']!=''){
                                        if($x7c->permissions['admin_panic']){
                                          $position = '<a class="dark_link" onClick="javascript: window.opener.location.href=\'index.php?act=frame&room='.$row['position'].'\';">'.$row['long_name'].'</a>';
                                          }
                                        else if($row['type']!=2 || $row['username']==$x7s->username){//We do not show position in private chat
					   $position = $row['long_name'];
                                        }
                                        else{
                                            $position = "Mappa";
                                        }
                                }
				else if($row['position']=="Mappa")
					$position = "Mappa";
				else
					$position = "&nbsp;";
				
				$body .= "\n<tr>
							<td class=\"dark_row\"><a class=\"dark_link\" onClick=\"javascript: window.open('index.php?act=sheet&pg={$row['username']}','sheet_other','width=500,height=680, toolbar=no, status=yes, location=no, menubar=no, resizable=no, status=yes');\">{$row['username']}</a></td>
							<td class=\"dark_row\">{$position}</td>";
				if($room!='' && $room!="Mappa")
					if($row['long_name'] != '' && $row['long_name']==$room)
						$body .= "<td class=\"dark_row\"><a class=\"dark_link\" onClick=\"javascript: opener.document.chatIn.msgi.value='@{$row['username']}@ ';\">Invia sussurro</a></td>";
					else
						$body .= "<td class=\"dark_row\">&nbsp;</td>";
				
				if($x7c->permissions['admin_panic']){
					$new_state='';
					$action='';
					if($row['talk']){
						$new_state="Mute";
						$action="mute";
					}
					else{
						$new_state="<span class=\"bold_red\">Unmute</span>";
						$action="unmute";
					}

					$getstanza='';

					if($room!='' && $room!="Mappa"){
						$body .= "<td class=\"dark_row\"height=\"25\"><a class=\"dark_link\" href=\"index.php?act=usr_action&action=dice&user={$row['username']}&room={$row['position']}\">Tira un dado</a></td>";
					}

					if($room!='')
						$getstanza="&room";
						
					$body.="<td class=\"dark_row\"height=\"25\"><a class=\"dark_link\" href=\"index.php?act=memberlist&$action&user={$row['username']}$getstanza\">$new_state</a></td>";

				}
			
				$body .= "</tr>";
			}
			
		}
		
		$body .= "</table><p align=\"center\"><a class=\"dark_link\" href=\"#\" onClick=\"javascript: window.close();\">[Chiudi]</a></p></div>";

                $title='';
		if($room!='')
			$title = "Lista cittadini Online";
                else if(isset($_GET['dead']))
                        $title = "Lista deceduti";
		else
			$title = "Lista cittadini";
			
		$body .= '<script language="javascript" type="text/javascript">
				setTimeout("update()",10000);
				
				function update(){
					window.location.reload();
				}
			</script>';


		print_memberlist($body,'', $title);
	}
	
	function print_memberlist($body,$sfondo='',$myhead=''){
		global $print,$x7c,$x7s;
		
                
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} -- $myhead</title>";
		echo $print->style_sheet;
		
		$sfondo = './graphic/sfondopresenti.jpg';
		
		$memberlist_style = '
		<style type="text/css">
			#member{
				width: 450px;
				height: 500px;
				background-image:url('.$sfondo.');
			}
			
			.dark_row{
				font-size: 10pt;
				color: black;
				background: transparent;
				border-bottom: solid 1px gray;
			}

			table{
				width: 90%;
				margin-top: 20px;
				border: solid 2px gray;
			}

			.dark_link{
				font-style: italic;
				color: black;
			}

			.col_header{
				background: transparent;
				margin-top: 10px;
    				border: 0;
				border-top: solid 2px gray;
				border-bottom: solid 2px gray;
			}

			a:hover{
				color: red;
			}

			.bold_red{
				color: red;
				font-weight: bold;
			}

			#inner_member{
				width: 99%;
				height: 98%;
				overflow: auto;
			}

		
			
		</style>
		';
		
		
		
		echo $memberlist_style;
		
		echo '</head><body>
 			<div class="member" id="member">
 				<div id="inner_member">
 			';
 			
		
		
		
		echo $body;
		echo '
			</div>
		</div>
		</body>
			</html>';
		
	}

?> 
