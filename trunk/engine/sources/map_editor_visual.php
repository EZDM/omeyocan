<?php
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

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Map editor</title>
    <LINK REL="SHORTCUT ICON" HREF="../favicon.ico">
    <style type="text/css">
      div{
        background-color: transparent;
        position: absolute;
        border: solid 1px white;
      }
      body{
        margin: 0;
        color: white;
        background: black;
      }

      #map{
        width: 1026px;
        height: 723px;
        background-image:url('./graphic/sfondomap1026x723.jpg');
      }
      #editor{ 
        top: 0px;
        left: 1030px;
      }
      
      #errore{ 
      	position: relative; 
        color: red;
        font-size: 14pt;
        font-weight: bold;
        border: 0;
      }
      
      #form{
      	position: relative;
      	border: 0;
      }
      
      

    </style>

    <script language="javascript" type="text/javascript">
    edited = 0;
    added = 0;
    deleted = 0;
    new_id=-1;
    
    
    function place_button(e) {
		if(deleted || edited){
			alert("Non puoi aggiungere un nuovo pulsante finche' non invii le modifiche effettuate finora");
			return;
		}
        
    	var posx = -1;
    	var posy = -1;
    	if (!e) var e = window.event;
    	if (e.pageX || e.pageY) 	{
    		posx = e.pageX;
    		posy = e.pageY;
    	}
    	else if (e.clientX || e.clientY) 	{
    		posx = e.clientX + document.body.scrollLeft
    			+ document.documentElement.scrollLeft;
    		posy = e.clientY + document.body.scrollTop
    			+ document.documentElement.scrollTop;
    	}

    	//Correction for button size
    	posx-=9;
    	posy-=9;

    	document.getElementById('selected_link_static').selectedIndex=0;
  		document.getElementById('selected_link').selectedIndex=0;

		document.getElementById('visual_selected_x').innerHTML=posx;
		document.getElementById('visual_selected_y').innerHTML=posy;
		document.getElementById('visual_selected_id').value="<Nome da visualizzare>";

		document.getElementById('selected_x').value=posx;
		document.getElementById('selected_y').value=posy;
		document.getElementById('selected_id').value=new_id;

		if(!added){
			var new_button=document.createElement('img');
			pulsante_img = './graphic/pulsante.gif';

			el = document.getElementById('selected_img');
			if(el.options[el.selectedIndex].value != '')
				pulsante_img = el.options[el.selectedIndex].value;
				
			new_button.setAttribute('src', pulsante_img);
			new_button.setAttribute('style', 'position: absolute; top:'+posy+'; left:'+posx+';');
			new_button.setAttribute('id', new_id);
			new_button.setAttribute('onClick', 'javascript: edit_button(event)');
	
	
	    	document.getElementById('selected_link_static').selectedIndex=0;
	    	document.getElementById('selected_link').selectedIndex=0;
			
			document.getElementById('map').appendChild(new_button);
			added = 1;
			document.getElementById('add').value = 1;
        	document.getElementById('edit').value = -1;
        	document.getElementById('delete').value = -1;
		}
		else{
			var new_button=document.getElementById('-1');
			new_button.setAttribute('style', 'position: absolute; top:'+posy+'; left:'+posx+';');
		}

    }

    function edit_button(e){
    	var targ;
    	if (!e) 
        	var e = window.event;

        if (e.stopPropagation) 
            e.stopPropagation();
    	
    	if(e.target)
        	targ = e.target;
    	else if (e.srcElement) 
        	targ = e.srcElement;

    	id=targ.id;
    	link=targ.getAttribute('alt');

		if((added || edited || deleted) && id!=document.getElementById('selected_id').value){
			alert("Non puoi modificare un pulsante finche' non invii le modifiche effettuate finora");
			return;
		}

		document.getElementById('selected_link_static').selectedIndex=0;
  		document.getElementById('selected_link').selectedIndex=0;

		document.getElementById('visual_selected_x').innerHTML=targ.offsetLeft;
		document.getElementById('visual_selected_y').innerHTML=targ.offsetTop;
		document.getElementById('visual_selected_id').value=targ.getAttribute('title');

		found = false;
		for(i=0; i<document.getElementById('selected_link').length && !found; i++){
			if(document.getElementById('selected_link').options[i].value==link){
				document.getElementById('selected_link').selectedIndex=i;
				document.getElementById('visual_pop_w').style.visibility = 'hidden';
				document.getElementById('visual_pop_h').style.visibility = 'hidden';
				found = true;
			}
		}

		for(i=0; i<document.getElementById('selected_link_static').length && !found; i++){
			if(document.getElementById('selected_link_static').options[i].value==link){
				document.getElementById('selected_link_static').selectedIndex=i;
				document.getElementById('visual_pop_w').style.visibility = 'visible';
				document.getElementById('visual_pop_h').style.visibility = 'visible';
				found = true;
			}
		}

		for(i=0; i<document.getElementById('selected_img').length; i++){
			if(document.getElementById('selected_img').options[i].value==targ.getAttribute('src')){
				document.getElementById('selected_img').selectedIndex=i;
		    	img = document.getElementById('visual_img_preview');
				img.setAttribute('src', document.getElementById('selected_img').options[i].value);
			}
		}

		document.getElementById('selected_x').value=targ.offsetLeft;
		document.getElementById('selected_y').value=targ.offsetTop;
		document.getElementById('selected_id').value=id;

		if(targ.getAttribute('night') ==0)
			document.getElementById('night_red').checked=false;
		else
			document.getElementById('night_red').checked=true;

		if(targ.getAttribute('rollover') ==0)
			document.getElementById('rollover').checked=false;
		else
			document.getElementById('rollover').checked=true;

		document.getElementById('edit').value = id;
    document.getElementById('add').value = -1;
    document.getElementById('delete').value = -1;

		document.getElementById('pop_w').value = targ.getAttribute('pop_w');
		document.getElementById('pop_h').value = targ.getAttribute('pop_h');


    }

    function delete_button(e){
        if (e.stopPropagation) 
            e.stopPropagation();
        
        id = document.getElementById('selected_id').value;
        btn = document.getElementById(id);

		if((added || deleted) && id !=-1){
			alert("Non puoi cancellare un pulsante finche' non invii le modifiche effettuate finora");
			return;
		}

        if(btn){         
        	document.getElementById('map').removeChild(btn);
        	if(id == -1){
        		added = 0;
        		document.getElementById('add').value = -1;
            	document.getElementById('edit').value = -1;
            	document.getElementById('delete').value = -1;
        	}
        	else{
            	deleted = 1;
            	document.getElementById('delete').value = id;
            	document.getElementById('add').value = -1;
            	document.getElementById('edit').value = -1;
        	}

      		document.getElementById('selected_link_static').selectedIndex=0;
      		document.getElementById('selected_link').selectedIndex=0;
            	
        }


    }

    function reset_static(){
        sel = document.getElementById('selected_link_static');
        sel.selectedIndex=0;
				document.getElementById('visual_pop_w').style.visibility = 'hidden';
				document.getElementById('visual_pop_h').style.visibility = 'hidden';

		if(document.getElementById('edit').value > 0)
			edited = 1;

    }

    function reset_room(){
        sel = document.getElementById('selected_link');
        sel.selectedIndex=0;
				document.getElementById('visual_pop_w').style.visibility = 'visible';
				document.getElementById('visual_pop_h').style.visibility = 'visible';

		if(document.getElementById('edit').value > 0)
			edited = 1;
    }

    function update_img(el){
		img = document.getElementById('visual_img_preview');
		img.setAttribute('src', el.options[el.selectedIndex].value);

		id = document.getElementById('selected_id').value;
		if(id){
        	btn = document.getElementById(id);
        	if(btn){
            	btn.setAttribute('src', el.options[el.selectedIndex].value);

        		if(document.getElementById('edit').value > 0)
        			edited = 1;
        	}
		}

		
    }

    </script>

  </head>
  <body onLoad="javascript: document.editor.reset();">
      <div id="map" onClick="javascript: place_button(event);">
		<?php echo $button_list; ?>
		
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 175; left: 205;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 234; left: 209;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 221; left: 239;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 146; left: 202;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 143; left: 235;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 236; left: 238;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 206; left: 240;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 173; left: 220;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 198; left: 253;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 192; left: 210;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 165; left: 248;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 234; left: 207;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 144; left: 247;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 219; left: 232;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 233; left: 215;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 142; left: 243;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 214; left: 230;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 217; left: 245;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 164; left: 222;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 200; left: 251;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 242; left: 213;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 232; left: 219;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 211; left: 241;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 239; left: 234;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 221; left: 214;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 170; left: 226;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 230; left: 204;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 239; left: 237;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 146; left: 236;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 195; left: 217;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 158; left: 215;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 158; left: 212;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 243; left: 221;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 224; left: 252;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 140; left: 214;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 204; left: 220;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 171; left: 229;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 167; left: 235;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 152; left: 206;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 154; left: 214;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 140; left: 233;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 229; left: 223;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 236; left: 220;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 229; left: 214;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 184; left: 244;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 220; left: 216;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 143; left: 237;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 210; left: 211;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 160; left: 230;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 179; left: 219;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 144; left: 235;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 151; left: 216;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 223; left: 249;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 156; left: 234;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 200; left: 230;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 207; left: 245;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 177; left: 202;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 142; left: 203;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 218; left: 216;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 192; left: 243;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 202; left: 254;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 179; left: 250;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 154; left: 252;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 173; left: 230;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 227; left: 225;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 182; left: 234;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 161; left: 210;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 177; left: 247;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 215; left: 241;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 140; left: 221;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 236; left: 221;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 188; left: 245;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 241; left: 230;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 153; left: 206;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 219; left: 203;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 178; left: 237;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 225; left: 204;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 141; left: 210;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 172; left: 226;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 150; left: 219;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 233; left: 221;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 145; left: 222;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 206; left: 243;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 217; left: 231;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 205; left: 211;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 147; left: 200;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 181; left: 210;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 151; left: 237;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 202; left: 233;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 185; left: 200;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 143; left: 231;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 147; left: 225;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 226; left: 227;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 206; left: 217;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 206; left: 229;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 238; left: 232;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 169; left: 243;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 218; left: 247;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 152; left: 207;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 241; left: 228;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 177; left: 254;"></div>
		<div id="secret" style="border: 0; background-color: green; width: 3px; height: 3px; top: 170; left: 250;"></div>
				
      </div>
      
      <div id="editor">
      	<div id="form">
	      	<form name="editor" method="post" action="index.php?act=mapeditor&edited=1">
		      	<table>
		      		<tr><td>Descrizione:</td><td>
							<input type="text" id="visual_selected_id"
								name="descr" onClick="javascript: this.select();"></td></tr>
		      		<tr><td>Coordinata X:</td><td id="visual_selected_x"></td></tr>
		      		<tr><td>Coordinata Y:</td><td id="visual_selected_y"></td></tr>
		      		<tr><td>Stanza:</td><td id="visual_selected_link">	
							<select id="selected_link" name="selected_link"
								onChange="javascript: reset_static(this);">
								<?php echo $link_selection;?> </select></td></tr>
		      		<tr><td>Popup:</td><td id="visual_selected_link_static">
								<select id="selected_link_static" name="selected_link_static"
									onChange="javascript: reset_room(this);">
								<?php echo $link_selection_static;?> </select></td></tr>
							<tr id="visual_pop_w" style="visibility: hidden;">
							<td>Larghezza popup:</td>
								<td><input type="text" id="pop_w" name="pop_w" size=3
								value=600> px</tr>
							<tr id="visual_pop_h" style="visibility: hidden;">
							<td>Altezza popup:</td>
								<td><input type="text" id="pop_h" name="pop_h" size=3 
								value=440> px</tr>
		      		<tr><td>Immagine:</td><td id="visual_selected_img">
								<img id="visual_img_preview" src="./graphic/pulsante.gif">
								<select id="selected_img" name="selected_img"
								onChange="javascript: update_img(this);">
								<?php echo $button_img;?> </select></td></tr>
		      		<tr><td>Cambia colore<br> di notte?</td><td>
								<input type="checkbox" checked name="night_red" id="night_red">
							</td></tr>
		      		<tr><td>Rollover<br> abilitato?</td><td>
								<input type="checkbox" checked name="rollover" id="rollover">
							</td></tr>
		      		<tr><td><input type="button" id="delete_btn"
							onClick="javascript: delete_button(event);"
							value="Cancella pulsante"></td></tr>
		      		<tr><td><input type="submit" value="Invia modifiche"></td><td><input type="button" id="abort_btn" onClick="javascript: window.location.reload();" value="Annulla modifiche"></td></tr>
		      		<tr><td><br><input type="button" value="Chiudi editor" onClick="javascript: window.close(self);"></td></tr>
		      	</table>
		      	
		      	<input type="hidden" id="selected_id" name="selected_id">
		      	<input type="hidden" id="selected_x" name="selected_x">
		      	<input type="hidden" id="selected_y" name="selected_y">
		      	<input type="hidden" id="edit" value="-1" name="edit">
		      	<input type="hidden" id="add" value="-1" name="add">
		      	<input type="hidden" id="delete" value="-1" name="delete">
	      	</form>
      	</div>
      	<div id="errore">
      		<?php echo $errore;?>
      	</div>
      </div>
  </body>
</html>
