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
	
	//This function take trace of double login for users
	function double_login($room){
		global $x7s, $x7c, $prefix, $db;
		
		$msg = " <b>DOPPIO LOGIN:</b> l\'utente <b>$x7s->username </b> cerca di aprire una seconda finestra in $room mentre sta in <br>";
		$query = $db->DoQuery("SELECT room,ip FROM {$prefix}online WHERE name='$x7s->username'");
		
		while($row = $db->Do_Fetch_assoc($query)){
			$msg .= "{$row['room']} con IP: {$row['ip']}<br>";
		}
		
		$time = time();
		$db->DoQuery("INSERT INTO {$prefix}logs (user, msg, time) VALUES ('{$x7s->username}','$msg','$time')");
		
		
	}
	
	function sheet_modification($modified, $page){
		global $x7s, $x7c, $prefix, $db;
		
		if($page=='')
			$page="main";
		
		$msg = " <b>MODIFICA SCHEDA</b>: l\'utente <b>{$x7s->username} </b> modifica la pagina <b>$page</b> dell\'utente <b>$modified</b> <br>";
		
		$time = time();
		$db->DoQuery("INSERT INTO {$prefix}logs (user, msg, time) VALUES ('{$x7s->username}','$msg','$time')");
		
		
	}
	
	function object_assignement($owner, $obj){
		global $x7s, $x7c, $prefix, $db;
		
		$msg = " <b>ASSEGNA OGGETTO</b>: l\'utente <b>{$x7s->username} </b> assegna l\'oggetto <b>$obj</b> all\'utente <b>$owner</b> <br>";
		
		$time = time();
		$db->DoQuery("INSERT INTO {$prefix}logs (user, msg, time) VALUES ('{$x7s->username}','$msg','$time')");
		include_once("./lib/message.php");
		send_offline_msg($owner,"Hai ricevuto un oggetto","Hai ricevuto l\'oggetto: $obj");
		
		
	}
	
	function object_moves($owner,$old, $obj){
		global $x7s, $x7c, $prefix, $db;
		
		$msg = " <b>SPOSTA OGGETTO</b>: l\'utente <b>{$x7s->username} </b> assegna l\'oggetto <b>$obj</b> dall\'utente <b>$old</b> all\'utente <b>$owner</b><br>";
		
		$time = time();
		$db->DoQuery("INSERT INTO {$prefix}logs (user, msg, time) VALUES ('{$x7s->username}','$msg','$time')");
		include_once("./lib/message.php");
		send_offline_msg($owner,"Hai ricevuto un oggetto","Hai ricevuto l\'oggetto: $obj");
		
		
	}
	
	function object_uses($owner, $obj, $use){
		global $x7s, $x7c, $prefix, $db;
		$query = $db->DoQuery("SELECT name FROM {$prefix}objects WHERE id='$obj'");
		if($row = $db->Do_Fetch_assoc($query))	
			$msg = " <b>CAMBIO USI OGGETTO</b>: l\'utente <b>{$x7s->username} </b> cambia l\'oggetto <b>$row[name]</b> dell\'utente <b>$owner</b> assegnando <b>$use</b> usi<br>";
		else
			$msg = " <b>CAMBIO USI OGGETTO</b>: l\'utente <b>{$x7s->username} </b> cambia l\'oggetto <b>$obj</b> dell\'utente <b>$owner</b> assegnando <b>$use</b> usi<br>";
		
		$time = time();
		$db->DoQuery("INSERT INTO {$prefix}logs (user, msg, time) VALUES ('{$x7s->username}','$msg','$time')");
		
		
	}
	
	function object_usage($owner, $obj, $use){
		global $x7s, $x7c, $prefix, $db;
		$query = $db->DoQuery("SELECT name FROM {$prefix}objects WHERE id='$obj'");
		if($row = $db->Do_Fetch_assoc($query))
			$msg = " <b>UTILIZZO OGGETTO</b>: l\'utente <b>{$x7s->username} </b> utilizza l\'oggetto <b>$row[name]</b> dell\'utente <b>$owner</b> assegnando <b>$use</b> usi<br>";
		else	
			$msg = " <b>UTILIZZO OGGETTO</b>: l\'utente <b>{$x7s->username} </b> utilizza l\'oggetto <b>$obj</b> dell\'utente <b>$owner</b> assegnando <b>$use</b> usi<br>";

		if(isset($_GET['room']))
			$msg .= " <b>Stanza:</b> {$_GET['room']}";
		if(isset($_POST['msg']))
			$msg .= " <b>Messaggio:</b> {$_POST['msg']}";

		$time = time();
		$db->DoQuery("INSERT INTO {$prefix}logs (user, msg, time) VALUES ('{$x7s->username}','$msg','$time')");
		
		
	}

?>
