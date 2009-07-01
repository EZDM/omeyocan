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

		document.getElementById('visual_selected_x').innerHTML=posx;
		document.getElementById('visual_selected_y').innerHTML=posy;
		document.getElementById('visual_selected_id').value="<Nome da visualizzare>";

		document.getElementById('selected_x').value=posx;
		document.getElementById('selected_y').value=posy;
		document.getElementById('selected_id').value=new_id;

		if(!added){
			var new_button=document.createElement('img');
			new_button.setAttribute('src', './graphic/pulsante.gif');
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

		if((added || edited) && id!=document.getElementById('selected_id').value){
			alert("Non puoi modificare un pulsante finche' non invii le modifiche effettuate finora");
			return;
		}

		document.getElementById('visual_selected_x').innerHTML=targ.offsetLeft;
		document.getElementById('visual_selected_y').innerHTML=targ.offsetTop;
		document.getElementById('visual_selected_id').value=targ.getAttribute('title');

		found = false;
		for(i=0; i<document.getElementById('selected_link').length && !found; i++){
			if(document.getElementById('selected_link').options[i].value==link){
				document.getElementById('selected_link').selectedIndex=i;
				found = true;
			}
		}

		for(i=0; i<document.getElementById('selected_link_static').length && !found; i++){
			if(document.getElementById('selected_link_static').options[i].value==link){
				document.getElementById('selected_link_static').selectedIndex=i;
				found = true;
			}
		}

		for(i=0; i<document.getElementById('selected_img').length; i++){
			if(document.getElementById('selected_img').options[i].value==targ.getAttribute('src')){
				document.getElementById('selected_img').selectedIndex=i;
			}
		}

		document.getElementById('selected_x').value=targ.offsetLeft;
		document.getElementById('selected_y').value=targ.offsetTop;
		document.getElementById('selected_id').value=id;


		document.getElementById('edit').value = id;
    	document.getElementById('add').value = -1;
    	document.getElementById('delete').value = -1;
    	

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

        	document.getElementById('selected_link').selectedIndex=0;
        	document.getElementById('selected_link_static').selectedIndex=0;
            	
        }


    }

    function reset_static(){
        sel = document.getElementById('selected_link_static');
        sel.selectedIndex=0;

		if(document.getElementById('edit').value > 0)
			edited = 1;

    }

    function reset_room(cur){
        sel = document.getElementById('selected_link');
        sel.selectedIndex=0;

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
        	}
		}

		
    }

    </script>

  </head>
  <body onLoad="javascript: document.editor.reset();">
      <div id="map" onClick="javascript: place_button(event);">
		<?php echo $button_list; ?>
      </div>
      
      <div id="editor">
      	<div id="form">
	      	<form name="editor" method="post" action="index.php?act=mapeditor&edited=1">
		      	<table>
		      		<tr><td>Descrizione:</td><td><input type="text" id="visual_selected_id" name="descr" onClick="javascript: this.select();"></td></tr>
		      		<tr><td>Coordinata X:</td><td id="visual_selected_x"></td></tr>
		      		<tr><td>Coordinata Y:</td><td id="visual_selected_y"></td></tr>
		      		<tr><td>Stanza:</td><td id="visual_selected_link" onChange="javascript: reset_static(this);"><select id="selected_link" name="selected_link"><?php echo $link_selection;?> </select></td></tr>
		      		<tr><td>Popup:</td><td id="visual_selected_link_static"><select id="selected_link_static" name="selected_link_static" onChange="javascript: reset_room(this);"><?php echo $link_selection_static;?> </select></td></tr>
		      		<tr><td>Immagine:</td><td id="visual_selected_img"><img id="visual_img_preview" src="./graphic/pulsante.gif"><select id="selected_img" name="selected_img" onChange="javascript: update_img(this);"><?php echo $button_img;?> </select></td></tr>
		      		<tr><td><input type="button" id="delete_btn" onClick="javascript: delete_button(event);" value="Cancella pulsante"></td><td><input type="button" id="abort_btn" onClick="javascript: window.location.reload();" value="Annulla modifiche"></td></tr>
		      		<tr><td><input type="submit" value="Invia modifiche"></td></tr>
		      		<tr><td><br><input type="button" value="Chiudi editor" onClick="javascript: window.close(self);"></td></tr>
		      	</table>
		      	
		      	<input type="hidden" id="selected_id" name="selected_id"><br>
		      	<input type="hidden" id="selected_x" name="selected_x"><br>
		      	<input type="hidden" id="selected_y" name="selected_y"><br>
		      	<input type="hidden" id="edit" value="-1" name="edit"><br>
		      	<input type="hidden" id="add" value="-1" name="add"><br>
		      	<input type="hidden" id="delete" value="-1" name="delete"><br>
	      	</form>
      	</div>
      	<div id="errore">
      		<?php echo $errore;?>
      	</div>
      </div>
  </body>
</html>