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
		$letter='';
		
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
		else{
			$query_banned = $db->DoQuery("SELECT user_ip_email as username, reason FROM {$prefix}banned");
			while($row_banned = $db->Do_Fetch_Assoc($query_banned)){
				$banned[$row_banned['username']]=$row_banned['reason'];
			}
		}

		if(isset($_GET['letter'])){
        	$letter=$_GET['letter'];
        }
			
		// See if the user wants the data sorted in anyway
		$order = " ORDER BY username ASC";
		$sort_order_1 = 2;
		$sort_order_2 = 4;
		if(isset($_GET['sort'])){
			if($_GET['sort'] == "1"){
				$order = " ORDER BY username ASC";
				$sort_order_1 = 2;
			}elseif($_GET['sort'] == "2"){
				$order = " ORDER BY username DESC";
				$sort_order_1 = 1;
			}elseif($_GET['sort'] == "3"){
				$order = " ORDER BY position ASC";
				$sort_order_2 = 4;
			}elseif($_GET['sort'] == "4"){
				$order = " ORDER BY position DESC";
				$sort_order_2 = 3;
			}
		}
		
		$costitution=false;
		$sheet=false;
		if($x7c->permissions['admin_panic']){
			if(isset($_GET['cos'])){
				$costitution = true;
			}
			elseif(isset($_GET['sheet'])){
				$sheet=true;
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

                if($letter!=''){
                    $more_query .= " AND username LIKE '$letter%' ";
                }

                $get_room='';
                if($room!='')
                    $get_room="&room=$room";
                    
		if(!$costitution && !$sheet){
			$query = $db->DoQuery("SELECT username, position,talk,long_name,type,admin_panic,m_invisible AS invisible
                                          FROM {$prefix}users u,
                                            {$prefix}rooms r, {$prefix}permissions p
                                            WHERE (r.name = u.position
                                            OR (u.position='' AND r.name='Mappa'))
                                            AND p.usergroup = u.user_group
                                            {$more_query}
                                            {$order}");
		}
		elseif($sheet){
			$query = $db->DoQuery("SELECT username, position,talk,long_name,type,admin_panic,m_invisible AS invisible
                                          FROM {$prefix}users u,
                                            {$prefix}rooms r, {$prefix}permissions p
                                            WHERE (r.name = u.position
                                            OR (u.position='' AND r.name='Mappa'))
                                            AND p.usergroup = u.user_group
                                            AND sheet_ok = 0
                                            {$more_query}
                                            {$order}");
		}
		elseif($costitution){
			$query = $db->DoQuery("SELECT u.username AS username, position,talk,long_name,type,admin_panic,m_invisible AS invisible
                                          FROM {$prefix}users u,
                                            {$prefix}rooms r, {$prefix}permissions p,
                                            {$prefix}usercharact uc
                                            WHERE (r.name = u.position
                                            OR (u.position='' AND r.name='Mappa'))
                                            AND p.usergroup = u.user_group
                                            AND uc.username = u.username
                                            AND uc.charact_id = 'rob'
                                            AND uc.value <= '6'
                                            AND sheet_ok = '1'
                                            {$more_query}
                                            {$order}");
		}
                                            
		$additional_controls='';                                            
        if($x7c->permissions['admin_panic'] && $room==''){
        	$additional_controls .= "<br><a href=\"index.php?act=memberlist&cos\">[Mostra robustezza &lt;= 6]</a><a href=\"index.php?act=memberlist&sheet\">[Mostra pg senza scheda]</a>";
		}
		
		$body = "<div id=\"navigator\">
                      <a href=\"index.php?act=memberlist$get_room\">[Tutti]</a><br>
                      <a href=\"index.php?act=memberlist&letter=a$get_room\">[a]</a>
                      <a href=\"index.php?act=memberlist&letter=b$get_room\">[b]</a>
                      <a href=\"index.php?act=memberlist&letter=c$get_room\">[c]</a>
                      <a href=\"index.php?act=memberlist&letter=d$get_room\">[d]</a>
                      <a href=\"index.php?act=memberlist&letter=e$get_room\">[e]</a>
                      <a href=\"index.php?act=memberlist&letter=f$get_room\">[f]</a>
                      <a href=\"index.php?act=memberlist&letter=g$get_room\">[g]</a>
                      <a href=\"index.php?act=memberlist&letter=h$get_room\">[h]</a>
                      <a href=\"index.php?act=memberlist&letter=i$get_room\">[i]</a>
                      <a href=\"index.php?act=memberlist&letter=j$get_room\">[j]</a>
                      <a href=\"index.php?act=memberlist&letter=k$get_room\">[k]</a>
                      <a href=\"index.php?act=memberlist&letter=l$get_room\">[l]</a>
                      <a href=\"index.php?act=memberlist&letter=m$get_room\">[m]</a><br>
                      <a href=\"index.php?act=memberlist&letter=n$get_room\">[n]</a>
                      <a href=\"index.php?act=memberlist&letter=o$get_room\">[o]</a>
                      <a href=\"index.php?act=memberlist&letter=p$get_room\">[p]</a>
                      <a href=\"index.php?act=memberlist&letter=q$get_room\">[q]</a>
                      <a href=\"index.php?act=memberlist&letter=r$get_room\">[r]</a>
                      <a href=\"index.php?act=memberlist&letter=s$get_room\">[s]</a>
                      <a href=\"index.php?act=memberlist&letter=t$get_room\">[t]</a>
                      <a href=\"index.php?act=memberlist&letter=u$get_room\">[u]</a>
                      <a href=\"index.php?act=memberlist&letter=v$get_room\">[v]</a>
                      <a href=\"index.php?act=memberlist&letter=w$get_room\">[w]</a>
                      <a href=\"index.php?act=memberlist&letter=x$get_room\">[x]</a>
                      <a href=\"index.php?act=memberlist&letter=y$get_room\">[y]</a>
                      <a href=\"index.php?act=memberlist&letter=z$get_room\">[z]</a>
                      $additional_controls
                    </div>";

                $get_letter ='';
                if($letter != 0)
                  $get_letter = "&letter=$letter";
                  
		$additional_get='';                  
        if($costitution){
        	$additional_get .= "&cos";
        }
        
        if($sheet){
        	$additional_get .= "&sheet";
        }
        
		$body .= "<table align=\"center\" cellspacing=\"0\" cellpadding=\"2\">
			<tr>
				<td class=\"col_header\" height=\"25\">&nbsp;<a class=\"dark_link\" href=\"index.php?act=memberlist&sort={$sort_order_1}$get_room$get_letter$additional_get\">$txt[2]</a></td>
				<td class=\"col_header\" height=\"25\"><a class=\"dark_link\" href=\"index.php?act=memberlist&sort={$sort_order_2}$get_room$get_letter$additional_get\">$txt[560]</td>";
		if($room!='' && $room!="Mappa")
			$body.="<td class=\"col_header\" height=\"25\">Sussurra</td>";
				
		if($x7c->permissions['admin_panic']){
			if($room!='' && $room!="Mappa")
				$body .= "<td class=\"col_header\" height=\"25\">Dadi</td>";

			$body.="<td class=\"col_header\" height=\"25\">Mute / Unmute</td>";
		}
				
		$body.=	"</tr>";
		
	
		$list[0]="";
		$list[1]="";
		$cur=0;
		
		
		while($row = $db->Do_Fetch_Assoc($query)){
			
			if(($room!='' && $row['position']!='') ||$room==''){
				// Output this entry
				$position='';
				if($row['long_name']!="Mappa" && $row['long_name']!=''){
                                        if($x7c->permissions['admin_panic']){
                                          $position = '<a class="dark_link" onClick="javascript: window.opener.location.href=\'index.php?act=frame&room='.$row['position'].'\';">'.$row['long_name'].'</a>';
                                        }
                                        else{
					   $position = $row['long_name'];
                                        }
                                }
				else if($row['position']=="Mappa")
					$position = "Mappa";
				else
					$position = "&nbsp;";

                                //For Quest buster
                                if($position != "&nbsp;" &&
                                    $row['admin_panic'] &&
                                    $row['invisible'] &&
                                    !$x7c->permissions['admin_panic']){
                                            $position = "Ovunque";
                                }
				$master_gif="";
				
				$cur=0;
				
				if(isset($_GET['sort'])){
					if($position=="Ovunque" && $_GET['sort'] == "3"){
						$cur=0;
					}
					if($position=="Ovunque" && $_GET['sort'] == "4"){
						$cur=1;
					}
					if($position!="Ovunque" && $_GET['sort'] == "3"){
						$cur=1;
					}
					if($position!="Ovunque" && $_GET['sort'] == "4"){
						$cur=0;
					}
				}
				
				$barred='';
				if(!isset($_GET['room'])){
					if(isset($banned) && isset($banned[$row['username']])){
						$barred = " style=\"text-decoration: line-through;\" title=\"".$banned[$row['username']]."\" ";
					}
				}
				

				if($row['admin_panic'])
					$master_gif='&nbsp;<img src="./graphic/master_gif.gif" />';
				
				$list[$cur] .= "\n<tr>
							<td class=\"dark_row\"><a $barred class=\"dark_link\" onClick=\"javascript: window.open('index.php?act=sheet&pg={$row['username']}','sheet_other','width=500,height=680, toolbar=no, status=yes, location=no, menubar=no, resizable=no, status=yes');\">{$row['username']}$master_gif</a></td>
							<td class=\"dark_row\">{$position}</td>";
				
				if($room!='' && $room!="Mappa")
					if($row['position'] != '' && $row['position']==$room)
						$list[$cur] .= "<td class=\"dark_row\"><a class=\"dark_link\" onClick=\"javascript: opener.document.chatIn.msgi.value='@{$row['username']}@ ';\">Invia sussurro</a></td>";
					else
						$list[$cur] .= "<td class=\"dark_row\">&nbsp;</td>";

                                //Adding more controle for admins
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
						$list[$cur] .= "<td class=\"dark_row\"height=\"25\"><a class=\"dark_link\" href=\"index.php?act=usr_action&action=dice&user={$row['username']}&room={$row['position']}\">Tira un dado</a></td>";
					}

					if($room!='')
						$getstanza="&room";
						
					$list[$cur].="<td class=\"dark_row\"height=\"25\"><a class=\"dark_link\" href=\"index.php?act=memberlist&$action&user={$row['username']}$getstanza\">$new_state</a></td>";

				}
			
				$list[$cur] .= "</tr>";
			}
			
		}
		
		$body.= $list[0] . $list[1];
		
		$body .= "</table><p align=\"center\"><a class=\"dark_link\" href=\"#\" onClick=\"javascript: window.close();\">[Chiudi]</a></p></div>";

                $title='';
		if($room!='')
			$title = "Lista cittadini Online";
                else if(isset($_GET['dead']))
                        $title = "Lista deceduti";
		else
			$title = "Lista cittadini";
			
		$body .= '<script language="javascript" type="text/javascript">
				setTimeout("update()",60000);
				
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
		<LINK REL="SHORTCUT ICON" HREF="./favicon.ico">
		<style type="text/css">
			body{
				margin: 0;
				overflow: hidden;
			}
			
			#member{
				width: 450px;
				height: 500px;
				background-image:url('.$sfondo.');
			}
            
			#navigator{
                position: relative;
                top: 10px;
                width: 100%;
                text-align: center;
                font-weight: bold;
                color: black;
           }

           #navigator a{
                 color: black;
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
				//border-top: solid 2px gray;
				//border-bottom: solid 2px gray;
			}

			a:hover{
				color: red;
			}

			.bold_red{
				color: red;
				font-weight: bold;
			}

			#inner_member{
				width: 440px;
				height: 480px;
				overflow: auto;
			}

		
			
		</style>
		';
		
		
		
		echo $memberlist_style;
		
		echo '</head><body>
 			<div id="member">
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
