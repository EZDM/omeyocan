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
//		your rightaaaa to use this software.
//	
////////////////////////////////////////////////////////////////EOH
?><?PHP

	// Read file name, guess what is handles, you are right
	function register_user(){
		global $x7c, $print, $txt, $db, $prefix, $g_default_settings;
		
		// If admin doesn't want new members then tell them to go away
		if($x7c->settings['allow_reg'] == 0){
			$print->normal_window($txt[14],"$txt[15]");	
			return 0;
		}
		
		// Let's see if they have already filled out the form
		if(isset($_GET['step']) && @$_GET['step'] != "act"){
			// They have already filled out the register form and sent it
			
			// Clean up incoming data
			$_POST['pass1'] = auth_encrypt($_POST['pass1']);
			$_POST['pass2'] = auth_encrypt($_POST['pass2']);
			
			// Check the data they submitted
			if(!eregi("^[^@]*@[^.]*\..*$",$_POST['email']))
				$error = $txt[24];
			if($_POST['pass1'] == "")
				$error = $txt[25];
			if($_POST['pass1'] != $_POST['pass2'])
				$error = $txt[26];
			if($_POST['username'] == "" || eregi("\.|'|,|;| |\"|[^a-zA-Z\-_]",$_POST['username']) || (strlen($_POST['username']) > $x7c->settings['maxchars_username'] && $x7c->settings['maxchars_username'] != 0)){
				$txt[23] = eregi_replace("_n","{$x7c->settings['maxchars_username']}",$txt[23]);
				$error = $txt[23];
			}
			$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE username='$_POST[username]'");
			$row = $db->Do_Fetch_Row($query);
			if($row[0] != "")
				$error = $txt[27];
			
			// Did any errors occur?
			if(isset($error)){
				// An error has occured!
				$body = $error."<Br><Br><div align=\"center\"><a style=\"cursor: pointer;cursor:hand;\" onClick=\"javascript: history.back();\">[$txt[77]]</a></div>";
			
			}else{
				// No Problems!  Create their account
				
				// Generate Activation code
				if($x7c->settings['req_activation'] == 1){
					$seed = "abcdefghijklmnoparstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
					$act_code = $seed[rand(0,strlen($seed)-1)];
					for($x = 0;$x < 5;$x++){
						$act_code .= $seed[rand(0,strlen($seed)-1)];
					}
				}else{
					$act_code = 1;
				}
				
				$time = time();
				$ip = $_SERVER['REMOTE_ADDR'];
				$settings = $g_default_settings; // This is defined in lib/auth.php
				$default_max_panic = $x7c->settings['default_max_panic'];
				$default_start_xp=$x7c->settings['starting_xp']*$x7c->settings['xp_ratio'];
				$default_spazio=$x7c->settings['default_spazio'];
				$default_avatar=$x7c->settings['default_avatar'];

        $gif_query = $db->DoQuery("SELECT logo from {$prefix}permissions 
						WHERE usergroup='{$_POST['base_group']}'");

        $row = $db->Do_Fetch_Assoc($gif_query);

				if (!$row)
					die("Invalid base group, should not happen");

        $gif=$row['logo'];
				
				$db->DoQuery("INSERT INTO {$prefix}users (id,username,password,email,
					status,user_group,time,settings,hideemail,ip,activated,sheet_ok,xp,
					iscr,max_panic,bio,spazio,base_group,avatar) 
						VALUES('0','$_POST[username]','$_POST[pass1]','$_POST[email]',
							'$txt[150]','{$_POST['base_group']}','$time','$settings','0',
							'$ip','$act_code','0','$default_start_xp','$time',
							'$default_max_panic','$gif','$default_spazio',
							'{$_POST['base_group']}','$default_avatar')");
				$db->DoQuery("INSERT INTO {$prefix}groups (username,usergroup,corp_master) 
						VALUES('$_POST[username]','{$_POST['base_group']}','0') 
						ON DUPLICATE KEY UPDATE username=username");
				
				$query_ab = $db->DoQuery("SELECT * FROM {$prefix}ability WHERE corp=''");
				$query_ch = $db->DoQuery("SELECT * FROM {$prefix}characteristic");
				
				//We must create an empty character sheet
				while($row_ch = $db->Do_Fetch_Assoc($query_ch)){
					$db->DoQuery("INSERT INTO {$prefix}usercharact
							(charact_id, username, value)
							VALUES('{$row_ch['id']}', '$_POST[username]', '0')");
				}
				
				while($row_ab = $db->Do_Fetch_Assoc($query_ab)){
					$db->DoQuery("INSERT INTO {$prefix}userability
							(ability_id, username, value)
							VALUES('{$row_ab['id']}', '$_POST[username]', '0')");
				}
				
				$URL = eregi_replace("step=1","step=act&act_code=$act_code","http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}");
				mail($_POST['email'],$txt[618],"$txt[617]\r\n\r\n$URL\r\n","From: {$x7c->settings['site_name']} <{$x7c->settings['admin_email']}>\r\n" ."Reply-To: {$x7c->settings['admin_email']}\r\n" ."X-Mailer: PHP/" . phpversion());

				include_once("./lib/message.php");

				$body = $txt[28];
				
				if($act_code != 1)
					$body .= "<br><br>".$txt[613];
			}
			
		}elseif(@$_GET['step'] == "act"){
		
			$body = activate_account();
				
		}else if(!isset($_GET['disclaimer_done']) && !isset($_GET['base_group'])){
			include_once('./sources/register_intro.php');
			$body = '
				<div id="register_banner">
					  <div id="register_image">
  						<img src="./graphic/choose_page.gif">
						</div>
						<div id="register_intro">
						'.$register_intro.'<br>
						</div>
				</div>
				';
		}else if(!isset($_GET['base_group'])) {
			include_once('./sources/register_intro.php');
			$body = '
		    <script type="text/javascript">	

				function over(id) {
					el = document.getElementById(id);
					el.style.display = "block";
				}

				function restore(id){
					el = document.getElementById(id);
					el.style.display = "none";
				}
				</script>

				<div id="register_banner">
				<div id="class_choice">
					<a href="index.php?act=register&base_group=Umano" class="classlink">
						<div class="class_container" onmouseover="over(\'human\')"
						onmouseout="restore(\'human\')">
							<div class="class_image">
								<img src="./graphic/adam_el.jpg" class="citizen_banner">
							</div>
							<div class="class_descr" id="human">
							Uomini, umani, adamiti o pi&#249; volgarmente chiamati scimmie,
							non hanno particolari forze o debolezze, sono i pi&#249;
							adattabili... da sempre. Possono far parte di una qualsiasi
							gremio, gilda, corporazione o semplice aggregazione; tecnici,
							soldati, esploratori o studiosi non importa: la loro innata
							capacit&#224; di imparare e migrare li rende ottimi individui
							in qualsiasi campo. Possono divenire espER o mutanti, cos&#236;
							come possono imparare le arti arcane in minima parte. L&#39;umano
							&#232; la scelta pi&#249; semplice da giocare se si &#232;
							all&#39;inizio considerando che possono intraprendere carriere
							multiple o cambiare la loro vita in corso.
							</div>
						</div>
					</a>
					<a href="index.php?act=register&base_group=NeoUmano" class="classlink">
						<div class="class_container" onmouseover="over(\'neohuman\')"
						onmouseout="restore(\'neohuman\')">
							<div class="class_image">
								<img src="./graphic/neu_el.jpg" class="citizen_banner">
							</div>
							<div class="class_descr" id="neohuman">
							I neoUmani sono individui dotati di corpo umano e cervello 
							positronico, non sono cyborgs ma possono diventarlo senza
							controindicazioni grazie alla loro -Empatia Sintetica- innata
							che gli permette di innestarsi arti o organi artificiali senza
							ripercussioni negative: i loro cervelli positronici sono infatti
							in grado di metabolizzare facilmente la presenza di parti non
							umane nel loro corpo. Connessi mentalmente tra di loro attraverso
							&quot;La Quiete&quot; sono individui pi&#249; pacati e meno
							portati agli eccessi rispetto agli umani: imparano molto pi&#249;
							in fretta ma hanno limitazioni che non gli permettono di
							massimizzare le loro abilit&#224; e non possono divenire ne
							mutanti, ne espER. Raramente alcuni hanno imparato, in piccola
							parte, le arti arcane, sono guidati da una specie di Source
							artificiale chiamata The Broken Admin che sembra detenere un
							sacco di sapienza e che &#232; quella che resuscita i morti 
							trapiantando loro il cervello positronico: si i neoUmani sono
							tutti cadaveri rianimati artificialmente!
							[Maggiori info in Manuale].
							</div>
						</div>
					</a>
					<a href="index.php?act=register&base_group=NephEl" class="classlink">
						<div class="class_container" onmouseover="over(\'nephel\')"
						onmouseout="restore(\'nephel\')">
							<div class="class_image">
								<img src="./graphic/neph_el.jpg" class="citizen_banner">
							</div>
							<div class="class_descr" id="nephel">
							Demoni, mostri, orrori, diavoli, deva, djinn o quant&#39;altro,
						  cosa siano davvero i discendenti dei Nephilim non si sa per certo:
							queste creature dalle caratteristiche &quot;infernali&quot;
							che spesso razziano in lungo e in largo utilizzando la loro
							supremazia fisica e la loro discreta capacit&#224; di gestire le
							energie del Quantum (anima?). Rosse aureole dietro al loro capo
							vengono spesso sfoggiate con boria, hanno scarsa capacit&#224;
							collaborativa e non possono far parte di alcuna gilda o gremio e
							sono lenti ad apprendere a causa della loro ottusa tracotanza,
							possono divenire espER ma non mutanti, i Neph El hanno anche la
							insolita peculiarit&#224; di morire ogni volta che salgono di
							livello, ognuna di queste morti per&#242; diviene anche una
							resurrezione istantanea (vite di riserva) in caso di decesso
							accidentale o di battaglia, una specie di immortalit&#224; nata
							tra sofferenza e dolore, come biasimarli se poi amano agghindarsi
							di ossa e abiti demonici o se spesso si li trova a sghignazzare
							come folli demoni urlanti? *Tutte le variazioni di razza verranno
							applicate dallo Staff una volta entrati in Land.
							[Maggiori info in Manuale].
							</div>
						</div>
					</a>
					<a href="index.php?act=register&base_group=ElBeth" class="classlink">
						<div class="class_container" onmouseover="over(\'bethel\')"
						onmouseout="restore(\'bethel\')">
							<div class="class_image">
								<img src="./graphic/beth_el.jpg" class="citizen_banner">
							</div>
							<div class="class_descr" id="bethel">
							I discendenti degli &quot;angeli&quot; &#232; una descrizione
							volgare ma calzante: le loro origini sono tutt&#39;ora nebulose
							ma si dice che quando un elohim si unisce con un umano ne nasca
							poi un ibrido dotato di alcune caratteristiche
							&quot;angeliche&quot;: solitamente dotati di corpi belli e
							armoniosi ma intrisi di deformit&#224; che sottolinea
							l&#39;impurit&#224; della loro discendenza. Hanno 6 dita per mano
							(due pollici opponibili) e una facilit&#224; di manipolazione del
							Quantum (anima?) eccezionale: le arti arcane non hanno segreti per
							un El-Beth che possono quindi incanalare il Quantum stesso in
							auree di energia bianco/azzurre volgarmente chiamate aureole, pur
							potendo camminare preferiscono levitare a circa 15 cm da terra:
							questo per differenziarsi bene dalle cosiddette altre &quot;razze
							grezze&quot;. Non temono la morte perch&#232; tornano in vita
							finch&#232; &#232; presente nei paraggi almeno un altro Beth-El
							[giocatori o pNg presenti] *Tutte le variazioni di razza verranno
							applicate dallo Staff una volta entrati in Land.
							[Maggiori info in Manuale].
							</div>
						</div>
					</a>
				</div>
				</div>
				';
		}else{
		
			// No, they still need to fill out this form:
			// If we make it here then the admin wants all the user's they can get!
			$body = "	<form action=\"index.php?act=register&step=1\" method=\"post\" name=\"registerform\">
						<table border=\"0\" width=\"400\" cellspacing=\"0\" cellpadding=\"0\"
						id=\"register_table\">
							<tr valign=\"top\">
								<td width=\"400\" style=\"text-align: center\" colspan=\"4\">$txt[19]<Br><Br></td>
							</tr>
							<tr valign=\"top\">
								<td width=\"50\">&nbsp;</td>
								<td width=\"120\" style=\"vertical-align: middle;\">$txt[2]:<br>
                <b>Questo nome verra' utilizzato per il login, la posta e per la lista presenti</b></td>
								<td width=\"175\" height=\"25\"><input type=\"text\" class=\"text_input\" name=\"username\"></td>
								<td width=\"50\">&nbsp;</td>
							</tr>
							<tr valign=\"top\">
								<td width=\"50\">&nbsp;</td>
								<td width=\"120\" style=\"vertical-align: middle;\">$txt[3]: </td>
								<td width=\"175\" height=\"25\"><input type=\"password\" class=\"text_input\" name=\"pass1\"></td>
								<td width=\"50\">&nbsp;</td>
							</tr>
							<tr valign=\"top\">
								<td width=\"50\">&nbsp;</td>
								<td width=\"120\" style=\"vertical-align: middle;\">$txt[21]: </td>
								<td width=\"175\" height=\"25\"><input type=\"password\" class=\"text_input\" name=\"pass2\"></td>
								<td width=\"50\">&nbsp;</td>
							</tr>
							<tr valign=\"top\">
								<td width=\"50\">&nbsp;</td>
								<td width=\"120\" style=\"vertical-align: middle;\">$txt[20]: </td>
								<td width=\"175\" height=\"25\"><input type=\"text\" class=\"text_input\" name=\"email\"></td>
								<td width=\"50\">&nbsp;</td>
							</tr>
							<tr valign=\"top\">
								<td width=\"400\" style=\"text-align: center\" colspan=\"4\"><input type=\"submit\" value=\"$txt[18]\" class=\"button\"></td>
							</tr>
						</table>
						<input type=\"hidden\" name=\"base_group\" value=\"$_GET[base_group]\">
						</form>
						<div align=\"center\">$txt[22]<Br><Br><a href=\"./index.php\">[$txt[77]]</a></div>
					";
		}
		
		// Save the body to the print buffer
		include_once('./sources/loginout.php');
		print_loginout($body, true);
		return 1;
	}
	
	function activate_account(){
		global $x7c, $print, $txt, $db, $prefix;
		
		// Make sure the activation code exists
		$query = $db->DoQuery("SELECT * FROM {$prefix}users WHERE activated='$_GET[act_code]'");
		$row = $db->Do_Fetch_row($query);
		if($row[0] != ""){
			$db->DoQuery("UPDATE {$prefix}users SET activated='1' WHERE activated='$_GET[act_code]'");
			$body = $txt[614];
		}else{
			$body = $txt[615];
		}
		
		return $body;
	}
?> 
