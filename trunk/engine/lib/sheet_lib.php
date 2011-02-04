<?PHP

    function toggle_heal($pg, $heal){
        global $db, $prefix;
			$errore='';
			$time=time();
			$db->DoQuery("UPDATE {$prefix}users SET heal_time='$time', autoheal='$heal' WHERE username='$pg'");
    }
    
		function get_base_group($user){
    	global $db, $prefix, $x7c; 
    	$query = $db->DoQuery("SELECT base_group FROM {$prefix}users WHERE username = '$user'");
    	
    	$row = $db->Do_Fetch_Assoc($query);
			return $row['base_group'];
		}
    
    function can_join($user, $gremios){
    	global $db, $prefix, $x7c; 
    	$query = $db->DoQuery("SELECT gremios FROM {$prefix}permissions WHERE usergroup = '$gremios'");
    	
    	$row = $db->Do_Fetch_Assoc($query);
    	
    	if(!$row['gremios'])
    		return true;
    		
    	else{
    		$query = $db->DoQuery("SELECT user_group,base_group FROM {$prefix}users WHERE username='$user'");
    		$row = $db->Do_Fetch_Assoc($query);
    		if(	$gremios != $row['base_group'] &&
    			$row['user_group']!=$row['base_group'] &&
    			$row['user_group'] != $gremios){
    			return false; 
    			}
    		else
    			return true;
    	}
    }

	function toggle_death($pg, $kill, $resurgo=true){
		global $db, $prefix, $x7c;
		$errore='';
		if($kill){
			if ($resurgo) {
				//Morto per 5 giorni
				$death_day=$x7c->settings['death_days'];
				$resurgo = time() + $death_day*24*3600;
				$db->DoQuery("UPDATE {$prefix}users SET talk='0', info='Morto', autoheal='0', resurgo='$resurgo' WHERE username='$pg'");
			} 
			else {
				$db->DoQuery("UPDATE {$prefix}users SET talk='0', info='-11', autoheal='0' WHERE username='$pg'");
			}
			$query = $db->DoQuery("SELECT count(*) AS cnt FROM {$prefix}userability WHERE username='$pg' AND value>'0'");
			$row = $db->Do_Fetch_Assoc($query);
			$cnt = $row['cnt'];

			if($cnt > 0){

				$query = $db->DoQuery("SELECT * FROM {$prefix}userability u, {$prefix}ability a WHERE u.ability_id=a.id AND username='$pg' AND value>'0' ORDER BY ability_id");
				srand(time()+microtime());
				$roll=floor(rand(1,$cnt));
				$i=0;
				$row=0;

				while($i<$roll){
					$row = $db->Do_Fetch_Assoc($query);
					$i++;
				}
				$new_value=$row['value']-1;


				$db->DoQuery("UPDATE {$prefix}userability SET value='$new_value' WHERE ability_id='$row[ability_id]' AND username='$pg'");
				$db->DoQuery("UPDATE {$prefix}users SET lvl=lvl-{$row['value']} WHERE username='$pg'");

				include_once("./lib/message.php");
				send_offline_msg($pg,"Morte","Sei morto e hai perso un punto in $row[name]");
				$errore = "Ucciso e perso un punto in $row[name]";
			}
			else{
				$errore = "Ucciso, ma non aveva punti abilita' residui";
			}

		}
		else{
			$errore = "Resuscitato";
			$db->DoQuery("UPDATE {$prefix}users u SET resurgo='0', autoheal='1', talk='1', info=(SELECT 2*value FROM {$prefix}usercharact uc WHERE uc.username='$pg' AND charact_id='rob') WHERE username='$pg'");

			$query = $db->DoQuery("SELECT base_group FROM {$prefix}users 
					WHERE username='$pg'");
			$row_user = $db->Do_Fetch_Assoc($query);

			if(!$row_user)
				die("User not in database: should not happen");

			include_once("./lib/message.php");

			if ($row_user['base_group'] == $x7c->settings['usergroup_default'])
				send_offline_msg($pg, "Resurrezione", 
						$x7c->settings['citizen_death_mail']);
			else
				send_offline_msg($pg, "Resurrezione", 
						$x7c->settings['uncitizen_death_mail']);
		}

		return $errore;
	}


	
    function join_corp($pg, $corp, $from_sheet=0){
        global $db, $prefix, $x7s, $x7c, $x7p;
        
        if(!can_join($pg, $corp))
        	return "$pg non puo' far parte di $corp";
        
        $query = $db->DoQuery("SELECT id FROM {$prefix}ability WHERE corp='$corp'");

        if(!$from_sheet){                        
	        while($row = $db->Do_Fetch_Assoc($query)){
	            $db->DoQuery("INSERT INTO {$prefix}userability (ability_id, username, value) VALUES('$row[id]', '$pg', '0')
	                          ON DUPLICATE KEY UPDATE username=username, ability_id=ability_id");
	        }
        }

        if($from_sheet){
                
                $perm_query=$db->DoQuery("SELECT admin_panic FROM {$prefix}permissions WHERE usergroup='$corp'");
                $row_perm = $db->Do_Fetch_Assoc($perm_query);

                if($row_perm==null)
                        return "Gruppo $corp non esistente";

                //Only admin can make admin and masters
                if(!in_array($x7c->settings['usergroup_admin'], $x7p->profile['usergroup']) && $row_perm['admin_panic']==1){
                        return "Non sei autorizzato a gestire questo gremios";
                }
                
        		while($row = $db->Do_Fetch_Assoc($query)){
	            	$db->DoQuery("INSERT INTO {$prefix}userability (ability_id, username, value) VALUES('$row[id]', '$pg', '0')
	                          ON DUPLICATE KEY UPDATE username=username, ability_id=ability_id");
	        	}
               
        }
                
        $gif_query = $db->DoQuery("SELECT logo FROM {$prefix}permissions WHERE usergroup='$corp'");
        $row=$db->Do_Fetch_Assoc($gif_query);
        $gif=$row['logo'];
        $db->DoQuery("UPDATE {$prefix}users SET bio='$gif' WHERE username='$pg'");
        
        if(is_gremios($corp)){
						$base_group = get_base_group($pg);
        		$db->DoQuery("DELETE FROM {$prefix}groups WHERE username='$pg' AND usergroup='{$base_group}'");
            $db->DoQuery("UPDATE {$prefix}users SET user_group='$corp', bio='$gif' WHERE username='$pg'");
        }
        
        $db->DoQuery("INSERT INTO {$prefix}groups (usergroup, username, corp_master) VALUES('$corp', '$pg','0')
        					ON DUPLICATE KEY UPDATE usergroup=usergroup, username=username");
    }

    function leave_corp($target, $corp){
        global $db, $prefix, $x7s, $x7c, $x7p;
        $query = $db->DoQuery("SELECT usergroup FROM {$prefix}groups WHERE username='$target' AND usergroup='$corp'");
        $row = $db->Do_Fetch_Assoc($query);
				$base_group = get_base_group($target);

        //We can remove only members that belong to our corp
        if(in_array($corp, $x7p->profile['usergroup']) || checkIfMaster()){
                $perm_query=$db->DoQuery("SELECT admin_panic FROM {$prefix}permissions WHERE usergroup='$row[usergroup]'");
                $row_perm = $db->Do_Fetch_Assoc($perm_query);

                if($row_perm==null)
                        return "Gruppo $corp non esistente";

                //Only admin can make admin and masters
                if(!in_array($x7c->settings['usergroup_admin'], $x7p->profile['usergroup']) && $row_perm['admin_panic']==1){
                        return "Non sei autorizzato a gestire questo gremios";
                }
                
                $gif_query = $db->DoQuery("SELECT logo FROM {$prefix}permissions WHERE usergroup='{$base_group}'");
                $row=$db->Do_Fetch_Assoc($gif_query);
                $gif=$row['logo'];
        
                $db->DoQuery("UPDATE {$prefix}users SET bio='$gif' WHERE username='$target'");
                $db->DoQuery("DELETE FROM {$prefix}groups WHERE username='$target' AND usergroup='$corp'");
                
            
	            if(is_gremios($corp)){
    	        	$db->DoQuery("UPDATE {$prefix}users SET user_group='{$base_group}' WHERE username='$target'");
    	        	$db->DoQuery("INSERT INTO {$prefix}groups (usergroup, username, corp_master) VALUES('{$base_group}', '$target','0')
        					ON DUPLICATE KEY UPDATE usergroup=usergroup, username=username");
	            }
        }
        else
                return "Non sei autorizzato a gestire questo gremios";

    }

    function admin_corp($target, $status, $corp){
        global $db, $prefix, $x7s, $x7c, $x7p;
        $query = $db->DoQuery("SELECT usergroup FROM {$prefix}groups WHERE username='$target' AND usergroup='$corp'");
        $row = $db->Do_Fetch_Assoc($query);
        

        //We can remove only members that belong to our corp
        if(in_array($corp,$x7p->profile['usergroup']) || checkIfMaster()){
                $perm_query=$db->DoQuery("SELECT admin_panic FROM {$prefix}permissions WHERE usergroup='$row[usergroup]'");
                $row_perm = $db->Do_Fetch_Assoc($perm_query);

                if($row_perm==null)
                        return "Gruppo $corp non esistente";

                //Only admin can make admin and masters
                if(!in_array($x7c->settings['usergroup_admin'], $x7p->profile['usergroup']) && $row_perm['admin_panic']==1){
                        return "Non sei autorizzato a gestire questo gremios";
                }
                
                $db->DoQuery("UPDATE {$prefix}groups SET corp_master='$status' WHERE username='$target' AND usergroup='$corp'");
        }
        else
                return "Non sei autorizzato a gestire questo gremios";

    }
    
    function is_gremios($group){
    	global $db, $prefix;
    	
    	$query = $db->DoQuery("SELECT gremios FROM {$prefix}permissions WHERE usergroup='$group'");
    	$row = $db->Do_Fetch_Assoc($query);
    	
    	return $row['gremios'];
    }

    function build_ability_javascript($max_ab){
        $body='';
        if(!checkIfMaster()){
					$body .='	<script language="javascript" type="text/javascript">
								
								'.ability_script($max_ab).'
								
								function do_form_refresh(ab_name){
									document.sheet_form[ab_name+"_display"].value = document.sheet_form[ab_name].value;
									document.sheet_form["xp_display"].value = document.sheet_form["xp"].value;
								}';
				}
				//Master can everithing wothout controls
				else{
					$body .='	<script language="javascript" type="text/javascript">
								function add(ab_name){
									var value = parseInt(document.sheet_form[ab_name].value);
									
									document.sheet_form[ab_name].value = value + 1;
										
									do_form_refresh(ab_name);
								}								
								
								
								function sub(ab_name){
									var value = parseInt(document.sheet_form[ab_name].value);
									
									document.sheet_form[ab_name].value = value - 1;
									
									do_form_refresh(ab_name);
									
									
								}
								
								function do_form_refresh(ab_name){
									document.sheet_form[ab_name+"_display"].value = document.sheet_form[ab_name].value;
								}';
				
				}
							
				$body .= '
								function modify(){
								        if(document.getElementById("visual"))
									       document.getElementById("visual").style.visibility="hidden";
                                                                        if(document.getElementById("visual2"))
                                                                              document.getElementById("visual2").style.visibility="hidden";
									if(document.getElementById("modifiable"))
									       document.getElementById("modifiable").style.visibility="visible";
									if(document.getElementById("modifiable2"))
									       document.getElementById("modifiable2").style.visibility="visible";
									if(document.getElementById("modifiable3"))
									       document.getElementById("modifiable3").style.visibility="visible";
                                    if(document.getElementById("modify"))
									       document.getElementById("modify").style.visibility="hidden";
									if(document.getElementById("modify2"))
									       document.getElementById("modify2").style.visibility="hidden";
                                                                        if(document.getElementById("modify3"))
									       document.getElementById("modify3").style.visibility="hidden";
									if(document.getElementById("aggiorna"))
									       document.getElementById("aggiorna").style.visibility="visible";
								}
	
						</script>';
        return $body;
        }
                
	function ability_script($max_ab){
		$body = 'function add(ab_name){
				var value = parseInt(document.sheet_form[ab_name].value);
				var xp = parseInt(document.sheet_form["xp"].value);

				if (xp >= value+1 && value < '.$max_ab.'){

					dep = document.sheet_form[ab_name+"_dep"].value;

					if(dep != ""){
						dep_act_val = parseInt(document.sheet_form[dep].value);
						if(2*dep_act_val > value){
							document.sheet_form[ab_name].value = value + 1;
							document.sheet_form["xp"].value = xp - (value + 1);
						}
						else{
						        right_value=2*dep_act_val;
						        if(right_value==0)
						            right_value=1;
							alert("Non puoi alzare \""+document.sheet_form[ab_name+"_name"].value+"\" senza avere almeno "+right_value+" gradi in \""+document.sheet_form[dep+"_name"].value+"\"");
						}
					}
					else{
						document.sheet_form[ab_name].value = value + 1;
						document.sheet_form["xp"].value = xp - (value + 1);
					}

					do_form_refresh(ab_name);
				}
			}

			function sub(ab_name){
				var value = parseInt(document.sheet_form[ab_name].value);
				var min = parseInt(document.sheet_form[ab_name+"_min"].value);

				if (value > min){
					document.sheet_form[ab_name].value = value - 1;
					var xp = parseInt(document.sheet_form["xp"].value);
					document.sheet_form["xp"].value = xp + value;
					leafs = "";
					if(document.sheet_form[ab_name+"_dep"].value == ""){
						leafs = document.sheet_form[ab_name+"_leaf"].value;
					}

					if(leafs != ""){
						splitted = leafs.split("|");
						for (i in splitted){
							if(splitted[i]!=""){
								actual_value = parseInt(document.sheet_form[splitted[i]].value);
								final_value = parseInt(document.sheet_form[splitted[i]+"_min"].value);
								back_xp = 0;
								
								while(actual_value > final_value){
									back_xp += actual_value;
									actual_value--;
								}

								document.sheet_form[splitted[i]].value = document.sheet_form[splitted[i]+"_min"].value;
								xp = parseInt(document.sheet_form["xp"].value);

								document.sheet_form["xp"].value = xp + back_xp;

								do_form_refresh(splitted[i]);
							}
						}
					}
					do_form_refresh(ab_name);
				}

			}';

		return $body;
	}

	function get_user_space($username){
		global $db, $prefix, $x7c;
		$residuo = 0;

		$query_obj = $db->DoQuery("SELECT SUM(size) as total_size FROM 
				{$prefix}objects WHERE owner='$username' AND equipped='1'");
		$query_usr = $db->DoQuery("SELECT spazio FROM {$prefix}users 
				WHERE username = '$username'");

		$row_obj = $db->Do_Fetch_Assoc($query_obj);
		$row_usr = $db->Do_Fetch_Assoc($query_usr);
	
		if ($row_usr)
			$residuo = $row_usr['spazio'] - $row_obj['total_size'];

		return $residuo;
	}
?>
