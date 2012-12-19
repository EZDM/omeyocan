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
<?php 
	$link_selection='<option value=""></option>\n';
	$link_selection_static='<option value=""></option>\n';
	$link_up='<option value=""></option>\n';
	$link_down='<option value=""></option>\n';
	$link_left='<option value=""></option>\n';
	$link_right='<option value=""></option>\n';
	$button_list='';
	$button_img='';
	$errore='';
	
	function map_editor_main(){		
		global $x7c, $db, $prefix, $button_list, $link_selection, $button_img, $link_selection_static, $errore, $link_up, $link_down, $link_left, $link_right;
		if(!$x7c->permissions['admin_panic']){
			die("Non autorizzato");
		}

		$selected_up = '';
		$selected_down = '';
		$selected_left = '';
		$selected_right = '';
		
		if(!isset($_GET['view']))
			$_GET['view'] = 'map_main';
		
		if(isset($_GET['edited']))
			map_edit();

		$query = $db->DoQuery("SELECT * FROM {$prefix}map WHERE view='".
				$_GET['view']."'");
		
		while($row = $db->Do_Fetch_Assoc($query)){
			$button=dirname($_SERVER['PHP_SELF'])."/graphic/pulsante.gif";
			
			if($row['button']!='')
				$button=$row['button'];
			
			$href = "index.php?act=mapeditor&view=$row[link]";
			if($row['link_type'] == 10){
				$selected_up = $row['link'];
				$button_list .= '<a href="'.$href.'"><div id="map_up">'.$row['link'].
					'</div></a>';
			} else if($row['link_type'] == 11) {
				$selected_down = $row['link'];
				$button_list .= '<a href="'.$href.'"><div id="map_down">'.$row['link'].
					'</div></a>';
			} else if($row['link_type'] == 12) {
				$selected_left = $row['link'];
				$button_list .= '<a href="'.$href.'"><div id="map_left">'.
					join('<br>', str_split($row['link'])).'</div></a>';
			} else if($row['link_type'] == 13) {
				$selected_right = $row['link'];
				$button_list .= '<a href="'.$href.'"><div id="map_right">'.
					join('<br>', str_split($row['link'])).'</div></a>';
			}	else {
				$button_list .= "<img rollover=\"$row[rollover]\"
					night=\"$row[night_red]\" 
					id=\"$row[id]\" 
					title=\"$row[descr]\" 
					alt=\"$row[link]\" 
					src=\"$button\" 
					pop_w=\"$row[width]\"
					pop_h=\"$row[height]\"
					onClick=\"javascript: edit_button(event);\" 
					style=\"position: absolute; top: $row[posy]; left: $row[posx]\">\n";
			}
		}
		
		$query = $db->DoQuery("SELECT id, name, long_name FROM {$prefix}rooms ORDER BY long_name");
		
		while($row = $db->Do_Fetch_Assoc($query)){
			$link_selection .= "<option id=\"{$row['name']}\" value=\"index.php?act=frame&room={$row['name']}\">{$row['long_name']}</option>\n";
		}
		
		
		
		$basedir=dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
		$path=$basedir."/graphic/";
		$site_path=dirname($_SERVER['PHP_SELF'])."/graphic/";
		
		if($dh = opendir($path)){
			while (($file = readdir($dh)) !== false) {				
				if($file[0]!="." && filetype($path.$file)!="dir" && eregi("pulsante.*", $file)){
					$selected='';
					if($file=="pulsante.gif")
						$selected="selected";
						
					$button_img .= "<option value=\"$site_path$file\" $selected>$file</option>";
				}
				
			}
			
			closedir($dh);
		}
		
		
		$basedir=dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
		$path=$basedir."/sources/";
		$site_path=dirname($_SERVER['PHP_SELF'])."/sources/";
		
		if($dh = opendir($path)){
			while (($file = readdir($dh)) !== false) {				
				if($file[0]!="." && filetype($path.$file)!="dir" && eregi("sub.*\.html", $file)){
					$link_selection_static .= "<option value=\"$site_path$file\">$file</option>";
				}
				
			}
			
			closedir($dh);
		}
		
		$basedir=dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
		$path=$basedir."/graphic/map/";
		
		if($dh = opendir($path)){
			while (($file = readdir($dh)) !== false) {				
				if($file[0]!="." && filetype($path.$file)!="dir" &&
						preg_match("/.*\.jpg/i", $file) &&
						!preg_match("/obscure|night/", $file)){
					$file = preg_replace("/\.jpg/i", "", $file);

					$selected = '';
					if ($file == $selected_up)
						$selected = 'selected';
					$link_up .= "<option value=\"$file\" $selected>$file</option>";
					$selected = '';

					if ($file == $selected_down)
						$selected = 'selected';
					$link_down .= "<option value=\"$file\" $selected>$file</option>";
					$selected = '';
					
					if ($file == $selected_left)
						$selected = 'selected';
					$link_left .= "<option value=\"$file\" $selected>$file</option>";
					$selected = '';

					if ($file == $selected_right)
						$selected = 'selected';
					$link_right .= "<option value=\"$file\" $selected>$file</option>";
				}
				
			}
			
			closedir($dh);
		}
		
		include_once('./sources/map_editor_visual.php');
	}
	
	
	
	
	
	function map_edit(){
		global $prefix, $db, $errore;
		
		$db->DoQuery("DELETE FROM {$prefix}map WHERE
				link_type >= 10 AND link_type <= 13 AND view = '$_GET[view]'");

		$links[10] = 'link_up';
		$links[11] = 'link_down';
		$links[12] = 'link_left';
		$links[13] = 'link_right';

		for($i=10; $i<14 ; $i++) {
			$link = $links[$i];
			$link_type = $i;
			if(@$_POST[$link]) {
				$db->DoQuery("INSERT INTO {$prefix}map (link_type, link, view)
						VALUES ($link_type, '$_POST[$link]', '$_GET[view]')");		
			}
		}

		if(isset($_POST['delete']) && $_POST['delete']>0){
			$db->DoQuery("DELETE FROM {$prefix}map WHERE id='{$_POST['delete']}'");
			header("location: index.php?act=mapeditor");
			return;
		}	
		if(isset($_POST['edit']) && $_POST['edit']>0){
				$link_type=-1;
				$link = '';
				if(	!isset($_POST['selected_x']) ||
					!isset($_POST['selected_y']) ||
					(!isset($_POST['selected_link']) && !isset($_POST['selected_link_static'])) &&
					!isset($_POST['selected_img']) ||
					!isset($_POST['descr'])
				){	
					$errore = "Parametri mancanti per l'edit";
					return;
				}
				
				$night_red=0;
				$rollover=0;
				$pop_w=600;
				$pop_h=440;
				
				if(isset($_POST['night_red']))
					$night_red=1;
					
				if(isset($_POST['rollover']))
					$rollover=1;	

				if(isset($_POST['pop_w']))
					$pop_w = $_POST['pop_w'];

				if(isset($_POST['pop_h']))
					$pop_h = $_POST['pop_h'];
				
				if(isset($_POST['selected_link']) && $_POST['selected_link']!=''){
					$link_type=0;
					$link = $_POST['selected_link'];
				}
				if(isset($_POST['selected_link_static']) && $_POST['selected_link_static']!=''){
					$link_type=1;
					$link = $_POST['selected_link_static'];
				}
					
				if($link_type!=-1){
					$db->DoQuery("UPDATE {$prefix}map 
								SET link = '$link',
									posx = '{$_POST['selected_x']}',
									posy = '{$_POST['selected_y']}',
									button = '{$_POST['selected_img']}',
									link_type = '$link_type',
									night_red = '$night_red',
									rollover = '$rollover',
									descr = '{$_POST['descr']}',
									width = '$pop_w',
									height = '$pop_h'
								WHERE id='{$_POST['edit']}'");
					
					header("location: index.php?act=mapeditor");					
					return;
				}
				else{
					$errore="Non hai selezionato alcun link o popup!";
					return;
				}
		}
		
		
		
		if(isset($_POST['add']) && $_POST['add']>0){
				$link_type=-1;
				$link = '';
				if(	!isset($_POST['selected_x']) ||
					!isset($_POST['selected_y']) ||
					(!isset($_POST['selected_link']) && !isset($_POST['selected_link_static'])) &&
					!isset($_POST['selected_img']) ||
					!isset($_POST['descr'])
				){	
					$errore="Parametri mancanti per l'add";
					return;
				}
				
				$night_red=0;
				$rollover = 0;
				$pop_w=600;
				$pop_h=440;
				
				if(isset($_POST['night_red']))
					$night_red=1;
					
				if(isset($_POST['rollover']))
					$rollover=1;
				
				if(isset($_POST['pop_w']))
					$pop_w = $_POST['pop_w'];

				if(isset($_POST['pop_h']))
					$pop_h = $_POST['pop_h'];
					
				if(isset($_POST['selected_link']) && $_POST['selected_link']!=''){
					$link_type=0;
					$link = $_POST['selected_link'];
				}
				if(isset($_POST['selected_link_static']) && $_POST['selected_link_static']!=''){
					$link_type=1;
					$link = $_POST['selected_link_static'];
				}
					
				if($link_type!=-1){
					$db->DoQuery("INSERT INTO {$prefix}map 
							(link, posx, posy, button, link_type, descr, night_red, rollover,
							 width, height, view) 
									VALUES('$link', '$_POST[selected_x]', '$_POST[selected_y]',
										'$_POST[selected_img]',	'$link_type', '$_POST[descr]',
										'$night_red', '$rollover', '$pop_w', '$pop_h', 
										'$_GET[view]')");
					header("location: index.php?act=mapeditor&view=$_GET[view]");
					return;
				}
				else{
					$errore =  "Non hai selezionato alcun link o popup!";
					return;
				}
		}
		
		
		
	}

?>
