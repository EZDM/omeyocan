<?PHP

      function resurgo_main(){
                global $x7s;
                $body='';
                if($x7s->status=="Morto"){
                                $body='<table><tr><td id="inner_resurgo">Risorgerai tra:<br><span class="CountDownPanel" id="CountDownPanel" time_format="%h:%m:%s"></span></td></tr></table>
                                      <script type="text/javascript" src="sources/AdvancedCountDown.js"> </script>
                                      <script type="text/javascript">

                                              ActivateCountDown("CountDownPanel", '.$x7s->resurgo.', null);
                                      
                                      
                                      </script>';
                }

                print_page($body);
      }


        function print_page($body){
		global $print,$x7c,$x7s;
		
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		echo "<html dir=\"$print->direction\"><head><title>{$x7c->settings['site_name']} Descrizione stanza</title>";

		srand(time()+microtime()/date("s"));
		$i=rand(1,4);
		
		$mail_style = '
		<style type="text/css">
                        body {
                            background: black;
                            color: white;			
                        }

                        #resurgo{
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 500px;
                            height: 150px;
                            background: url(./graphic/banner_resurgo'.$i.'.jpg);
                            border: 0;
                            margin: 0;
                            
                        }

                        #inner_resurgo{
                                color: #c88b07;
                                text-align: center;
                                font-weight: bold;
                                font-size: 16pt;
			}

			table{
                                width: 100%;
                                height: 100%;
			}
			
		</style>
		';
		
		echo $mail_style;
		
		echo '</head><body>
 			<div id="resurgo">

 			';
 			
		
		echo $body;
		echo '
			
		</div>
		</body>
			</html>';
	}


?>