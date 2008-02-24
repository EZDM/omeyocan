<?PHP


	function imglist_main(){
		global $print, $x7s, $x7c;
		
		$image_dir="/images/";
		
		if($x7c->permissions['admin_panic']){
			
			$basedir=dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
			
			//Eventualmente estendere per le sottodirectory
			$file_path=$basedir.$image_dir;
			
			//TODO fileupdate
			if(isset($_GET['file'])){
				file_upload($file_path);
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
		$head="Immagini in $path";
		
		$body=$path."<br>\n";
		
		if((bool) ini_get('file_uploads')){
		
			$phpmaxsize = ini_get('upload_max_filesize')."B";
		
			$body='	<div id="uploadtitle"><strong>File Upload</strong> (Max Filesize: '.$phpmaxsize.')</div>
				<form method="post" action="index.php?act=images&file=1" enctype="multipart/form-data">
				<input type="file" name="file" /> <input type="submit" value="Upload" />
				</form>';
		}
			
		$body.='	<script language="JavaScript">
				function putimage(url) {
    					opener.document.chatIn.msgi.value=opener.document.chatIn.msgi.value +" £"+ url +" ";
    					window.close(self);
				}
				</script>';
		
		
		$i=0;
		$maxcol=6;
		
		$body.="<table>\n";
		
		if($dh = opendir($path)){
			while (($file = readdir($dh)) !== false) {
				
				if($file[0]!="." && filetype($path.$file)!="dir"){
					if($i % $maxcol == 0){
					$body.="<tr>";
					}
					
					$body.= "<td align=\"center\"><a onClick=\"putimage('$url$file');\"><img src=\"$url$file\" width=100>". "<br>$file<br></a><td>\n";
					
					$i++;
				
					if($i % $maxcol == 0){
						$body.="</tr>\n";
					}
					
				}
				
        		}
			
			closedir($dh);
		}
		
		$body.="</table>";
		$print->normal_window($head,$body);
	}
	
	function file_upload($path){
		global $print;
		
		if($_FILES['file']['type'] == "image/gif" || $_FILES['file']['type'] == "image/png" || $_FILES['file']['type'] == "image/jpeg" || $_FILES['file']['type'] == "image/pjpeg"){
		
			$size = getimagesize($_FILES['file']['tmp_name']);
			if($size[0] > 800){
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
	
?>