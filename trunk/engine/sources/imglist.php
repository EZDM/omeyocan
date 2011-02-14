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

function imglist_main(){
	global $print, $x7s, $x7c, $x7p;

	$image_dir="/images/";

	if(isset($_GET['subdir']) && $_GET['subdir']!="")
	$image_dir.=$_GET['subdir']."/";

	if($x7c->permissions['admin_panic'] || authorized($image_dir, $x7p->profile['usergroup'])){
			
		$basedir=dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
			
		//Eventualmente estendere per le sottodirectory
		$file_path=$basedir.$image_dir;
		
		$error = "<p style=\"color: red; font-weight: bold;\">";
		if(isset($_GET['file'])){
			$error .= file_upload($file_path);
		}
		elseif(isset($_GET['delete'])){
			$error .= file_delete($file_path.$_GET['delete']);
		}
		elseif(isset($_POST['multidel'])){
			foreach ($_POST['multidel'] as $file){
				$error .= file_delete($file_path.$file);
			}
		}
		$error .= "</p>";
			
		$site_path=dirname($_SERVER['PHP_SELF']).$image_dir;
		$output = file_list($file_path, $site_path);

		$body = $error.$output['body'];
		$head = $output['head'];

		$print->normal_window($head, $body);
			
	}
	else{
		return "Non sei autorizzato a vedere questa pagina <br>";
	}



}

function file_list($path,$url){
	global $print;
	$head="Immagini in /immagini/";

	$body=$path."<br>\n";
	$subdir="";
	if(isset($_GET['subdir']) && $_GET['subdir']!=""){
		$subdir=$_GET['subdir'];
		$head.=$_GET['subdir'];
	}


	if((bool) ini_get('file_uploads')){

		$phpmaxsize = ini_get('upload_max_filesize')."B";

		$body='	<div id="uploadtitle"><strong>File Upload</strong> (Max Filesize: '.$phpmaxsize.')</div>
				<form method="post" action="index.php?act=images&file=1&subdir='.$subdir.'" enctype="multipart/form-data">
				<input type="file" name="file" /> <input type="submit" value="Upload" />
				</form>';
	}
		
	$body.='	<script language="JavaScript">
				function putimage(url) {
					if(opener.name == "admincp")
						opener.document.forms[0].image_url.value=url;
					else
    						opener.document.chatIn.msgi.value=opener.document.chatIn.msgi.value +" £"+ url +"; ";
    					window.close(self);
				}

				function do_delete(url){
          if(confirm("Vuoi davvero cancellare il file?\n\nATTENTO: se il file è parte di un oggetto o e\' ancora visualizzato in qualche stanza, comparira il box di \"oggetto mancante\""))
          window.location.href=url;
        }  
				
				function do_multidelete(url){
          return confirm("Vuoi davvero cancellare i files?\n\nATTENTO: se i files sono parte di un oggetto o sono ancora visualizzati in qualche stanza, comparira il box di \"oggetto mancante\"");
        } 

				function flash_preview(id, url) {
					document.getElementById(id).innerHTML = \'<object>\
							<param name="movie" value="\' + url + \'" width="100" height="100">\
							<param name="quality" value="high">\
							<param name="allowScriptAccess" value="sameDomain" />\
							<param name="allowFullScreen" value="True" />\
							<embed src="\' + url + \'" play="false" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowScriptAccess="sameDomain" allowFullScreen="True" width="100" height="100">\
							</embed>\
						</object>\';

				}
				</script>';


	$i=0;
	$maxcol=5;

	$body.="<table style=\"width: 750px;\">\n";

	$dir="<tr><td><h3>Categorie</h3><ul>";
	if($subdir!=""){
		$dir.="<li><a href=\"index.php?act=images\">/</a></li>";
		
		$prev_array = explode("/", $subdir);
		$previous ="";
		for($i=0; $i<(count($prev_array)-1);$i++){
			if($i>0)
				$previous .= "/";
			$previous .=$prev_array[$i];
		}
			
		$dir.="<li><a href=\"index.php?act=images&subdir=$previous\">../</a></li>";
	}
	$img="<form action=\"./index.php?act=images&subdir=".$subdir.
		"\" method=\"post\" name=\"multidelete\" onSubmit=\"return do_multidelete();\">
		<tr><td><input type=\"submit\" value=\"Multi delete\"></td></tr>";

	if($dh = opendir($path)){
		while (($file = readdir($dh)) !== false) {
			$file_array[]=$file;
		}
		
		natcasesort($file_array);
		
		$sep="";
		if($subdir!="")
			$sep="/";

		$flash_id = 0;
	
		foreach($file_array as $file){

			if($file[0]!="." && filetype($path.$file)!="dir"){
				if($i % $maxcol == 0){
					$img.="<tr>";
				}
				
				if(preg_match("/swf$/i", $path.$file)){
					$img.= "			
						<td align=\"center\" width=\"110\">
						<a onClick=\"javascript: flash_preview('flash_id$flash_id', '".
						$url.$file."');\">
						<div id=\"flash_id$flash_id\" style=\"background: url('./graphic/flash_preview.gif'); width: 100px; height: 100px;\">
						</div></a>

						<input type=\"checkbox\" name=\"multidel[]\" value=\"$file\">
						<br>
						<a onClick=\"putimage('$url$file');\">
						$file</a><br>
						<a onClick='javascript: do_delete(\"index.php?act=images&subdir={$subdir}&delete=$file\")'>[Delete]</a><td>\n";
					$flash_id++;
				}
				else{
					$img.= "
						<td align=\"center\" width=\"110\">
						<input type=\"checkbox\" name=\"multidel[]\" value=\"$file\">
						<a onClick=\"putimage('$url$file');\"><img src=\"$url$file\" width=\"100\"> 
						<br>$file<br></a><a onClick='javascript: do_delete(\"index.php?act=images&subdir={$subdir}&delete=$file\")'>[Delete]</a><td>\n";
				}
					
				$i++;

				if($i % $maxcol == 0){
					$img.="</tr>\n";
				}
					
			}
			elseif($file[0]!="." && filetype($path.$file)=="dir"){
				$dir.="<li><a href=\"index.php?act=images&subdir=$subdir$sep$file\">$file</a></li>";
			}

		}
			
		closedir($dh);
	}

	$img .= "</form>";
	$dir.="</ul><hr></td></tr>";

	$body.=$dir.$img;
	$body.="</table>";

	$output['head'] = $head;
	$output['body'] = $body;
	return $output;
}

