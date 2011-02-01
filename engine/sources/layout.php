<script language="JavaScript">
<!--
// F. Permadi May 2000
function getFlashMovieObject(movieName)
{
  if (window.document[movieName]) 
  {
    return window.document[movieName];
  }
  if (navigator.appName.indexOf("Microsoft Internet")==-1)
  {
    if (document.embeds && document.embeds[movieName])
      return document.embeds[movieName]; 
  }
  else // if (navigator.appName.indexOf("Microsoft Internet")!=-1)
  {
    return document.getElementById(movieName);
  }
}

function StopTardis()
{
  var flashMovie=getFlashMovieObject("tardisFlash");
  flashMovie.StopPlay();
}

function PlayTardis()
{
  var flashMovie=getFlashMovieObject("tardisFlash");
  flashMovie.Play();
}

saved_src='';

function ShowPopup(hoveritem, locat)
{
	if (!e) var e = window.event;
	if (e.pageX || e.pageY)   {
		posx = e.pageX;
		posy = e.pageY;
	}
	else if (e.clientX || e.clientY)  {
		posx = e.clientX + document.body.scrollLeft
			+ document.documentElement.scrollLeft;
		posy = e.clientY + document.body.scrollTop
			+ document.documentElement.scrollTop;
	}
	hp = document.getElementById("position");

	// Set popup to visible
	hp.style.top = posy - 30;
	hp.style.left = posx + 15;
	hp.style.zIndex = 1;
	hp.innerHTML = locat;

	hp.style.visibility = "Visible";
}

function location_over(hoveritem) {
	saved_src=hoveritem.src;
	hoveritem.src='./graphic/pulsante_over.gif';

}

function HidePopup(hoveritem)
{
	hp = document.getElementById("position");
	hp.style.visibility = "Hidden";	
}

function location_out(hoveritem) {
	hoveritem.src=saved_src;
}

//-->
</script>

<!-- <div id="debug"></div> -->
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
  codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0"
  id="tardisFlash" width=0 height=0> 
              <param name=movie value="./graphic/tardis.swf"> 
              <param name=quality value=high> 
              <param name=play value=false> 
              <param name=bgcolor value=#FFFFFF> 
              <embed play=false swliveconnect="true" name="tardisFlash"
                src="./graphic/tardis.swf" quality=high bgcolor=#FFFFFF  
                width=0 height=0 type="application/x-shockwave-flash"  
                pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"> 
              </embed > 
 </object >
      
<?PHP 
  if($x7c->permissions['admin_panic'] ||$x7c->permissions['admin_objects']){
    echo '
      <div id="adminbtn" style="position: absolute; top: 10px; left: 1px;">
        <a onClick="'.popup_open(800, 620, "index.php?act=admincp", "admincp", "yes").'";>Administration
        </a>
      </div>';

    echo '
      <div id="anagrafe" style="position: absolute; top: 30px; left: 1px;">
        <a onClick="'.popup_open(450, 500, "index.php?act=memberlist", "memberlist").'";>Anagrafe
        </a>
      </div>';
    

		echo '
      <div id="shop_debug" style="position: absolute; top: 50px; left: 1px;">
        <a onClick="'.popup_open(800, 720, "index.php?act=shop", "shop").'";>Shop
        </a>
      </div>';
  }

	include_once('./sources/graphic_elements.php');

   if($x7s->status=="Morto"){
    echo '
      <script type="text/javascript">        
        '.popup_open(500, 150, "index.php?act=resurgo", "resurgo").'
      </script>';
    }
?>
   
<!-- PULSANTI -->

<?PHP
	if (!$x7c->settings['panic']) {
		echo '
			<div id="hintbtn" style="position: absolute; top: 672px; left: 283px;">
			<a onClick="'.popup_open(446, 558, "index.php?act=hint", "hint").'"> 
				<img src="./graphic/hint_button.gif" 
				onMousemove="javascript: ShowPopup(this, \'Chiedilo ad Aya\');"
				onMouseout="javascript: HidePopup(this);">
			</a>
			</div>';
	}
?>


<div id="logoutbtn" style="position: absolute; top: 235px; left: 932px;">
  <a onClick="javascript: window.location.href='index.php?act=logout';">
    <img src="<?PHP echo $logout_src;?>"
		onMouseOver="javascript: this.src='<?PHP echo $logout_over_src;?>'"
		onMouseOut="javascript: this.src='<?PHP echo $logout_src; ?>'">
  </a> 
</div>

<a href="index.php">
  <img style="position:absolute; top:320px; left:896px;" 
    src="<?PHP echo $mappa;?>"
		onMouseOver="javascript: this.src='<?PHP echo $mappa_over;?>'"
		onMouseOut="javascript: this.src='<?PHP echo $mappa;?>'">
</a>
      
<a onClick="<?PHP echo popup_open(800, 620, "index.php?act=boards", "board"); ?>"> 
  <img style="position:absolute; top:377px; left:896px;"
    src="<?PHP echo $bacheca;?>" 
		onMouseOver="javascript: this.src='<?PHP echo $bacheca_over;?>'"
		onMouseOut="javascript: this.src='<?PHP echo $bacheca;?>'">
</a>
      
<a onClick="<?PHP echo popup_open(450, 500, "index.php?act=memberlist&room",
	"memberlist"); ?>">
  <img style="position:absolute; top:431px; left:896px;"
    src="<?PHP echo $presenti;?>"
		onMouseOver="javascript: this.src='<?PHP echo $presenti_over;?>'"
		onMouseOut="javascript: this.src='<?PHP echo $presenti;?>'">
</a>
      
<a onClick="<?PHP echo popup_open(500, 680, "index.php?act=sheet", "sheet_other"); ?>">
  <img style="position:absolute; top:485px; left:896px;" 
  src="<?PHP echo $scheda;?>"
	onMouseOver="javascript: this.src='<?PHP echo $scheda_over;?>'"
	onMouseOut="javascript: this.src='<?PHP echo $scheda;?>'">
</a>
      
<a onClick="<?PHP echo popup_open(488, 650, "index.php?act=mail", "MsgCenter"); ?>">
  <img id="posta" style="position:absolute; top:573px; left:911px;"
  src="<?PHP echo $posta_no; ?>">
</a>
    
<div align="center" id="copyrigth" style="visibility: visible;">
  <marquee onMouseOver="this.stop();" onMouseOut="this.start();">
    Game engine by:
    <a href="http://netgroup.polito.it/Members/niccolo_cascarano"
      target="_blank">
      Niccol&ograve; Cascarano
    </a> 
    Graphic by: Federico Gori
    Powered By
    <a href="http://www.x7chat.com/" target="_blank">
      X7 Chat
    </a> 
    2.0.5 &copy; 2004 By The
    <a href="http://www.x7chat.com/" target="_blank">
      X7 Group
    </a>
  </marquee>

</div>

