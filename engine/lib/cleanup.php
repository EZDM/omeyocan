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
			$room_query = $db->DoQuery("SELECT name FROM ${prefix}rooms");
			
			while($room = $db->Do_Fetch_Row($room_query)){
				$query = $db->DoQuery("SELECT count(*) AS num FROM {$prefix}messages WHERE (type='1' OR type='14') AND room='$room[0]'");
				$row = $db->Do_Fetch_Assoc($query);
			
				if($row['num'] > $exptime){
	                        	$toDelete = $row['num'] - $exptime;
                        		$db->DoQuery("DELETE FROM {$prefix}messages WHERE (type='1' OR type='14') AND room='$room[0]' ORDER BY time LIMIT $toDelete");
				}
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
	
	// Updates the timestamps on your current room and username
	function prevent_cleanup(){
		global $db, $prefix, $x7s;
		$time = time();
		if(@$_GET['room'] != "")
			$db->DoQuery("UPDATE {$prefix}rooms SET time='$time' WHERE name='$_GET[room]' AND time<>0");
		if(@$x7s->username != "")
			$db->DoQuery("UPDATE {$prefix}users SET time='$time' WHERE username='$x7s->username'");
	}

	function resurgo(){
              global $db, $prefix;
              $time = time();
              $db->DoQuery("UPDATE {$prefix}users u SET resurgo='0', talk='1', info=(SELECT 2*value FROM {$prefix}usercharact uc WHERE u.username=uc.username AND charact_id='rob') WHERE resurgo<'$time' AND info='Morto'");
              
	}
	
?>
