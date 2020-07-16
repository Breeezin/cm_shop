<?php

$this->param('Auth','');

if($this->ATTRIBUTES['Auth'] != 'asdf7aisf87f43lqjflk0834r43x56r04040843ri43rrk0439r-3x4')
    die();

$this->param('JustList','yes');
$this->param('PrintList','');

	if($_SERVER['SERVER_ADDR'] == '203.97.91.81') {
        $link = mysql_connect('localhost', 'admin', '4des5ewq')
            or die('Could not connect: ' . mysql_error());
    } else {
        $link = mysql_connect('localhost', 'root', '08ed893c')
            or die('Could not connect: ' . mysql_error());
    }

echo 'Connected successfully<br>';
// mysql_select_db('my_database') or die('Could not select database');

// Performing SQL query
$query = 'SELECT * FROM my_table';
$result = mysql_query("show databases") or die('Query failed: ' . mysql_error());


     $Q_AllDatabases = query("show databases");
    $databases = array();
    $usedOptions = array();

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
        $databases[$row['Database']] = $row['Database'];
    }


mysql_free_result($result);


	$options = array();
    $printList = '';
    foreach($databases as $db){

        $result2 = mysql_query("show tables from $db like 'configuration'") or die('Query failed: ' . mysql_error());

        $R_AllTables =  mysql_fetch_array($result2, MYSQL_ASSOC);
        if($R_AllTables) {
            foreach( $R_AllTables as $table) {
                $result = mysql_query("select cfg_options, cfg_website_name, cfg_plaintext_server from $db.$table") or  $result = mysql_query("select cfg_options, cfg_website_name, cfg_folder_name as cfg_plaintext_server from $db.$table") or

                die('Query failed: ' .mysql_error());


                $usedOptions = mysql_fetch_array($result, MYSQL_ASSOC);
                mysql_free_result($result);

				$txtOptions = str_replace(chr(13).chr(10),chr(10),$usedOptions['cfg_options']);
				$newLine = chr(10);
				$arrayOptions = ListToArray($txtOptions,$newLine);
				foreach ($arrayOptions as $aOption) {
					if(strpos($aOption,'=') === false) {
                            $s = array (
                                'value' => true,
                                'site name' => $usedOptions['cfg_website_name'] ,
                                'server'  => $usedOptions['cfg_plaintext_server']
                            );
                            $k = strtolower($aOption);
                            $options[$k][$usedOptions['cfg_website_name']] = $s;
					} else {
                            $s = array (
                                'value' => ListLast($aOption,'='),
                                'site name' => $usedOptions['cfg_website_name'],
                                'server'  => $usedOptions['cfg_plaintext_server']
                            );
                            $k = strtolower(ListFirst($aOption,'='));
                            $options[$k][$usedOptions['cfg_plaintext_server']] = $s;
					}
				}
            }
        } else {
            echo "No configuration Table in $db<br>";
        }
        mysql_free_result($result2);

    }
ksort($options);
reset($options);

echo '<p>Results</p>';

if (strlen($this->ATTRIBUTES['PrintList']) > 0 ) {

    $printList = $printList . "Site Server#Option value#Site Name\n";
    foreach ($options as $k => $o) {
        if(strtolower($this->ATTRIBUTES['PrintList']) == $k){
            foreach ($o as $kk => $oo){
                $printList = $printList . $kk.'#'.$oo['value'].'#'.$oo['site name']."\n";
            }
            echo $printList;
            break;
        }
    }
} else if(strtolower($this->ATTRIBUTES['JustList']) == 'yes' ) {
    foreach ($options as $k => $o) {
        echo "$k<br/>" ;
    }
} else
   ss_DumpVar($options);

?>