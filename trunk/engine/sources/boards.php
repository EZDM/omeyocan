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

	
	//This is the main function called by the index.php
	function board_main(){
		global $x7c, $x7s, $db, $prefix;


		udpate_unread();
		
		if(isset($_GET['newboard'])){
			create_board();
		}
		else if(isset($_GET['board'])){
			show_board($_GET['board']);
		}
		else if(isset($_GET['send'])){
			new_communication($_GET['send']);
		}
		else if(isset($_GET['delete'])){
			delete_message($_GET['delete']);
		}
		else if(isset($_GET['delboard'])){
			delete_board($_GET['delboard']);
		}
		else if(isset($_GET['readall'])){
			read_all();
		}
		else 
			board_list();
			
		
	}

	function read_all(){
		global $x7s, $db, $prefix;

		$db->DoQuery("DELETE FROM {$prefix}boardunread WHERE user='{$x7s->username}'");

		board_list();
	}
	
	function delete_board($id){
		global $print, $x7s, $db, $prefix;
		
		$body='';
		$head='';
		
		if(checkIfMaster()){
		
			if(isset($_GET['confirm'])){
				$db->DoQuery("DELETE FROM {$prefix}boardunread WHERE id IN
						(SELECT id FROM {$prefix}boardmsg WHERE board='$id')");
				
				$db->DoQuery("DELETE FROM {$prefix}boards WHERE id='$id'");
				$db->DoQuery("DELETE FROM {$prefix}boardmsg WHERE board='$id'");
				
				$body.='Board cancellata<br>
					<a href="./index.php?act=boards">Torna alle boards';
			}
			else{
				$body.="Sei sicuro di voler cancellare la board? <br>
					<input class=\"button\" type=\"button\" value=\"Si\" onClick=\"javascript: location.href= './index.php?act=boards&delboard=$id&confirm=1';\">
					<input class=\"button\" type=\"button\" value=\"No\" onClick=\" location.href='./index.php?act=boards'\";>";
			}
			
			$print->board_window("Cancellazione board",$body);
		}
		else{
			$print->board_window("Errore","Non sei autorizzato a visualizzare questa pagina");
		}
	}
	
	function create_board(){
		global $print, $x7s, $db, $prefix;
		$body ='';
		if(checkIfMaster()){
			if($_GET['newboard']){
				if(isset($_GET['name']) && $_GET['name']!="" && isset($_GET['group'])){
					if(isset($_GET['ronly']))
						$ronly=1;
					else
						$ronly=0;
					$db->DoQuery("INSERT INTO {$prefix}boards (name, user_group, readonly)  VALUES ('{$_GET['name']}','{$_GET['group']}','$ronly')");
					
					$body.="Board {$_GET['name']} creata correttamente<br>";
				}
				else
					$body.="Errore; mancano dei parametri";
			}
			
			$query = $db->DoQuery("SELECT usergroup FROM {$prefix}permissions");
			$options='';
			
			while($row = $db->Do_Fetch_Assoc($query)){
				$options.="<option value=\"{$row['usergroup']}\">{$row['usergroup']}</option>";
			}
			
			$head="Crea board";
			
			$body.="<form action=\"./index.php\" method=\"get\">

			<input type=\"hidden\" name=\"act\" value=\"boards\">
			<input type=\"hidden\" name=\"newboard\" value=\"1\">
			Nome board: <input class=\"text_input\" type=\"text\" name=\"name\"><br><br>
			Gruppo: <select class=\"button\" name=\"group\">
				$options
			</select><br>
			Read-only <input class=\"text_input\" type=\"checkbox\" name=\"ronly\"><br><br>
			<input type=\"submit\" value=\"Crea\" class=\"button\">
			<br><a href=\"index.php?act=boards\">Torna all'indice</a>";
			
			$print->board_window($head,$body);
			
			
		}
		else{
			$print->board_window("Errore","Non sei autorizzato a visualizzare questa pagina");
		}
	}
	
	function delete_message($msgid){
		global $print, $x7s, $db, $prefix;
		$indice=0;
		$msgid = $_GET['delete'];
		$query = $db->DoQuery("SELECT * FROM {$prefix}boardmsg WHERE id='$msgid'");
			
		$row = $db->Do_Fetch_Assoc($query);
		
		if(!$row){
			$body="La conversazione richiesta non esiste<br>
				<A HREF=\"javascript:javascript:history.go(-1)\"> Torna indietro</a>";
		
			$head="Errore";
			$print->board_window($head,$body,$indice);
			return;
		}
		
		$father=$row['father'];
		$bid=$row['board'];
		$user=$row['user'];
		
		if($father==0){		
			$query = $db->DoQuery("SELECT * FROM {$prefix}boards WHERE id='{$bid}'");
			$row = $db->Do_Fetch_Assoc($query);
		}
		else{
			$query = $db->DoQuery("SELECT * FROM {$prefix}boardmsg WHERE id='{$father}'");
			$row = $db->Do_Fetch_Assoc($query);
			$bid=$row['board'];
		
			$query = $db->DoQuery("SELECT * FROM {$prefix}boards WHERE id='{$bid}'");
			$row = $db->Do_Fetch_Assoc($query);
		}
		
		$board['name']=$row['name'];
		$board['id']=$row['id'];
		$board['user_group']=$row['user_group'];
		$board['readonly']=$row['readonly'];
				
		//Master can delete all messages... other can delete only their own messages
		if((checkAuth($board['id']) && !$board['readonly'] && $x7s->username == $user )|| checkIfMaster()){
			
			//All the conversation.. we are deleting the head message
			if($father==0)
				$db->DoQuery("DELETE FROM {$prefix}boardmsg WHERE id='$msgid' OR father='$msgid'");
			else
				$db->DoQuery("DELETE FROM {$prefix}boardmsg WHERE id='$msgid'");

			$db->DoQuery("DELETE FROM {$prefix}boardunread WHERE id='$msgid'");
		}
		else{
			$body="Operazione non permessa";
		
			$head="Errore";
			$print->board_window($head,$body,$indice);
			return;
		}
			
		$location='Location: ./index.php?act=boards&board='.$bid;
		header($location);
		return;
		
	}
	
	function new_communication($bid){
		global $print, $x7s, $db, $prefix;
		$indice=0;
		
		$body='';
		$query = $db->DoQuery("SELECT * FROM {$prefix}boards WHERE id='{$bid}'");
		$row = $db->Do_Fetch_Assoc($query);
		
		if(!$row){
			$body="La board richiesta non esiste
				<A HREF=\"javascript:javascript:history.go(-1)\"> Torna indietro</a>";
			
			$head="Errore";
			$print->board_window($head,$body,$indice);
			return;
		}
		
		$board['name']=$row['name'];
		$board['id']=$row['id'];
		$board['user_group']=$row['user_group'];
		$board['readonly']=$row['readonly'];
		
		$subject='';
		$msg='';
		$reply=0;
		
		
		// In this case the user has sent a message
		if(isset($_POST['subject']) && isset($_POST['body'])){
			if($_POST['body']==''){
				$body="Il messaggio non pu&ograve; essere vuoto
					<A HREF=\"javascript:javascript:history.go(-1)\"> Torna indietro</a>";
			
				$head="Errore";
				$print->board_window($head,$body,$indice);
				return;
			}
		
			$replies = 0;
			$father = 0;
			$msg = $_POST['body'];
			$toboard = $board['id'];
			
			if(isset($_GET['reply']) && $_GET['reply']!=0){
				$reply=$_GET['reply'];
				$query = $db->DoQuery("SELECT * FROM {$prefix}boardmsg WHERE id='$reply'");
				$row = $db->Do_Fetch_Assoc($query);
			
				if(!$row){
					$body="La conversazione non esiste
						<A HREF=\"javascript:javascript:history.go(-1)\"> Torna indietro</a>";
			
					$head="Errore";
					$print->board_window($head,$body,$indice);
					return;
				}
				
				$rawtext = $row['body'];
				$msg_id = $row['id'];
				$replies = $row['replies'];
				
				if($row['father'] == 0)
					$father = $reply;
				else
					$father = $row['father'];
				
				$replies++;
			
				$nb = offline_msg_split($rawtext);
				$subject = $nb[1];
				
				
				$_GET['message']=$msg_id;
			}
			else{
				if($_POST['subject']==''){
					$body="L'oggetto non pu&ograve; essere vuoto
						<A HREF=\"javascript:javascript:history.go(-1)\"> Torna indietro</a>";
			
					$head="Errore";
					$print->board_window($head,$body,$indice);
					return;
				}
				
				$subject = $_POST['subject'];

			}
			
			//Do the real send (master can always send and modify even if readonly)
			if((checkAuth($board['id']) && !$board['readonly']) || checkIfMaster()){
				$time = time();
				
				$msg = eregi_replace("\n","<Br>",$msg);
				$msg = eregi_replace("\\\\n","<Br>",$msg);
				
				$db->DoQuery("INSERT INTO {$prefix}boardmsg (father, user, body, board, time, replies)
						VALUES('$father','{$x7s->username}','$subject::$msg','$toboard','$time','0')");
				if(isset($_GET['reply']))
					$db->DoQuery("UPDATE {$prefix}boardmsg SET replies='$replies' WHERE id='$reply'");
			}
			else{
				$body="Operazione non permessa
					<A HREF=\"javascript:javascript:history.go(-1)\"> Torna indietro</a>";
			
				$head="Errore";
				$print->board_window($head,$body,$indice);
			return;
			}
		
			//We return to board or conversation
			$location='Location: ./index.php?act=boards&board='.$board['id'];
			if(isset($_GET['reply']) && $_GET['reply']!=0)
				$location .= '&message='.$_GET['reply'];
			
			header($location);
			return;
			
		}
		
		//In this case the user want to send a message
		
		if(!isset($_GET['reply'])){
			$head = "Nuova comunicazione su ".$board['name'];
		}
		else{
			$reply=$_GET['reply'];
			$query = $db->DoQuery("SELECT * FROM {$prefix}boardmsg WHERE id='$reply'");
			$row = $db->Do_Fetch_Assoc($query);
			
			if(!$row){
				$body="La conversazione non esiste
					<A HREF=\"javascript:javascript:history.go(-1)\"> Torna indietro</a>";
			
				$head="Errore";
				$print->board_window($head,$body,$indice);
				return;
			}
			$rawtext = $row['body'];
			
			$nb = offline_msg_split($rawtext);
			$msg = $nb[0];
			$subject = $nb[1];
			
			$head = "Risposta alla comunicazione: ".$subject;
			$msg = "\n\n-----\nMessaggio originale:\n\n".$msg;
		}	
		
		
		$body .= "<div align=\"center\">
			<form action=\"./index.php?act=boards&send=$board[id]&reply=$reply\" method=\"post\">";
			
		if(!isset($_GET['reply'])){
			$body .= "Oggetto: <input class=\"text_input\" size=40 type=\"text\" name=\"subject\" value=\"$subject\"><br>";
		}
		else
			$body .= "<input type=\"hidden\" name=\"subject\" value=\"$subject\">";
			
		$body .= "<textarea name=\"body\" class=\"text_input\" cols=\"40\" rows=\"20\">$msg</textarea><Br>
			<input type=\"submit\" value=\"Invia\" class=\"button\">
			</form></div>";
		
		$indice = indice_board();
		$print->board_window($head,$body, $indice);
	}
	
	//This function show the list of all board you are atuhorized to see
	function board_list(){
		global $print, $x7s, $db, $prefix;
		$head = "";
		$body='';
		
		$indice = indice_board();


		$q_new = $db->DoQuery("SELECT count(*) AS cnt FROM {$prefix}boardunread WHERE user='{$x7s->username}'");
		$new_msg = $db->Do_Fetch_Assoc($q_new);

		$body="<b>Ci sono $new_msg[cnt] messaggi nuovi</b>";
		
		$print->board_window($head,$body,$indice);
		
	}
	
	function indice_board(){
		global $print, $x7s, $db, $prefix;
		$body='	<table width=100% align="center" style="border-collapse: collapse;">';
		
		if(checkIfMaster()){
			$query = $db->DoQuery("SELECT * FROM {$prefix}boards ORDER BY id");	
		}
		else{
			$query = $db->DoQuery("SELECT * FROM {$prefix}boards WHERE user_group='{$x7s->user_group}' OR user_group='Cittadino'");
		}
		
		while($row = $db->Do_Fetch_Assoc($query)){
			$q_new = $db->DoQuery("SELECT count(*) AS cnt FROM {$prefix}boardmsg msg, {$prefix}boardunread un
								WHERE msg.id=un.id
 								AND board='$row[id]'
								AND un.user='{$x7s->username}'");
			$new_msg = $db->Do_Fetch_Assoc($q_new);

			$new_cnt='';
			if($new_msg['cnt']>0){
				$new_cnt="<b>($new_msg[cnt])</b>";
			}
			
			$body.='<tr class="board_cell"><td class="board_cell"><a href="./index.php?act=boards&board='.$row['id'].'"><b>'.$row['name'].' '.$new_cnt.' </b></a>';
			
			if(checkIfMaster()){
				$body.='<a href="./index.php?act=boards&delboard='.$row['id'].'">[Delete]</a>';
			}
			
			$body.="</td></tr>";
		
		}

		$body.='<tr><td align="center"><a href="./index.php?act=boards&readall">Segna come letti</a></td></tr>';
		
		if(checkIfMaster()){
			$body.='<tr><td align="center"><a href="./index.php?act=boards&newboard=0">Nuova board</a></td></tr>';
		}
		
		$body.="<tr><td align=\"center\"><a href=\"#\" onClick=\"javascript: window.close();\">[Chiudi]</a></td></tr>
		</table>";
		
		return $body;
	
	}
	
	//This function check if you want to see all the messages of a board or a single conversation
	//and also check user permission
	function show_board($bid){
		global $print, $x7s, $db, $prefix;
		$indice=0;
		
		$query = $db->DoQuery("SELECT * FROM {$prefix}boards WHERE id='{$bid}'");
		$row = $db->Do_Fetch_Assoc($query);
		
		if(!$row){
			$body="La board richiesta non esiste
				<A HREF=\"javascript:javascript:history.go(-1)\"> Torna indietro</a>";
			
			$head="Errore";
			$print->board_window($head,$body,$indice);
			return;
		}
		
		$board['name']=$row['name'];
		$board['id']=$row['id'];
		$board['user_group']=$row['user_group'];
		$board['readonly']=$row['readonly'];
		
		
		$head="Board ".$board['name'];
		
		

		if(!checkAuth($bid)){
			$body="Non sei autorizzato a vedere questa board
				<A HREF=\"javascript:javascript:history.go(-1)\"> Torna indietro</a>";
			
			$print->board_window($head,$body,$indice);
			return;
		}
		
		if(!isset($_GET['message'])){
			show_all_messages($board);
		}
		else{
			show_single_message($_GET['message'],$board);
		}
		
		
	}
	
	
	//This function show all messages of a board
	function show_all_messages($board){
		global $print, $x7s, $db, $prefix;

		$head="Board ".$board['name'];
		$body='';
		$indice = indice_board();
		
		$maxmsg=10;
		$navigator='<p style="text-align: center;">';;
		
		if(isset($_GET['startfrom'])){
			$limit=$_GET['startfrom'];
		}
		else
			$limit=0;
		
		$query = $db->DoQuery("SELECT count(*) AS total FROM {$prefix}boardmsg WHERE board='{$board['id']}' AND father='0'");
		$row = $db->Do_Fetch_Assoc($query);
		$total = $row['total'];
		
		if($total > $maxmsg){
			$i=0;
			while($total > 0){
				if((isset($_GET['startfrom']) && $_GET['startfrom'] == $i) || (!isset($_GET['startfrom']) && $i == 0))
					$navigator .= "<a href=\"index.php?act=boards&board=$board[id]&startfrom=$i\"><b>[".($i+1)."]</b></a> ";
				else
					$navigator .= "<a href=\"index.php?act=boards&board=$board[id]&startfrom=$i\">".($i+1)."</a> ";
				$i++;
				$total -= $maxmsg;
				
			}
			$navigator.="</p>";
		}
		
			
		$limit_min = $limit * $maxmsg;
		$limit_max = (($limit+1) * $maxmsg);		
		
		$query = $db->DoQuery("SELECT * FROM {$prefix}boardmsg WHERE board='{$board['id']}' AND father='0' ORDER BY time DESC LIMIT $limit_min, $limit_max");

		
		if(!$board['readonly'] || checkIfMaster()){
			$body .="<a href=./index.php?act=boards&send=".$board['id'].">Nuova comunicazione</a><br>";
		}
		
		$body.=$navigator;
		$unreads = get_unread();
		while($row = $db->Do_Fetch_Assoc($query)){
			$q_new = $db->DoQuery("SELECT count(*) AS cnt FROM {$prefix}boardmsg msg, {$prefix}boardunread un
						WHERE	msg.id=un.id
						AND un.user='{$x7s->username}'
						AND board='{$board['id']}' AND father='$row[id]'");
			$new_replies = $db->Do_Fetch_Assoc($q_new);
			
			$unread='';
			if(isset($unreads[$row['id']]))
				$unread = "<b>(Nuovo) </b>";

			if($new_replies['cnt']>0){
				$unread .= "<b>(Nuove repliche: $new_replies[cnt])</b>";
			}
			
			$nb = offline_msg_split($row['body']);
			$msg = $nb[0];
			$object = $nb[1];
			$msgid=$row['id'];
			$user=$row['user'];
			
			$body.="<p>".$row['user']."<br><a href=./index.php?act=boards&board=".$board['id']."&message=".$row['id'].">
				 <b>".$object."</b> ".$unread."</a>";
				
			if($user == $x7s->username || checkIfMaster())
				$body.=" <a href=./index.php?act=boards&delete=$msgid>Delete</a>";
			
			$body.="</p><hr>";
		
		}
		$body.=$navigator;
		if(!$board['readonly'] || checkIfMaster()){
			$body .="<br><br><a href=./index.php?act=boards&send=".$board['id'].">Nuova comunicazione</a><br>";
		}
		
		//$body.="<a href=\"index.php?act=boards\">Torna all'indice</a>";
		
		$print->board_window($head,$body,$indice);
		
	}
	
	
	//This function show a conversation
	function show_single_message($id, $board){
		global $print, $x7s, $db, $prefix;
		$body='';
		$indice=indice_board();
		$maxmsg=5;
		$navigator='';
		
		if(isset($_GET['startfrom'])){
			$limit=$_GET['startfrom'];
		}
		else
			$limit=0;
		
		$query = $db->DoQuery("SELECT count(*) AS total FROM {$prefix}boardmsg WHERE id='{$id}' OR father='{$id}'");
		$row = $db->Do_Fetch_Assoc($query);
		$total = $row['total'];

		
		
		if($total > $maxmsg){
			$i=0;
			while($total > 0){
				if((isset($_GET['startfrom']) && $_GET['startfrom'] == $i) || (!isset($_GET['startfrom']) && $i == 0))
					$navigator .= "<a href=\"index.php?act=boards&board=$board[id]&message=$id&startfrom=$i\"><b>[".($i+1)."]</b></a> ";
				else
					$navigator .= "<a href=\"index.php?act=boards&board=$board[id]&message=$id&startfrom=$i\">".($i+1)."</a> ";
				$i++;
				$total -= $maxmsg;
				
			}
			$navigator.="<br>";
		}
		
			
		$limit_min = $limit * $maxmsg;
		$limit_max = (($limit+1) * $maxmsg);
		
		$query = $db->DoQuery("SELECT 	b.id AS id,
						b.father AS father,
						b.user AS user,
						b.body AS body,
						b.board AS board,
						b.time AS time,
						b.replies AS replies,
						u.avatar AS avatar
					FROM {$prefix}boardmsg b, {$prefix}users u
					WHERE	b.user = u.username AND
						(b.id='{$id}' OR father='{$id}')
						ORDER BY time LIMIT $limit_min, $maxmsg");
		
		//Head message
		$row = $db->Do_Fetch_Assoc($query);
		
		$nb = offline_msg_split($row['body']);
		$msg = $nb[0];
		$object = $nb[1];

		$unread='';
		$unreads = get_unread();
		if(isset($unreads[$row['id']])){
			$unread = "<b>(Nuovo)</b>";
			$db->DoQuery("DELETE FROM {$prefix}boardunread WHERE id='{$row['id']}' AND user='{$x7s->username}'");
		}

		if(!$board['readonly'] || checkIfMaster()){
			$body .="<a href=./index.php?act=boards&send=".$board['id']."&reply=".$id.">Replica</a><br>";
		}
		$body.="<a href=\"index.php?act=boards&board=".$board['id']."\">Torna alla board</a><br>";
		
		$body .= $navigator;
		$head="Board ".$board['name']." messaggio: ".$object;

		$body .="<table width=\"100%\" cellspacing=0>";

		$avatar='';
		if($row['avatar']!=''){
			$avatar="<br><img src=\"$row[avatar]\" width=\"100\" height=\"100\">";
		}
		
		$body.="<tr><td class=\"msg_row\"><b>Utente:</b> ".$row['user'].$avatar."</td><td class=\"msg_row\"><b>Oggetto:</b> ".$object." ".$unread;
		$msgid=$row['id'];
		$user=$row['user'];
			
		if(($user == $x7s->username && !$board['readonly']) || checkIfMaster()){
				$body .=" <a href=./index.php?act=boards&delete=".$msgid.">[Delete]</a>";
		}
		
		$body.= "<br><br>".$msg."<br><br><br><br></td></tr>\n";
		
		
		while($row = $db->Do_Fetch_Assoc($query)){
			$avatar='';
			if($row['avatar']!=''){
				$avatar="<br><img src=\"$row[avatar]\" width=\"100\" height=\"100\">";
			}
			
			$unread='';
			if(isset($unreads[$row['id']])){
				$unread = "<b>(Nuovo)</b>";
				$db->DoQuery("DELETE FROM {$prefix}boardunread WHERE id='{$row['id']}' AND user='{$x7s->username}'");
			}
			
			$nb = offline_msg_split($row['body']);
			$msg = $nb[0];
			$object = $nb[1];
			
			$msgid=$row['id'];
			$user=$row['user'];
			
			$body.="<tr><td class=\"msg_row\"><b>Utente:</b> ".$row['user'].$avatar."</td><td class=\"msg_row\"><b>Oggetto:</b> ".$object." ".$unread;
			if(($user == $x7s->username && !$board['readonly']) || checkIfMaster()){
				$body .=" <a href=./index.php?act=boards&delete=".$msgid.">[Delete]</a>";
			}
			
			$body.= "<br><br>".$msg."<br><br><br><br></td></tr>\n";
		}

		$body .= "</table>";
		
		if(!$board['readonly'] || checkIfMaster()){
			$body .="<br><br><a href=./index.php?act=boards&send=".$board['id']."&reply=".$id.">Replica</a><br>";
		}
		
		$body.="<a href=\"index.php?act=boards&board=".$board['id']."\">Torna alla board</a><br>";
		
		$body.=$navigator;
		
		$print->board_window($head,$body,$indice);
		
	}
	
	
	//This function returns true if the user is an admin or a master
	function checkIfMaster(){
		global $x7s, $x7c;
		
		$value = $x7c->permissions['admin_panic'];
		
		return $value;
	}
	
	//This function return true if the user is allowed to see the board
	function checkAuth($bid){
		global $x7s, $db, $prefix;
		
		$query = $db->DoQuery("
			SELECT user_group
			FROM  {$prefix}boards
			WHERE id='{$bid}'
		");
		
		$row = $db->Do_Fetch_Assoc($query);
		
		if(checkIfMaster())
			return true;		
		else if($row['user_group'] == $x7s->user_group || $row['user_group'] == 'Cittadino')
			return true;
		else
			return false;
	}
	
	function offline_msg_split($body){
		// 0 is the body
		$return[0] = preg_replace("/^(.+?)::/i","",$body);

		// 1 is the subject
		if(preg_match("/^(.+?)::/i",$body,$match))
			$return[1] = $match[1];
		else
			$return[1] = '';

		return $return;
	}

	function udpate_unread(){
		global $print, $x7s, $db, $prefix;

		$query = $db->DoQuery("SELECT last_board_id FROM {$prefix}users WHERE username='{$x7s->username}'");
		$row = $db->Do_Fetch_Assoc($query);
		$last_read = $row['last_board_id'];
		
		//We create the list of new messages
		if(checkIfMaster()){
			$query = $db->DoQuery("SELECT id FROM {$prefix}boardmsg msg WHERE id>'$last_read' AND user<>'{$x7s->username}'");
		}
		else{
			$query = $db->DoQuery("SELECT msg.id FROM {$prefix}boardmsg msg, {$prefix}boards brd
 					WHERE msg.board=brd.id
 					AND (user_group='{$x7s->user_group}' OR user_group='Cittadino')
 					AND msg.id>'$last_read' AND user<>'{$x7s->username}'");
		}
		
		$lastid=0;
		while($new_msg=$db->Do_Fetch_Assoc($query)){
			if($lastid<$new_msg['id'])
				$lastid=$new_msg['id'];
				
			$db->DoQuery("INSERT INTO {$prefix}boardunread (id, user) VALUES('$new_msg[id]','{$x7s->username}')");
		}

		$db->DoQuery("UPDATE {$prefix}users SET last_board_id=(SELECT MAX(id) FROM {$prefix}boardmsg) WHERE username='{$x7s->username}'");
	}

	function get_unread(){
		global $print, $x7s, $db, $prefix;
		$unreads = array();
		$query = $db->DoQuery("SELECT id FROM {$prefix}boardunread WHERE user='{$x7s->username}'");
		while($row = $db->Do_Fetch_Assoc($query)){
			$unreads[$row['id']]=1;
		}

		return $unreads;
		
	}

?>