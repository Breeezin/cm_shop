<?php

$this->param('Auth','');

$this->param('DoAction','');

if($this->ATTRIBUTES['Auth'] != 'asdf7aisf87f43lqjflk0834r43x56r04040843ri43rrk0439r-3x4')
    die();

// ss_DumpVar($_REQUEST);


	if (strlen($this->ATTRIBUTES['DoAction'])>0) {
		$this->param('as_type','Newsletter');

        // connect to database
    	if($_SERVER['SERVER_ADDR'] == '203.97.91.81') {
            $link = mysql_connect('localhost', 'admin', '4des5ewq')
                or die('Could not connect: ' . mysql_error());
            echo '<h1>Connected successfully to Linux</h1>';
        } else {
            $link = mysql_connect('localhost', 'root', '08ed893c')
                or die('Could not connect: ' . mysql_error());
                echo '<h1>Connected successfully to Zeus</h1>';
        }

        $result = mysql_query("show databases") or die('Query failed: ' . mysql_error());
        $databases = array();
        $sites = array();

        // parse db list
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
            $databases[$row['Database']] = $row['Database'];
        }
        mysql_free_result($result);

        // for each database
        echo '<table border="1"><tr><td>Site</td><td>Email</td><td>Asset Type</td><td>Count</td><td>Limit</td></tr>';
        foreach($databases as $db){

            $result2 = mysql_query("show tables from $db like 'assets'") or die('Query failed: ' . mysql_error());

            $R_AllTables =  mysql_fetch_array($result2, MYSQL_ASSOC);
            if($R_AllTables) {
                // print to screen
                foreach( $R_AllTables as $table) {
                    $ok = true;
                    // get assets
                    $result = mysql_query("select as_id, as_type, at_limit, count(as_type) as count
                                            from $db.assets, $db.asset_types
                                            where at_name like '".$this->ATTRIBUTES['as_type']."'
                                                  and at_name=as_type
                                                  and (as_deleted IS NULL OR as_deleted = 0)
                                            group by as_type") or
                    $ok = false;

                if ( $ok ) {
                    $usedAsset = mysql_fetch_array($result, MYSQL_ASSOC);
                    mysql_free_result($result);

                    // get site config
                    $result = mysql_query("select cfg_website_name, cfg_email_address, cfg_plaintext_server from $db.configuration") or
                    $result = mysql_query("select cfg_website_name, cfg_email_address, cfg_folder_name as cfg_plaintext_server from $db.configuration")
                    or die('Query failed: ' .mysql_error());

                    $config = mysql_fetch_array($result, MYSQL_ASSOC);
                    mysql_free_result($result);

                    // print to screen
                    echo '<tr><td>'.$config['cfg_plaintext_server'].'<br>('.$db.')</td>';
                    echo '<td>'.$config['cfg_email_address'].'</td>';
                    echo '<td>'.$usedAsset['as_type'].'</td>';
                    echo '<td>'.$usedAsset['count'].'</td>';
                    echo '<td>'.$usedAsset['at_limit'].'</td></tr>';
                    if ($this->ATTRIBUTES['as_type'] == 'Newsletter') {
                        $ok = true;
                        $result3 = mysql_query("select na_sent
                                                from $db.newsletter_archive
                                                order by na_sent desc") or
                                   $ok = false;

                         if ( $ok ) {
                            $usedNewsletter = mysql_fetch_array($result3, MYSQL_ASSOC);
                            echo "<tr><td colspan='4'>".$usedNewsletter['na_sent']."</td></tr>";
                            mysql_free_result($result3);
                        }
                    }
                }
            }
            } else {
                echo "<tr><td colspan='4'>No assets Table in $db</td></tr>";
            }
            mysql_free_result($result2);
        }
        echo '</table>';
        echo 'done';
        die();

    } else {

    	if($_SERVER['SERVER_ADDR'] == '203.97.91.81') {
            echo '<h1>Asset Search Linux</h1>';
        } else {
                echo '<h1>Asset Search Zeus</h1>';
        }
// 					<form action="{tmpl_var AssetPath}/Service/Engine" method="POST">

$assetPath = str_replace(' ','%20',$assetPath);
        echo "
            <form name=\"assetsearch\" action=$assetPath/Service/ShowGlobalAssets/ method='POST'>
                <input type=\"hidden\" name=\"Auth\" value=\"".$this->ATTRIBUTES['Auth']."\" />
                <input type=\"hidden\" name=\"DoAction\" value='yes' />
                <select name='as_type'>
                    <option value='Newsletter' checked>Newsletter
                    <option value='BookingForm' >BookingForm
                    <option value='Catalogue' >Catalogue
                    <option value='CCC' >CCC
                    <option value='CountrySpecificPage' >CountrySpecificPage
                    <option value='CustomPayment' >CustomPayment
                    <option value='DataCollection' >DataCollection
                </select>
                <input type='submit' value='go' />
            </form>
        ";

    }
?>