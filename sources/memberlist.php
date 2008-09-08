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
		global $db, $prefix, $txt, $print, $x7c;
		
		$room='';
		
		if(isset($_GET['room']))
			$room =$_GET['room'];
			
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
		$query = $db->DoQuery("SELECT username, position FROM {$prefix}users {$order}");
		
		
		$body = "<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"col_header\">
			<tr>
				<td width=\"100\" height=\"25\">&nbsp;<a href=\"index.php?act=memberlist&sort={$sort_order_1}&room=$room\">$txt[2]</a></td>
				<td width=\"100\" height=\"25\"><a href=\"index.php?act=memberlist&sort={$sort_order_2}&room=$room\">$txt[560]</td>";
		if($room!='' && $room!="Mappa")
			$body.="<td width=\"100\" height=\"25\">Sussurra</td>";
				
		if($x7c->permissions['admin_panic'] && $room!='' && $room!="Mappa")
			$body .= "<td width=\"100\" height=\"25\">Dadi</td>";
				
		$body.=	"</tr>
		</table>
		<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"inside_table\">";
		
		while($row = $db->Do_Fetch_Assoc($query)){
			
		
			if(($room!='' && $row['position']!='')||$room==''){
				// Output this entry
				$position='';
				if($row['position']!="Mappa")
					$position = '<a onClick="javascript: window.opener.location.href=\'index.php?act=frame&room='.$row['position'].'\';">'.$row['position'].'</a>';
				else
					$position = "Mappa";
				
				$body .= "<tr>
							<td width=\"100\" class=\"dark_row\"><a onClick=\"javascript: window.open('index.php?act=sheet&pg={$row['username']}','sheet_other','width=500,height=680, toolbar=no, status=yes, location=no, menubar=no, resizable=no, status=yes');\">{$row['username']}</a></td>
							<td width=\"100\" class=\"dark_row\">{$position}</td>";
				if($room!='' && $room!="Mappa")
					if($row['position'] != '' && $row['position']==$room)
						$body .= "<td width=\"100\" class=\"dark_row\"><a onClick=\"javascript: opener.document.chatIn.msgi.value='@{$row['username']}@ ';\">Invia sussurro</a></td>";
					else
						$body .= "<td width=\"100\" class=\"dark_row\"></td>";
				
				if($x7c->permissions['admin_panic'] && $room!='' && $room!="Mappa")
					$body .= "<td width=\"100\" class=\"dark_row\"height=\"25\"><a href=\"index.php?act=usr_action&action=dice&user={$row['username']}&room={$row['position']}\">Tira un dado</a></td>";
			
				$body .= "</tr>";
			}
			
		}
		
		$body .= "</table><p align=\"center\"><a href=\"#\" onClick=\"javascript: window.close();\">[Chiudi]</a></p></div>";

		if($room!='')
			$head = "Lista cittadini Online";
		else
			$head = "Lista cittadini";
			
		$body .= '<script language="javascript" type="text/javascript">
				setTimeout("update()",10000);
				
				function update(){
					window.location.reload();
				}
			</script>';
					
		print_memberlist($body);
	}
	
	function print_memberlist($body,$sfondo=''){
		global $print,$x7c,$x7s;
		
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} -- Lista utenti</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;
		
		$sfondo = './graphic/sfondopresenti.jpg';
		
		$memberlist_style = '
		<style type="text/css">
			#member{
				width: 450px;
				height: 500px;
				background-image:url('.$sfondo.');
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
