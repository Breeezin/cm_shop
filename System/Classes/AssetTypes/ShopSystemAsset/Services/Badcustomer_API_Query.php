<?php
/*
 * An example request call to the API.
 */
require_once 'HttpClient2real.php';

/*$ip=$_SERVER['REMOTE_ADDR'];	*/

/*
 * An object of the class HttpClient2real
 */

$service = new HttpClient2real("5da9ebab-e798-b28d-5873-4b1481c0c935");


/* Example 1
 * An example query where card_number_hash is passed along with card_holder_name
 * @param api_key
 * @param card_number_hash/card_number
 * @param card_holder_name
 * @param json/xml
 */
/*
$response = $service->call("index",array(
			"api_key" => "5da9ebab-e798-b28d-5873-4b1481c0c935", 
			"card_number_hash" => md5("5424180642701194"),
			"card_holder_name"=>"Josh Harmon"
			),json);
*/
$response = $service->call("index",array(
			"api_key" => "5da9ebab-e798-b28d-5873-4b1481c0c935", 
			"card_holder_name"=>"Josh Harmon",
			"address"=>"28052 Nelsonia Road, Bloxom, VA","zip"=>"23308"
			),json);

$result = json_decode($response);

print_r( $result );
if( $result->found )
{

}
else
{

}


die;
//json    {"transactionId":"bf60ee96-bbff-e866-bf6f-4b15860011c7","error":"4","found":"0","result":"No Results"}
/* xml
<?xml version="1.0" encoding="UTF-8"?>
<API version="1.0">
    <transactionId>da741e11-940c-6bb3-ae7a-4b1587796a99</transactionId>
    <error>0</error>
    <found>0</found>
    <result>No Results</result>
*/

/* Example 2
 * An example query where card_number_hash is passed along with card_holder_name, address associated with the card and zip code.
 * @param api_key
 * @param card_number_hash/card_number
 * @param card_holder_name
 * @param address
 * @param zip
 * @param json/xml
 */
$response = $service->call("index",array(
			"api_key" => "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",  
			"card_number_hash" => "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
			"card_holder_name"=>"XXXXXX XXXXXX",
			"address"=>"480 X. 300 X.","zip"=>"XXXXX"
			),json);


/* Example 3
 * An example query where card_holder_name is passed along with address and zip.
 * @param api_key
 * @param card_holder_name
 * @param address
 * @param zip
 * @param json/xml
 */
$response = $service->call("index",array(
			"api_key" => "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
			"card_holder_name"=>"XXXXXXX XXXXXX",
			"address"=>"480 X 300 X","zip"=>"XXXXX"
			),json);


/* Example 4
 * An example query where card_number_hash is passed along with addresss and zip
 * @param api_key
 * @param card_number_hash/card_number
 * @param address
 * @param zip
 * @param json/xml
 */
$response = $service->call("index",array(
			"api_key" => "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
			"card_number_hash" => "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
			"address"=>"480 X. 300 X.","zip"=>"XXXXX"
			),json);



/* Example 5
 * An example query where card_number_hash is passed
 * @param api_key
 * @param card_number_hash/card_number
 * @param json/xml
 */
$response = $service->call("index",array(
			"api_key" => "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
			"card_number_hash" => "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
			),json);


/* Example 6
 * An example query where an email address is passed
 * @param api_key
 * @param email_address
 * @param json/xml
 */
$response = $service->call("index",array(
			"api_key" => "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX", 
			"email_address" => "xxxxxxxxx@xxxxx.xxx"
			),json);

/*
 * This will print the response form the website which can be json
 * or xml depending upon the argument passed in the call. 
 */
print_r($response);


?>