function file_upload($path){
	global $print;

	if(eregi("[^a-zA-Z0-9\-_\.]",$_FILES['file']['name'])){
		return "Errore: il nome del file non puo' avere spazi o caratteri speciali<br>";
	}

	if($_FILES['file']['type'] == "image/gif" || $_FILES['file']['type'] == "image/png" || $_FILES['file']['type'] == "image/jpeg" || $_FILES['file']['type'] == "image/pjpeg" || $_FILES['file']['type'] == "application/x-shockwave-flash"){

		/*$size = getimagesize($_FILES['file']['tmp_name']);
		if($size[0] > 650){
			return "L'immagine &egrave; troppo larga<br>";
		}*/
			
		move_uploaded_file($_FILES['file']['tmp_name'],$path.$_FILES['file']['name']);
			
		return "Upload ok ".$_FILES['file']['name']."<br>";
	}
	else{
		return "Errore: tipo di file errato<br>";
	}
}

function file_delete($path){
	global $print;

	if(unlink($path)){
		return "Delete ok ". basename($path). "<br>";
	}
	else{
		return "Errore: impossibile cancellare il file specificato<br>";
	}
}


function authorized($subdir, $group){
	global $prefix, $db;
	 
	$query = $db->DoQuery("SELECT groupname FROM {$prefix}imgpermission WHERE subdir='$subdir'");
	 
	$row = $db->Do_Fetch_Assoc($query);
	 
	if(in_array($row['groupname'],$group))
		return true;
	else
		return false;
}

?>
