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
	$link_selection='<option value=""></option>';
	$link_selection_static='<option value=""></option>';
	$button_list='';
	$button_img='';
	
	function map_editor_main(){		
		global $x7c, $db, $prefix, $button_list, $link_selection, $button_img, $link_selection_static;
		if(!$x7c->permissions['admin_panic']){
			die("Non autorizzato");
		}
		
		if(isset($_GET['edit']))
			map_edit();
		
		$query = $db->DoQuery("SELECT * FROM {$prefix}map");
		
		while($row = $db->Do_Fetch_Assoc($query)){
			$button=dirname($_SERVER['PHP_SELF'])."/graphic/pulsante.gif";
			
			if($row['button']!='')
				$button=$row['button'];
			
			$button_list .= "<img id=\"$row[id]\" src=\"$button\" onClick=\"javascript: edit_button(event);\" style=\"position: absolute; top: $row[posy]; left: $row[posx]\">";
		}
		
		$query = $db->DoQuery("SELECT id, name, long_name FROM {$prefix}rooms");
		
		while($row = $db->Do_Fetch_Assoc($query)){
			$link_selection .= "<option id=\"{$row['name']}\" value=\"index.php?act=frame&room={$row['name']}\">{$row['long_name']}</option>";
		}
		
		
		
		$basedir=dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
		$path=$basedir."/graphic/";
		$site_path=dirname($_SERVER['PHP_SELF'])."/graphic/";
		
		if($dh = opendir($path)){
			while (($file = readdir($dh)) !== false) {				
				if($file[0]!="." && filetype($path.$file)!="dir" && eregi("pulsante.*", $file)){
					$button_img .= "<option value=\"$site_path$file\">$file</option>";
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
		
		
		
		
		include('map_editor_visual.php');
	}
	
	function map_edit(){
		global $prefix, $db;
		
		$query = $db->DoQuery("SELECT * FROM {$prefix}map WHERE id='{$_POST['id']}'");
		
		$row = $db->Do_Fetch_Assoc($query);
		
		if(!isset($_POST['selected_id']) || 
				!isset($_POST['selected_x']) ||
				!isset($_POST['selected_y']) ||
				(!isset($_POST['selected_link']) && !isset($_POST['selected_link_static'])) &&
				!isset($_POST['selected_img']) ||
				!isset($_POST['descr'])
				)
			return;
		
		if(!$row){
			//New button
			
			$db->DoQuery("INSERT INTO {$prefix}map id, link, posx, posy, button, link_type, descr, 
									VALUES('$id', '$link', '$posx', '$posy', '$button', '$link_type', '$descr')");	
		}
		else{
			//Modify button	
		}
		
		
	}

?>

