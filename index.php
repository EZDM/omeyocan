<?PHP
	include_once('engine/lib/output.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

	<head>
	<LINK REL="SHORTCUT ICON" HREF="./engine/favicon.ico">
	  <title>Omeyocan</title>
		<meta name="description" content="Gioco di ruolo on-line horror. Entra per 
		sempre nell'oscurita'!" />

		<style type="text/css">
			body{
				background-color: black;
			}

			div, td{
				text-align: center;
			}
			
			#enter{
				position: absolute;
				left: 380px;
				top: 400px;
				width: 220px;
				height: 100px;
			}
			
			#rules{
				position: absolute;
				top: 400px;
				right: 0px;
				width: 180px;
				height: 100px;
			}
			
			#forum{
				position: absolute;
				top: 400px;
				left: 0px;
				width: 230px;
				height: 100px;
			}
			
			#uncitizen{
				position: absolute;
				top: 180px;
				left: 0px;
				width: 300px;
				height: 100px;
			}
			
			#citizen{
				position: absolute;
				top: 180px;
				right: 0px;
				width: 230px;
				height: 100px;
			}

			img {
				border: 0;
			}
			
			#main_img{
				background-image: url('layout/homepage_dark.jpg');
				width: 992px;
				height: 576px;
				position: relative;
				
			}

			.center{
				text-align: center;
				margin-left: auto;
				margin-right: auto;
			}
	
			a:link img, a:visited img, a:hover img{
				border: 0;
				text-decoration: none;	
			}
			
			.copyright{
				font-size: 6pt;
				color: grey;
			}


		</style>

		<script type="text/javascript">

		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-18911231-1']);
		_gaq.push(['_trackPageview']);

		(function() {
		 var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		 ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		 var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		 })();

		</script>
	</head>

	<body>
		<div>
			<table class="center">
				<tr>
					<td colspan="2">
					<div id="main_img">
										
					<a onClick="<?PHP echo popup_open(1028, 728, 'engine','main');?>" href="#"><div id="enter"></div></a>
					
					<a href="forum/"><div id="forum"></div></a>
					<a href="manual/"><div id="rules"></div></a>
					
					<a onClick="<?PHP 
						echo popup_open(484, 702, 'citizen.html','citizen');?>" href="#">
						<div id="citizen"></div></a>
					<a onClick="<?PHP
						echo popup_open(484, 702, 'uncitizen.html','citizen'); ?>" href="#">
						<div id="uncitizen"></div></a>
					</div>
					</td>
				</tr>
					
			</table>
		</div>
		<object>
		  <param name="bgcolor" value="#000000">
			<param name="movie" width="0" height="0" value="./engine/graphic/letmecryhigh.swf">
			<param name="quality" value="high">
			<param name="allowScriptAccess" value="sameDomain">
			<embed src="./engine/graphic/letmecryhigh.swf" width="0" height="0" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowScriptAccess="sameDomain" bgcolor="#000000">
			</embed>
		</object>
	</body>

</html>
