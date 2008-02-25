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
//		X7 Chat Version 2.0.4.3
//		Released August 28, 2006
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

	// Handles the room list page
	function room_list_page(){
		global $print, $prefix, $txt, $x7c, $x7s, $db;
		
		include_once('./lib/online.php');
		
		$db->DoQuery("UPDATE {$prefix}users SET position='Mappa' WHERE username='$x7s->username'");
		
		$time = time();
		$query = $db->DoQuery("SELECT count(*) AS num FROM {$prefix}online WHERE name='$x7s->username'");
		$row = $db->Do_Fetch_Assoc($query);
		if($row['num']!=0){
			$db->DoQuery("UPDATE {$prefix}online SET time='$time', room='Mappa' WHERE name='$x7s->username'");
		}
		else{
			$ip = $_SERVER['REMOTE_ADDR'];
			$db->DoQuery("INSERT INTO {$prefix}online VALUES('0','$x7s->username','$ip','Mappa','','$time','{$x7c->settings['auto_inv']}')");
		}
		
		clean_old_data();
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} -- Mappa</title>";
		echo $print->style_sheet;
		echo $print->ss_mini;
		echo $print->ss_chatinput;
		echo $print->ss_uc;


?>
 </head><body onload="javascript: do_initial_refresh();"> <!--openActionBox();">-->
 <div id="container">
 <div id="divmap">

<?PHP 
//This file include common layout for frame and map
	include('./sources/layout.html'); 

?>
  
  <!-- IMMAGINE DELLA POLAROID (a seconda della stanza) -->
  <img style="position:absolute; top:0px; left:807px;" src="./graphic/polaroid.jpg">
  
  <!-- Pulsanti mappa -->
	<A href="index.php?act=frame&room=Chiesa"> <img onMouseDown="this.src='./graphic/pulsante_down.gif'" onMouseOut="this.src='./graphic/pulsante.gif'" onMouseOver="this.src='./graphic/pulsante_over.gif'" style="position:absolute; top:352px; left:206px;" src="./graphic/pulsante.gif"></A>
  
	<A href="index.php?act=frame&room=Cimitero"> <img onMouseDown="this.src='./graphic/pulsante_down.gif'" onMouseOut="this.src='./graphic/pulsante.gif'" onMouseOver="this.src='./graphic/pulsante_over.gif'" style="position:absolute; top:520px; left:655px;" src="./graphic/pulsante.gif"></A>

	<A href="index.php?act=frame&room=Piazza"> <img onMouseDown="this.src='./graphic/pulsante_down.gif'" onMouseOut="this.src='./graphic/pulsante.gif'" onMouseOver="this.src='./graphic/pulsante_over.gif'" style="position:absolute; top:550px; left:380px;" src="./graphic/pulsante.gif"></A>
  




<div align="center" id="copyrigth" style="visibility: visible;">
			Game engine by: <a href="http://netgroup.polito.it/Members/niccolo_cascarano" target="_blank">Niccol&ograve; Cascarano</a> - Graphic by: Federico Gori<br>

			Powered By <a href="http://www.x7chat.com/" target="_blank">X7 Chat</a> 2.0.5 &copy; 2004 By The <a href="http://www.x7chat.com/" target="_blank">X7 Group</a><br></div>

</div>

<script language="javascript" type="text/javascript">
						listhash = '';
						startfrom = 0;
						newMail = 0;

						function do_initial_refresh(){
							// Create object
							if(window.opener!=window.self){
								window.self.close();
							}
							
							mapRefresh = setInterval('do_refresh()','<?PHP echo $x7c->settings['refresh_rate']; ?>');
							do_refresh();
							
							
						}

						function requestReady_channel1(){
							if(httpReq2){
								if(httpReq2.readyState == 4){
									if(httpReq2.status == 200){

										playSound = 0;
										modification=0;
										
										
										//document.getElementById('debug').innerHTML += httpReq2.responseText;
										

										var dataArray = httpReq2.responseText.split("|");
										for(x = 0;x < dataArray.length;x++){
											var dataSubArray = dataArray[x].split(";");
											if(dataSubArray[0] == '2'){
												// Operators for userlist
												

												var dataSubArray2 = dataSubArray[1].split(",");
												for(x2 = 0;x2 < dataSubArray2.length;x2++){
													if(dataSubArray2[x2] != ''){
														dataSubArray2[x2] = restoreText(dataSubArray2[x2]);
													}
												}

												playSound = 2;

											}else if(dataSubArray[0] == '3'){
												// Users for userlist

												var dataSubArray2 = dataSubArray[1].split(",");
												for(x2 = 0;x2 < dataSubArray2.length;x2++){
													if(dataSubArray2[x2] != ''){
														dataSubArray2[x2] = restoreText(dataSubArray2[x2]);
													}
												}


											}else if(dataSubArray[0] == '4'){
												// Listhash update
												listhash = dataSubArray[1];
											}else if(dataSubArray[0] == '5'){
												// Endon update
												startfrom = dataSubArray[1];
											}else if(dataSubArray[0] == '6'){
												// Number of offline messages update
												if(dataSubArray[1] > 0) {
													document.getElementById('posta').src = "./graphic/05postasi.jpg";
													
													if(!newMail){
														var tardis = document.getElementById('tardis');
														tardis.Play();
													}
													
													newMail = 1;
												}
												else {
													document.getElementById('posta').src = "./graphic/05postano.jpg";
													newMail = 0;
												}
													
											}else if(dataSubArray[0] == '9'){
												// Redirect w/ error msg
												dataSubArray[1] = restoreText(dataSubArray[1]);
												if(dataSubArray[1] != '')
													alert(dataSubArray[1]);
												document.location = dataSubArray[2];
											}else if(dataSubArray[0] == '11'){
												//Panic update
												panic_value = parseInt(dataSubArray[1]);
												document.chatIn.panic.value=panic_value;
											}else if(dataSubArray[0] == '12'){
												//Panic update
												valore = parseInt(dataSubArray[1]);
												var messaggio;
												if(valore)
													messaggio="Arriva l'oscurità";
												else
													messaggio="L'oscurità se ne va";
												
												alert(messaggio);
												window.location.href = window.location.href;
											}else if(dataSubArray[0] == '13'){
												//Delete message
												document.getElementById('message_window').innerHTML ='';
												startfrom = 0;
												do_refresh();
											}
										


										}

									}
								}
							}
						}

						function restoreText(torestore){
							torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8183/g,"@");
							torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8181/g,"|");
							torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8182/g,";");
							torestore = torestore.replace(/74ce61f75c75b155ea7280778d6e8180/g,",");
							return torestore;
						}

						function do_refresh(){
							jd=new Date();
							nocache = jd.getTime();
							url = './index.php?act=frame&frame=update&room=Mappa&listhash=' + listhash + '&startfrom=' + startfrom + '&nc=' + nocache;							if(window.XMLHttpRequest){
								try {
									httpReq2 = new XMLHttpRequest();
								} catch(e) {
									httpReq2 = false;
								}
							}else if(window.ActiveXObject){
								try{
									httpReq2 = new ActiveXObject("Msxml2.XMLHTTP");
								}catch(e){
									try{
										httpReq2 = new ActiveXObject("Microsoft.XMLHTTP");
									}catch(e){
										httpReq2 = false;
									}
								}
							}
							httpReq2.onreadystatechange = requestReady_channel1;
							httpReq2.open("GET", url, true);
							httpReq2.send("");
						}
						
						

					</script>
					


</body>
</html>
		
<?PHP	}



?>
