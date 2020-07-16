<?php
	
	$BodyStart = '';
	$BodyEnd = '';

	class LayoutHandler {

		var $assetLayoutSettings = array();
		var $layout = 'default';
		var $content = NULL;
		var $subContent = NULL;
		var $title = 'Untitled';
		var $subTitle = "&nbsp;";
		var $breadCrumbs = null;
		var $titleImage = NULL;
		var $keywords = NULL;
		var $description = NULL;
		var $assetPath = '';
		var $assetID = NULL;
		var $styleSheet = 'main';
		
		
		var $bodyEnd = '';
		var $bodyStart = '';
		var $head = '';
		
		function __construct() {
			global $cfg;
			$this->siteName = $cfg['website_name'];
			$this->keywords = $cfg['keywords'];
			$this->description = $cfg['description'];
		}
	
		function process() {
			global $cfg;
			global $layouts;
			// Process the husk
			
			if (strtolower($this->layout) == 'none') {
				$temp = $this->content;
			} else {
				$original = $this->layout;

				// Strip HTML tags out from the title	
				$this->simpleTitle = preg_replace('/<[^<>]*>/', '', $this->title);

      			if (strlen($GLOBALS['cfg']['currentSiteFolder']) 
					and array_key_exists( $this->layout.'_'.str_replace('/', '', $GLOBALS['cfg']['currentSiteFolder']), $layouts))
				{
					$this->layout = $this->layout.'_'.str_replace('/', '', $GLOBALS['cfg']['currentSiteFolder']);
				}

      			if (array_key_exists( "HTTP_USER_AGENT", $_SERVER) 
					and stristr($_SERVER["HTTP_USER_AGENT"], "MSIE") )
			{
				if(  strlen($GLOBALS['cfg']['currentSiteFolder']) 
					and array_key_exists( $original.'ie_'.str_replace('/', '', $GLOBALS['cfg']['currentSiteFolder']), $layouts))
				{
					$this->layout = $original.'ie_'.str_replace('/', '', $GLOBALS['cfg']['currentSiteFolder']);
				}
//					ss_log_message( "Using Layout ".$this->layout );
			}

			// nasty hack from rex, look for an IE specific layout
			if ( 0 )
			{
				// we are using internet exploder, look for an alternate layout
				$this->layout = $this->layout.'ie_'.str_replace('/', '', $GLOBALS['cfg']['currentSiteFolder']);
				// does it exist?
				if( array_key_exists( $this->layout, $layouts ) &&  array_key_exists( 'directory', $layouts[$this->layout] ) )
				{
					$layoutDirectory = $layouts[$this->layout]['directory'];
					if( file_exists( $layoutDirectory.$layouts[$this->layout]['fileName'] ) )
					{
						// whooo hooo.
					}
					else
						$this->layout = $this->layout.'_'.str_replace('/', '', $GLOBALS['cfg']['currentSiteFolder']);
				}
				else
				{
					$this->layout = $this->layout.'_'.str_replace('/', '', $GLOBALS['cfg']['currentSiteFolder']);
				}
			}

			// need to check if this key exists, if not, redirect home
			if( !array_key_exists( $this->layout, $layouts ) )
			{
				ss_log_message( "Layout missing index '".$this->layout. "' from " );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $layouts );
				die;
			}
			else
			{
				if( !array_key_exists( 'directory', $layouts[$this->layout] ) )
				{
					ss_log_message( "Layout directory missing ".$this->layout );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $layouts[$this->layout] );
					die;
				}
			}

			if( array_key_exists( $this->layout, $layouts ) &&  array_key_exists( 'directory', $layouts[$this->layout] ) )
				$layoutDirectory = $layouts[$this->layout]['directory'];
			else
			{
				ss_log_message( "Layout missing " );
				header( 'Location: /' );
				die;
			}
				
        		$data = array();
				$data['this'] = $this;
				timerStart('Template Processing');
//    			ss_log_message( $_SERVER["HTTP_USER_AGENT"]." using Layout ".$this->layout );
				$temp = processTemplate($layoutDirectory."/".$layouts[$this->layout]['fileName'],$data);
				timerFinish('Template Processing');
				
				// Insert stuff to go before and after the body tags
				$temp = stri_replace('</BODY>',$this->bodyEnd.$GLOBALS['BodyEnd'].'</BODY>',$temp);
				preg_match('/<body[^>]*>/i',$temp,$result1);
				if( array_key_exists( 0, $result1 ) )
					$temp = str_replace($result1[0],$result1[0].$GLOBALS['BodyStart'].$this->bodyStart,$temp);
			
				// Fix up image and stylesheet paths
				$temp = stri_replace('background="images/','background="'.$layoutDirectory.'/Images/',$temp);
				$temp = stri_replace('src="images/','src="'.$layoutDirectory.'/Images/',$temp);
				$temp = stri_replace('href="sty_','href="'.$layoutDirectory.'/sty_',$temp);
			}
			if (ss_optionExists('Email Spam Protection')) {
				// Fix up links to email addresses <a href="mailto:blah@blah.com">blah@blah.com</a>
				while (preg_match('/<A[^>]*mailto:([^@]+)@([^\'"]+)[^>]*>([^@<]+)@([^<]+)<\/a>/i',$temp,$result)) {
					//ss_log_message_r($result);
					// $result[0] = <a href="mailto:someone@somewhere.com">someoneelse@somewhereelse.com</a>
					// $result[1] = someone
					// $result[2] = somewhere.com
					// $result[3] = someoneelse
					// $result[4] = somewhereelse.com
					//print($result[4]);
					
					$user = ss_JSStringFormat($result[1]);
					$domain = ss_JSStringFormat(str_replace('.','~',$result[2]));
					$user2 = ss_JSStringFormat($result[3]);
					$domain2 = ss_JSStringFormat(str_replace('.','~',$result[4]));
					$temp = stri_replace($result[0],"<script type=\"text/javascript\" language=\"Javascript\">ed='$domain';var re = new RegExp ('~');while(ed.search(re) != -1) {ed = ed.replace(re,'.');}eu='$user';ed2='$domain2';while(ed2.search(re) != -1) {ed2 = ed2.replace(re,'.');}eu2='$user2';document.write('<a href=\"'+'mai'+'lto:'+eu+'@'+ed+'\">'+eu2+'@'+ed2+'</a>');</script>",$temp);
				}
				
				// Fix up links to email addresses <a href="mailto:blah@blah.com">
				$regex = '/(<A[^>]*mailto:([^@]+)@([^\'"]+)[\'"]+[^>]*>)(.*)<\/a>/iU';
				while (preg_match($regex,$temp,$result)) {
					//ss_log_message_r($result);
					/*(
					    [0] => <a href="mailto:testt@tester.com">hmmm</a>
					    [1] => <a href="mailto:testt@tester.com">
					    [2] => testt
					    [3] => tester.com
					    [4] => hmmm
					)*/
//					break;
					$user = ss_JSStringFormat($result[2]);
					$domain = ss_JSStringFormat(str_replace('.','~',$result[3]));
					$linkContents = ss_JSStringFormat($result[4]);
					$temp = stri_replace($result[0],"<script type=\"text/javascript\" language=\"Javascript\">ed='$domain';eu='$user';var re = new RegExp ('~');while(ed.search(re) != -1) {ed = ed.replace(re,'.');}document.write('<a href=\"'+'mai'+'lto:'+eu+'@'+ed+'\">$linkContents<'+'/a>');</script>",$temp);
				}
			}


//				if( in_array( $_SERVER['REQUEST_URI'], $cfg['AllowCache'] ) )
					$noBrowserCache = '';
//				else
//					$noBrowserCache = 
//						'<meta http-equiv="Pragma" CONTENT="no-cache"/>'.
//						'<meta http-equiv="Cache-Control" CONTENT="no-cache"/>'.
//						'<meta http-equiv="Expires" CONTENT="Mon, 06 Jan 1990 00:00:01 GMT"/>';

//				$temp = stri_replace('<HEAD>','<HEAD>'.$noBrowserCache,$temp);
	
				// Set the base url so the browser knows what the current 
				// directory actually is
				$baseURL = $cfg['currentServer'];
				$temp = stri_replace('<head>',"<head><base href=\"$baseURL\"/>".$noBrowserCache,$temp);
			
			
			return $temp;
		}
	
		function startBuffering() {
			ss_ob_start();
		}
		
		function stopBuffering() {
			$this->content = ob_get_contents();
			ss_ob_end_clean();
		}
	
	}
?>
