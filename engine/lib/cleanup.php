<?PHP
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
	// Removes old records that have expired
	// Does not handle expiration online records, they are handled by online.php
	
	function cleanup_banned(){
		global $db,$prefix;
		$time = time();
		$db->DoQuery("DELETE FROM {$prefix}banned WHERE $time>starttime+endtime AND endtime<>0");
	}
	
	function cleanup_messages(){
		global $db,$prefix,$x7c;
		if($x7c->settings['expire_messages'] != 0){
			//Here I keep max_room_messages for type 1 (normal) and 14 (mastering) messages
			$exptime = $x7c->settings['max_room_messages'];
			$room_query = $db->DoQuery("
				SELECT room, count(*) AS num 
				FROM {$prefix}messages 
				WHERE (type='1' OR type='14') 
				GROUP BY room
				HAVING count(*) > $exptime");
			
			while($room = $db->Do_Fetch_Assoc($room_query)){
	                        	$toDelete = $room['num'] - $exptime;
                        		$db->DoQuery("DELETE FROM {$prefix}messages 
						WHERE (type='1' OR type='14') 
						AND room='$room[room]' ORDER BY time LIMIT $toDelete");
			}
			
			//Here I delete all other messages
			$exptime = time()-$x7c->settings['expire_messages'];
			$db->DoQuery("DELETE FROM {$prefix}messages WHERE time<$exptime AND type<>'6' AND type<>'1' AND type<>'14'");
		}
	}
	
	// Delete old rooms, only needs done on room list page :)
	function cleanup_rooms(){
		global $x7c, $db, $prefix;
		if($x7c->settings['expire_rooms'] != 0 && $x7c->settings['single_room_mode'] == ""){
			$exptime = time()-$x7c->settings['expire_rooms'];
			$db->DoQuery("DELETE FROM {$prefix}rooms WHERE time<$exptime AND time<>0");
		}
	}
	
	// Cleanup Accounts
	function cleanup_guests(){
		global $db, $prefix, $x7c;
		if($x7c->settings['expire_guests'] != 0){
			$exptime = time()-$x7c->settings['expire_guests'];
			$db->DoQuery("DELETE FROM {$prefix}users WHERE time<$exptime AND user_group='{$x7c->settings['usergroup_guest']}'");
		}
	}
	
	// Removes old guest logs
	function cleanup_guest_logs($guest){
		global $x7c;
		if(is_dir("{$x7c->settings['logs_path']}/$guest")){
			$diro = dir("{$x7c->settings['logs_path']}/$guest");
			while($file = $diro->read()){
				if($file != "." && $file != "..")
					unlink("{$x7c->settings['logs_path']}/$guest/$file");
			}
			rmdir("{$x7c->settings['logs_path']}/$guest");
		}
	}

	// remove temporary objects
	function cleanup_temp_objects() {
		include_once('./lib/shop_lib.php');
		global $db, $prefix, $shopper;
		$time = time();

		$query = $db->DoQuery("SELECT * FROM {$prefix}temp_obj WHERE expire_time < $time");
		while ($row = $db->Do_Fetch_Assoc($query)) {
			if ($row['shop_return']) {
				$db->DoQuery("UPDATE {$prefix}objects SET owner = '$shopper' 
						WHERE id = '$row[id]'");
			}
			else {
				$db->DoQuery("DELETE FROM {$prefix}objects WHERE id = '$row[id]'");
			}
		}

		$db->DoQuery("DELETE FROM {$prefix}temp_obj WHERE expire_time < $time");
	}
	
	// Updates the timestamps on your current room and username
	function prevent_cleanup(){
		global $db, $prefix, $x7s;
		$time = time();
		if(@$_GET['room'] != "")
			$db->DoQuery("UPDATE {$prefix}rooms SET time='$time' WHERE name='$_GET[room]' AND time<>0");
		if(@$x7s->username != "")
			$db->DoQuery("UPDATE {$prefix}users SET time='$time', exp_warn='0' WHERE username='$x7s->username'");
	}

	function resurgo(){
		global $db, $prefix;
		$time = time();
		$query = $db->DoQuery("SELECT username FROM {$prefix}users WHERE resurgo<'$time' AND info='Morto'");

		while($row = $db->Do_Fetch_Assoc($query)){
			include_once("./lib/sheet_lib.php");
			toggle_death($row['username'], 0);
		}
	}
	
	function delete_user($user){
		include_once('./lib/shop_lib.php');
		global $db, $prefix, $shopper;
		
		// Returning money
		$money = get_total_user_money($user, false);
		if ($money > 0) {
			pay($money, $user, $shopper, false, false);
		}

		$query = $db->DoQuery("SELECT id FROM {$prefix}users WHERE username='$user'");
		while($row = $db->Do_Fetch_Assoc($query)){
			$db->DoQuery("DELETE FROM {$prefix}banned WHERE id='$row[id]'");
		}
					
		$db->DoQuery("DELETE FROM {$prefix}banned WHERE user_ip_email='$user'");
		$db->DoQuery("DELETE FROM {$prefix}groups WHERE username='$user'");
		$db->DoQuery("DELETE FROM {$prefix}users WHERE username='$user'");
		// Delete bandwidth info
		$db->DoQuery("DELETE FROM {$prefix}bandwidth WHERE user='$user'");
		// Delete character sheet
		$db->DoQuery("DELETE FROM {$prefix}userability WHERE username='$user'");
		$db->DoQuery("DELETE FROM {$prefix}usercharact WHERE username='$user'");

		$db->DoQuery("DELETE FROM {$prefix}objects WHERE owner='$user'");
		$db->DoQuery("DELETE FROM {$prefix}boardmsg WHERE user='$user'");
		$db->DoQuery("DELETE FROM {$prefix}boardunread WHERE user='$user'");
		$db->DoQuery("DELETE FROM {$prefix}messages WHERE user='$user'
			OR room='$user'");
		// Clean up logs
		cleanup_guest_logs($user);	
	}
	
	
	function cleanup_inactive_users(){
		global $db, $prefix, $x7c;
		
		$warn_list='';
		$del_list='';
		$del_day='';
		$time = time();

		// We do this check once per day
		$now = date("d/m/Y");

		// We reset at 5 in the morning
		$before = date("d/m/Y", $x7c->settings['last_cleanup'] + 5*3600); 
		if($now == $before){
			return;
		}

		$db->DoQuery("DELETE FROM {$prefix}punish");
		$query_daily = $db->DoQuery("SELECT username, daily_post, last_punish, 
				daily_lotus
				FROM {$prefix}users 
				WHERE daily_post > 0
				AND base_group = '{$x7c->settings['usergroup_default']}'
				ORDER BY username");

		while ($row = $db->Do_Fetch_Assoc($query_daily)) {
			$db->DoQuery("INSERT INTO {$prefix}punish (username, daily_post) VALUES 
					('{$row['username']}', '{$row['daily_post']}')");
		}

		$db->DoQuery("DELETE FROM {$prefix}roomposts");
		$query_daily = $db->DoQuery("SELECT name, daily_post
				FROM {$prefix}rooms 
				WHERE daily_post > 0
				ORDER BY name");
		
		while ($row = $db->Do_Fetch_Assoc($query_daily)) {
			$db->DoQuery("INSERT INTO {$prefix}roomposts (name, daily_post) VALUES 
					('{$row['name']}', '{$row['daily_post']}')");
		}

		$db->DoQuery("UPDATE {$prefix}settings SET setting = '$time' WHERE variable = 'last_cleanup'");

		// Reset daily counters
		$db->DoQuery("UPDATE {$prefix}users SET daily_post = 0, daily_lotus = 0");
		$db->DoQuery("UPDATE {$prefix}rooms SET daily_post = 0");
		$db->DoQuery("UPDATE {$prefix}objects SET daily_use = 0");

		
		//First we send wanring to old pg
		$exptime = time()-$x7c->settings['pg_expire_warn'];
		$query = $db->DoQuery("SELECT username, email FROM {$prefix}users WHERE exp_warn='0' AND time<'$exptime' AND frozen = 0 ORDER BY username");
		
		
		while($row = $db->Do_Fetch_Assoc($query)){
			include_once('./lib/message.php');
			$db->DoQuery("UPDATE {$prefix}users SET exp_warn='1' WHERE username='$row[username]'");
			$warn_list.=$row['username']."\n";
			
			$warn_day = $x7c->settings['pg_expire_warn']/(24*3600);
			$del_day = ($x7c->settings['pg_expire']-$x7c->settings['pg_expire_warn'])/(24*3600);
			
			$obj="Avviso imminente cancellazione account";
			$body="Attenzione, l\'account $row[username] risulta inativo da $warn_day giorni. Se non to colleghi entro $del_day giorni sara\' cancellato senza ulteriore avviso";
			
			mail($row['email'],$obj,"$body\r\n","From: {$x7c->settings['site_name']} <{$x7c->settings['admin_email']}>\r\n" ."Reply-To: {$x7c->settings['admin_email']}\r\n" ."X-Mailer: PHP/" . phpversion());
			
			$body = parse_message($body);
		}
		
		//First we send wanring to old pg
		$exptime = time()-$x7c->settings['pg_expire'];
		$query = $db->DoQuery("SELECT username, email FROM {$prefix}users WHERE time<'$exptime' AND frozen = 0 ORDER BY username");
		
		
		while($row = $db->Do_Fetch_Assoc($query)){
			delete_user($row['username']);
			$del_list.=$row['username']."\n";	
		}
		
		if($warn_list!='' || $del_list!=''){
			
			$admin=$x7c->settings['usergroup_admin'];
			$query = $db->DoQuery("SELECT username FROM {$prefix}groups WHERE usergroup='$admin'");
			include_once('./lib/message.php');
			$obj = "Avviso cancellazione pg";
			
			$body='';
			
			if($warn_list!=''){
				$body.="I seguenti account saranno automaticamente rimossi tra $del_day giorni\n".$warn_list."\n\n";
			}
			
			if($del_list!=''){
				$body.="I seguenti account sono stati automaticamente rimossi\n".$del_list."\n\n";
			}
			
			$body= parse_message($body);
			while($row = $db->Do_Fetch_Assoc($query)){
				send_offline_msg($row['username'],$obj,$body,"Buio");	
			}
		}
	}
	
	
?>
