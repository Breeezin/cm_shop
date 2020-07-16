<?php
	global $classes;
	$classes = array();
	$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
	$directories = array("System/Classes", "System/Libraries", $rootFolder."Custom/Classes", $rootFolder."Custom/Libraries");
	patternFileFind($directories, $classes, "/^_(.+)\.php$/");
	
	$layouts = array();
	$directories = array("System/Classes", "Custom/ContentStore/Layouts", "Custom/Classes", "Custom/ContentStore/Templates");
	patternFileFind($directories, $layouts, "/^lyt_(.+)\.php$/");
	
	
	ss_log_message_r("classes", $classes);
	
	// Loop through the classes and insert their services into 
	// the services structure
	$services = array();
	foreach ($classes as $class) {
        /*
	    if (ss_isItUs()) {
            echo 'class: ';
            print_r($class);
            echo '<br>';
        }
        */
        $className = $class['name'];
		$classDirectory = $class['directory'];		
		
		// Include the class definition 
		require_once($classDirectory.'/'.$class['fileName']);
		
		$path = explode('/',$classDirectory);
		
		
		// Get the services that this class provides
		if (array_search('Classes',$path)!==false) {
			
			echo "$className<br/>";
			$classServices = call_user_func(array(new $className,'exposeServices'));
			
			// Apply the class name to the services
			
			$serviceArray = array_keys($classServices);
			for ($serviceIndex = 0; $serviceIndex < count($serviceArray); $serviceIndex++) {
				$classServices[$serviceArray[$serviceIndex]]['class'] = $className;
				if (!array_key_exists('level',$classServices[$serviceArray[$serviceIndex]])) {
					// Assign a default level of 1
					$classServices[$serviceArray[$serviceIndex]]['level'] = 1;
				}
			}
			
			// Add the services into the services array
			for ($serviceIndex = 0; $serviceIndex < count($serviceArray); $serviceIndex++) {
				$serviceName = $serviceArray[$serviceIndex];
				// Check if the service already exists
				if (array_key_exists($serviceName,$services)) {
					// If this service has a higher level than the existing service
					// then override it
					if ($classServices[$serviceName]['level'] > $services[$serviceName]['level']) {
						$services[$serviceName] = $classServices[$serviceName];
					}
		 		} else {
					// Add the service into the array
					$services[$serviceName] = $classServices[$serviceName];
				}
			}
		}
	}	
	
?>
