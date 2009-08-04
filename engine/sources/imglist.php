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
	global $print, $x7s, $x7c;

	$image_dir="/images/";

	if(isset($_GET['subdir']) && $_GET['subdir']!="")
	$image_dir.=$_GET['subdir']."/";

	if($x7c->permissions['admin_panic'] || authorized($image_dir, $x7s->user_group)){
			
		$basedir=dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
			
		//Eventualmente estendere per le sottodirectory
		$file_path=$basedir.$image_dir;
			
		//TODO fileupdate
		if(isset($_GET['file'])){
			file_upload($file_path);
		}
		elseif(isset($_GET['delete'])){
			file_delete($file_path);
		}
			
		$site_path=dirname($_SERVER['PHP_SELF']).$image_dir;
		file_list($file_path,$site_path);
			
	}
	else{

		$head="Errore";
		$body="Non sei autorizzato a vedere questa pagina";
		$print->normal_window($head,$body);
	}



}

function file_list($path,$url){
	global $print;
	$head="Immagini in /immagini/";

	$body=$path."<br>\n";
	$subdir="";
	if(isset($_GET['subdir']) && $_GET['subdir']!=""){
		$subdir="&subdir=".$_GET['subdir'];
		$head.=$_GET['subdir'];
	}


	if((bool) ini_get('file_uploads')){

		$phpmaxsize = ini_get('upload_max_filesize')."B";

		$body='	<div id="uploadtitle"><strong>File Upload</strong> (Max Filesize: '.$phpmaxsize.')</div>
				<form method="post" action="index.php?act=images&file=1'.$subdir.'" enctype="multipart/form-data">
				<input type="file" name="file" /> <input type="submit" value="Upload" />
				</form>';
	}
		
	$body.='	<script language="JavaScript">
				function putimage(url) {
					if(opener.name == "admincp")
						opener.document.forms[0].image_url.value=url;
					else
    						opener.document.chatIn.msgi.value=opener.document.chatIn.msgi.value +" £"+ url +" ";
    					window.close(self);
				}

				function do_delete(url){
                                        if(confirm("Vuoi davvero cancellare il file?\n\nATTENTO: se il file è parte di un oggetto o e\' ancora visualizzato in qualche stanza, comparira il box di \"oggetto mancante\""))
                                                window.location.href=url;
                                }
				</script>';


	$i=0;
	$maxcol=6;

	$body.="<table style=\"width: 750px;\">\n";

	$dir="<tr><td><h3>Categorie</h3><ul>";
	if($subdir!="")
	$dir.="<li><a href=\"index.php?act=images\">/</a></li>";
	$img="";

	if($dh = opendir($path)){
		while (($file = readdir($dh)) !== false) {

			if($file[0]!="." && filetype($path.$file)!="dir"){
				if($i % $maxcol == 0){
					$img.="<tr>";
				}
					
				$img.= "<td align=\"center\"><a onClick=\"putimage('$url$file');\"><img src=\"$url$file\" width=100>". "<br>$file<br></a><a onClick='javascript: do_delete(\"index.php?act=images{$subdir}&delete=$file\")'>[Delete]</a><td>\n";
					
				$i++;

				if($i % $maxcol == 0){
					$img.="</tr>\n";
				}
					
			}
			elseif($file[0]!="." && filetype($path.$file)=="dir"){
				$dir.="<li><a href=\"index.php?act=images&subdir=$file\">$file</a></li>";
			}

		}
			
		closedir($dh);
	}

	$dir.="</ul><hr></td></tr>";

	$body.=$dir.$img;
	$body.="</table>";
	$print->normal_window($head,$body);
}

function file_upload($path){
	global $print;

	if(eregi("[^a-zA-Z0-9\-_\.]",$_FILES['file']['name'])){
		$print->normal_window("Errore", "Il nome del file non puo' avere spazi o caratteri speciali");
		return;
	}

	if($_FILES['file']['type'] == "image/gif" || $_FILES['file']['type'] == "image/png" || $_FILES['file']['type'] == "image/jpeg" || $_FILES['file']['type'] == "image/pjpeg"){

		$size = getimagesize($_FILES['file']['tmp_name']);
		if($size[0] > 650){
			$print->normal_window("Errore","L'immagine &egrave; troppo larga");
			return;
		}
			
		move_uploaded_file($_FILES['file']['tmp_name'],$path.$_FILES['file']['name']);
			
		$print->normal_window("Upload ok",$path.$_FILES['file']['name']);
	}
	else{
		$print->normal_window("Errore", "Tipo di file errato");
	}
}

function file_delete($path){
	global $print;

	if(isset ($_GET['delete']) && unlink($path.$_GET['delete'])){
		$print->normal_window("Delete ok",$_GET['delete']);
	}
	else{
		$print->normal_window("Errore", "Impossibile cancellare il file specificato");
	}
}


function authorized($subdir, $group){
	global $prefix, $db;
	 
	$query = $db->DoQuery("SELECT count(*) AS cnt FROM {$prefix}imgpermission WHERE groupname='$group' AND subdir='$subdir'");
	 
	$row = $db->Do_Fetch_Assoc($query);
	 
	if($row['cnt']>0)
	return true;
	else
	return false;
}

?>