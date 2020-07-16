<?php
	// Generated file containing list of all services supported
	global $services;
	global $classes;
	global $layouts;
	$services = array();
	$classes = array();
	$layouts = array();
	include('Custom/Resources.php');

	Class Request {
		
		var $value = NULL;
		var $display = NULL;
		var $cache = 'No';
		
		function __construct($act,$parameters = array()) {
		
			global $services;
			global $classes;
			global $layouts;
			
			global $time;
			global $cfg;
	
			// record how deep we are in fuseaction requests.. so we can prevent some fuseactions being called from a URL directly
			$GLOBALS['RequestDepth']++;
			
			timerStart('Overhead');
			$result = NULL;
			
			// If the act is provided by any of our classes then try to run it
			if (array_key_exists($act,$services) == TRUE) {
			
				if (array_key_exists('visibility',$services[$act])) {
					switch ($services[$act]['visibility']) {
						case 'private':
							if ($GLOBALS['RequestDepth'] == 1) {
								trigger_error('Unauthorised act call.');
								die('Unauthorised act call.');	
							}
							break;
						case 'im':
							if (!ss_isItUs()) {
								trigger_error('Unauthorised act call.');
								die('Unauthorised act call.');	
							}
							break;
						default:
							trigger_error('Unknown visibility type');
					}
				}
					
				if ($cfg['cache']) {
					timerStart('Caching');
					$cacheKey = md5($act).'.'.md5(serialize($parameters).serialize($cfg)).'.cache';
					timerFinish('Caching');
				}
			
/*		argh
				if ($cfg['cache'] && file_exists("Custom/Cache/$cacheKey")) {
					// Use the cached version
					
					$filename = "Custom/Cache/$cacheKey";
					$fp = fopen($filename,"r");
					$cacheContents = fread($fp, filesize ($filename));
					fclose ($fp);
					
					// Deserialize and decompress the cache				
					$this = unserialize(gzuncompress($cacheContents));
					
				} else */ {
					$className = $services[$act]['class'];
					// Include the class definition 
					
					
					timerStart('ClassLoading');										
					require_once($classes[$className]['directory']."/_".$classes[$className]['name'].".php");					
					
					// Make an instance of the class	
					$temp = new $className($parameters);
					
					// Set some initial properties for the class				
					$temp->ATTRIBUTES = &$parameters;
					$temp->atts = &$parameters;
					$temp->classDirectory = $classes[$className]['directory'];
					$temp->display = new LayoutHandler();
					timerFinish('ClassLoading');
		
					// Call the input filter
					timerStart('InputFilter');
					$temp->inputFilter();
					timerFinish('InputFilter');
		
					// Start buffering the output
					$temp->display->startBuffering();

					// Do the action requested
					timerStart("act: ($act)");
					$this->value = call_user_func(array(&$temp,$services[$act]['method']));
					timerFinish("act: ($act)");
					$this->cache = $temp->cache;
					
					// Grab all the output and call the output filter
					timerStart('LayoutHandler');
					$temp->display->stopBuffering();
					$temp->display->content = $temp->outputFilter($temp->display->content);
					
					// If NoHusk then set the layout to 'None'
					if (array_key_exists('NoHusk',$temp->ATTRIBUTES) && ($temp->ATTRIBUTES['NoHusk'] == TRUE)) {
						$temp->display->layout = 'None';
					}
					
					// Insert the content into the correct layout 
//				    list($usec, $sec) = explode(" ",microtime()); 
//					print("starting layout handler".$usec."<BR>");
					$this->display = $temp->display->process();
					timerFinish('LayoutHandler');
//				    list($usec, $sec) = explode(" ",microtime()); 
//					print("finish layout handler".$usec."<BR>");
	
					// Store to Application cache
					if ($cfg['cache'] && ($this->cache == 'Application')) {
						timerStart('Caching');
						$fp = fopen("Custom/Cache/$cacheKey","w");
						
						// Write a compressed serialized version
						// of the output of the fuseaction
						fwrite($fp, gzcompress(serialize($this)));
						fclose ($fp);
						timerFinish('Caching');
					}
					
				}
			
			} else {
				// A few system fuseactions
				switch ($act) {
					case 'FlushCache' :
					
						$path = 'Custom/Cache';
						if (ss_deleteFiles($path)) {
							print('Cache cleaned.<br>');
						} else {
							print('Cache not cleaned.<br>');
						}
						
						$path = 'Custom/Cache/Templates';
						if (ss_deleteFiles($path)) {
							print 'Template folder not found.';
						} else {
							print 'Template folder not found.';
						}
						break;
					case 'Session' :
						print('<PRE>$_SESSION = ');
						print_r($_SESSION);
						print('</PRE>');
						break;
					case 'FlushBackStack' :
						unset($_SESSION['BackStack']);
						print('BackStack Flushed');
						print('<PRE>$_SESSION = ');
						print_r($_SESSION);
						print('</PRE>');
						break;
					case 'BackStack' :
						// Display the Back Stack
						print('<PRE>');
						print_r($_SESSION['BackStack']);
						print('</PRE>');
						break;
					case 'ResetServices' :
					case 'Reset' :
					
						/* Clean the cache */
						$path = 'Custom/Cache';
						if (ss_deleteFiles($path)) {
							print('Cache cleaned.<br>');
						} else {
							print('Cache not cleaned.<br>');
						}
						
						$path = 'Custom/Cache/Templates';
						if (ss_deleteFiles($path)) {
							print 'Templates cleaned<BR>';
						} else {
							print 'Templates not cleaned<BR>';
						}
						
						$path = 'Custom/Cache/Incoming';
						if (ss_deleteFiles($path)) {
							print 'Incoming cleaned<BR>';
						} else {
							print 'Incoming not cleaned<BR>';
						}
					
						require('System/Core/ExposeServices.php');
			
						// Generate PHP code that would generate the $services array
						$servicesDump = ss_VarExport($services);
						$classesDump = ss_VarExport($classes);
						$layoutsDump = ss_VarExport($layouts);
						// Write the services out to a file
						$fp = fopen('Custom/Resources.php',"w");
						fwrite($fp,"<?php \n");
						fwrite($fp,"\$classes = $classesDump;\n");
						fwrite($fp,"\$services = $servicesDump;\n");
						fwrite($fp,"\$layouts = $layoutsDump;\n");
						fwrite($fp,"?>");
						fclose($fp);
						
						print('<P>Services, Classes and Layouts have been reset.</P>');						
						break;
						
						/*
						$fp = fopen('Services.php',"w");
						fwrite($fp,'<?php $services = ');
						fwrite($fp,$servicesDump);
						fwrite($fp,";\n");
						fwrite($fp,'$classes = ');
						fwrite($fp,$classesDump);
						fwrite($fp,'?>');
						fclose($fp);
						*/
					case 'Services'	:
						// Display list of services
						print('<PRE>');
						print_r($services);
						print('</PRE>');
						break;
					case 'ShowDiskInfo'	:
						// Display list of services
						print('<PRE>');
						$temp = ss_getDiskSpaceUsage();
						ss_DumpVar($temp);
						print('</PRE>');
						break;	
					case 'Layouts'	:
						// Display list of services
						print('<PRE>');
						print_r($layouts);
						print('</PRE>');
						break;
					default :
						// Display an appropriate error message
//						print('Unknown act: '.$act);
						ss_log_message( 'Unknown act: '.$act );
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, debug_backtrace() );
				}
				

			}

			timerFinish('Overhead');

			$GLOBALS['RequestDepth']--;
			
			return $this;
		
		}
	
	}	
	
		

?>
