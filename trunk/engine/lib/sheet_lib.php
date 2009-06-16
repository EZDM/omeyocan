<?PHP

        function toggle_heal($pg, $heal){
                global $db, $prefix;
		$errore='';
		$time=time();
		$db->DoQuery("UPDATE {$prefix}users SET heal_time='$time', autoheal='$heal' WHERE username='$pg'");
		
		
        }

	function toggle_death($pg, $kill){
		global $db, $prefix;
		$errore='';
				if($kill){
                                        //Morto per 5 giorni
                                        $death_day=5;
                                        $resurgo = time() + $death_day*24*3600;
                                        $db->DoQuery("UPDATE {$prefix}users SET talk='0', info='Morto', autoheal='0', resurgo='$resurgo' WHERE username='$pg'");

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
                                }
                                
             return $errore;
		
		
		
	}
    function join_corp($pg, $corp, $from_sheet=0){
        global $db, $prefix, $x7s, $x7c;
        $query = $db->DoQuery("SELECT id FROM {$prefix}ability WHERE corp='$corp'");

        while($row = $db->Do_Fetch_Assoc($query)){
            $db->DoQuery("INSERT INTO {$prefix}userability (ability_id, username, value) VALUES('$row[id]', '$pg', '0')
                          ON DUPLICATE KEY UPDATE username=username, ability_id=ability_id");
        }

        if($from_sheet){
                
                $perm_query=$db->DoQuery("SELECT admin_panic FROM {$prefix}permissions WHERE usergroup='$corp'");
                $row_perm = $db->Do_Fetch_Assoc($perm_query);

                if($row_perm==null)
                        return "Gruppo $corp non esistente";

                //Only admin can make admin and masters
                if($x7s->user_group != $x7c->settings['usergroup_admin'] && $row_perm['admin_panic']==1){
                        return "Non sei autorizzato a gestire questo gremios";
                }
                
                $gif_query = $db->DoQuery("SELECT logo FROM {$prefix}permissions WHERE usergroup='$corp'");
                $row=$db->Do_Fetch_Assoc($gif_query);
                $gif=$row['logo'];
                
                $db->DoQuery("UPDATE {$prefix}users SET user_group='$corp', corp_master='0', bio='$gif' WHERE username='$pg'");
        }
    }

    function leave_corp($target){
        global $db, $prefix, $x7s, $x7c;
        $query = $db->DoQuery("SELECT user_group FROM {$prefix}users WHERE username='$target'");
        $row = $db->Do_Fetch_Assoc($query);

        //We can remove only members that belong to our corp
        if($row['user_group']==$x7s->user_group || checkIfMaster()){
                $perm_query=$db->DoQuery("SELECT admin_panic FROM {$prefix}permissions WHERE usergroup='$row[user_group]'");
                $row_perm = $db->Do_Fetch_Assoc($perm_query);

                if($row_perm==null)
                        return "Gruppo $corp non esistente";

                //Only admin can make admin and masters
                if($x7s->user_group != $x7c->settings['usergroup_admin'] && $row_perm['admin_panic']==1){
                        return "Non sei autorizzato a gestire questo gremios";
                }
                
                $gif_query = $db->DoQuery("SELECT logo FROM {$prefix}permissions WHERE usergroup='{$x7c->settings['usergroup_default']}'");
                $row=$db->Do_Fetch_Assoc($gif_query);
                $gif=$row['logo'];
        
                $db->DoQuery("UPDATE {$prefix}users SET user_group='{$x7c->settings['usergroup_default']}', corp_master='0', bio='$gif' WHERE username='$target'");
        }
        else
                return "Non sei autorizzato a gestire questo gremios";

    }

    function admin_corp($target, $status){
        global $db, $prefix, $x7s, $x7c;
        $query = $db->DoQuery("SELECT user_group FROM {$prefix}users WHERE username='$target'");
        $row = $db->Do_Fetch_Assoc($query);
        

        //We can remove only members that belong to our corp
        if($row['user_group']==$x7s->user_group || checkIfMaster()){
                $perm_query=$db->DoQuery("SELECT admin_panic FROM {$prefix}permissions WHERE usergroup='$row[user_group]'");
                $row_perm = $db->Do_Fetch_Assoc($perm_query);

                if($row_perm==null)
                        return "Gruppo $corp non esistente";

                //Only admin can make admin and masters
                if($x7s->user_group != $x7c->settings['usergroup_admin'] && $row_perm['admin_panic']==1){
                        return "Non sei autorizzato a gestire questo gremios";
                }
                
                $db->DoQuery("UPDATE {$prefix}users SET corp_master='$status' WHERE username='$target'");
        }
        else
                return "Non sei autorizzato a gestire questo gremios";

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

	function recalculate_space($username){
                global $x7s, $db, $prefix, $x7c;
                $query = $db->DoQuery("SELECT size FROM {$prefix}objects WHERE owner='$username' AND equipped='1'");

                $occupato=0;
                while($row = $db->Do_Fetch_Assoc($query)){
                    $occupato+=$row['size'];
                }
                $residuo = $x7c->settings['default_spazio'] - $occupato;

                if($residuo<0)
                        die('Left space not consistent');

                $db->DoQuery("UPDATE {$prefix}users SET spazio='$residuo' WHERE username='$username'");
	}

?>