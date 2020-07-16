<?php
// sermepa redirection page...


if( getDefaultCurrencyCode() != 'EUR' )
{
        $_SESSION = NULL;
        die;
}



$ACK_URL = "https://www.acmerockets.com/$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "https://www.acmerockets.com/Members";
$POST_URL = "https://sis.sermepa.es/sis/realizarPago";		// live
$merchant_code = '063924617';			// live

$sdetails = unserialize($Q_Order['or_shipping_details']);
$first_name = escape(rtrim(ltrim($sdetails['PurchaserDetails']['first_name'])));
$last_name = escape(rtrim(ltrim($sdetails['PurchaserDetails']['last_name'])));
$billingAddress = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])));
$City = $sdetails['PurchaserDetails']['0_50A2'];
$b_state_country = ' '.$sdetails['PurchaserDetails']['0_50A4'];
$pos = strpos( $b_state_country, "<BR>" );
if( $pos )
{
	$b_state = substr( $b_state_country, 0, $pos );
	$b_country = substr( $b_state_country, $pos + 4 );
}
else
{
	$b_state = $b_state_country;
	$b_country = $b_state_country;
}
$cn_two_code = getField( "select cn_two_code from countries where cn_name = '$b_country'");

$Postal = $sdetails['PurchaserDetails']['0_B4C0'];
$Phone = $sdetails['PurchaserDetails']['0_B4C1'];
$email_address = $sdetails['PurchaserDetails']['Email'];
$pos = strpos( $email_address, ">" );
if( $pos )
	$email_address = substr( $email_address, $pos + 1 );
$pos = strrpos( $email_address, "<" );
if( $pos )
	$email_address = substr( $email_address, 0, $pos );

$SECRETCODE = "KnC+9IOhgJ+NLbBB2DrMvZmhq8/+OvVu";

$bullshit = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabscdefghijklmnopqrstuvwxyz';
$bl = strlen( $bullshit );
$crap = '';
for( $i = 0; $i < 4; $i++ )
	$crap .= $bullshit[rand(0,$bl-1)];




// redsys API
/**
* NOTA SOBRE LA LICENCIA DE USO DEL SOFTWARE
* 
* El uso de este software está sujeto a las Condiciones de uso de software que
* se incluyen en el paquete en el documento "Aviso Legal.pdf". También puede
* obtener una copia en la siguiente url:
* http://www.redsys.es/wps/portal/redsys/publica/areadeserviciosweb/descargaDeDocumentacionYEjecutables
* 
* Redsys es titular de todos los derechos de propiedad intelectual e industrial
* del software.
* 
* Quedan expresamente prohibidas la reproducción, la distribución y la
* comunicación pública, incluida su modalidad de puesta a disposición con fines
* distintos a los descritos en las Condiciones de uso.
* 
* Redsys se reserva la posibilidad de ejercer las acciones legales que le
* correspondan para hacer valer sus derechos frente a cualquier infracción de
* los derechos de propiedad intelectual y/o industrial.
* 
* Redsys Servicios de Procesamiento, S.L., CIF B85955367
*/

class RedsysAPI{

	/******  Array de DatosEntrada ******/
    var $vars_pay = array();
	
	/******  Set parameter ******/
	function setParameter($key,$value){
		$this->vars_pay[$key]=$value;
	}

	/******  Get parameter ******/
	function getParameter($key){
		return $this->vars_pay[$key];
	}
	
	
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	////////////					FUNCIONES AUXILIARES:							  ////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	

	/******  3DES Function  ******/
	function encrypt_3DES($message, $key){
		// Se establece un IV por defecto
		$bytes = array(0,0,0,0,0,0,0,0); //byte [] IV = {0, 0, 0, 0, 0, 0, 0, 0}
		$iv = implode(array_map("chr", $bytes)); //PHP 4 >= 4.0.2

		// Se cifra
		$ciphertext = mcrypt_encrypt(MCRYPT_3DES, $key, $message, MCRYPT_MODE_CBC, $iv); //PHP 4 >= 4.0.2
		return $ciphertext;
	}

	/******  Base64 Functions  ******/
	function base64_url_encode($input){
		return strtr(base64_encode($input), '+/', '-_');
	}
	function encodeBase64($data){
		$data = base64_encode($data);
		return $data;
	}
	function base64_url_decode($input){
		return base64_decode(strtr($input, '-_', '+/'));
	}
	function decodeBase64($data){
		$data = base64_decode($data);
		return $data;
	}

	/******  MAC Function ******/
	function mac256($ent,$key){
		$res = hash_hmac('sha256', $ent, $key, true);//(PHP 5 >= 5.1.2)
		return $res;
	}

	
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	////////////	   FUNCIONES PARA LA GENERACIÓN DEL FORMULARIO DE PAGO:			  ////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	
	/******  Obtener Número de pedido ******/
	function getOrder(){
		$numPedido = "";
		if(empty($this->vars_pay['DS_MERCHANT_ORDER'])){
			$numPedido = $this->vars_pay['Ds_Merchant_Order'];
		} else {
			$numPedido = $this->vars_pay['DS_MERCHANT_ORDER'];
		}
		return $numPedido;
	}
	/******  Convertir Array en Objeto JSON ******/
	function arrayToJson(){
		$json = json_encode($this->vars_pay); //(PHP 5 >= 5.2.0)
		return $json;
	}
	function createMerchantParameters(){
		// Se transforma el array de datos en un objeto Json
		$json = $this->arrayToJson();
		// Se codifican los datos Base64
		return $this->encodeBase64($json);
	}
	function createMerchantSignature($key){
		// Se decodifica la clave Base64
		$key = $this->decodeBase64($key);
		// Se genera el parámetro Ds_MerchantParameters
		$ent = $this->createMerchantParameters();
		// Se diversifica la clave con el Número de Pedido
		$key = $this->encrypt_3DES($this->getOrder(), $key);
		// MAC256 del parámetro Ds_MerchantParameters
		$res = $this->mac256($ent, $key);
		// Se codifican los datos Base64
		return $this->encodeBase64($res);
	}
	


	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////// FUNCIONES PARA LA RECEPCIÓN DE DATOS DE PAGO (Notif, URLOK y URLKO): ////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////

	/******  Obtener Número de pedido ******/
	function getOrderNotif(){
		$numPedido = "";
		if(empty($this->vars_pay['Ds_Order'])){
			$numPedido = $this->vars_pay['DS_ORDER'];
		} else {
			$numPedido = $this->vars_pay['Ds_Order'];
		}
		return $numPedido;
	}
	function getOrderNotifSOAP($datos){
		$posPedidoIni = strrpos($datos, "<Ds_Order>");
		$tamPedidoIni = strlen("<Ds_Order>");
		$posPedidoFin = strrpos($datos, "</Ds_Order>");
		return substr($datos,$posPedidoIni + $tamPedidoIni,$posPedidoFin - ($posPedidoIni + $tamPedidoIni));
	}
	function getRequestNotifSOAP($datos){
		$posReqIni = strrpos($datos, "<Request");
		$posReqFin = strrpos($datos, "</Request>");
		$tamReqFin = strlen("</Request>");
		return substr($datos,$posReqIni,($posReqFin + $tamReqFin) - $posReqIni);
	}
	function getResponseNotifSOAP($datos){
		$posReqIni = strrpos($datos, "<Response");
		$posReqFin = strrpos($datos, "</Response>");
		$tamReqFin = strlen("</Response>");
		return substr($datos,$posReqIni,($posReqFin + $tamReqFin) - $posReqIni);
	}
	/******  Convertir String en Array ******/
	function stringToArray($datosDecod){
		$this->vars_pay = json_decode($datosDecod, true); //(PHP 5 >= 5.2.0)
	}
	function decodeMerchantParameters($datos){
		// Se decodifican los datos Base64
		$decodec = $this->base64_url_decode($datos);
		return $decodec;	
	}
	function createMerchantSignatureNotif($key, $datos){
		// Se decodifica la clave Base64
		$key = $this->decodeBase64($key);
		// Se decodifican los datos Base64
		$decodec = $this->base64_url_decode($datos);
		// Los datos decodificados se pasan al array de datos
		$this->stringToArray($decodec);
		// Se diversifica la clave con el Número de Pedido
		$key = $this->encrypt_3DES($this->getOrderNotif(), $key);
		// MAC256 del parámetro Ds_Parameters que envía Redsys
		$res = $this->mac256($datos, $key);
		// Se codifican los datos Base64
		return $this->base64_url_encode($res);	
	}
	/******  Notificaciones SOAP ENTRADA ******/
	function createMerchantSignatureNotifSOAPRequest($key, $datos){
		// Se decodifica la clave Base64
		$key = $this->decodeBase64($key);
		// Se obtienen los datos del Request
		$datos = $this->getRequestNotifSOAP($datos);
		// Se diversifica la clave con el Número de Pedido
		$key = $this->encrypt_3DES($this->getOrderNotifSOAP($datos), $key);
		// MAC256 del parámetro Ds_Parameters que envía Redsys
		$res = $this->mac256($datos, $key);
		// Se codifican los datos Base64
		return $this->encodeBase64($res);	
	}
	/******  Notificaciones SOAP SALIDA ******/
	function createMerchantSignatureNotifSOAPResponse($key, $datos, $numPedido){
		// Se decodifica la clave Base64
		$key = $this->decodeBase64($key);
		// Se obtienen los datos del Request
		$datos = $this->getResponseNotifSOAP($datos);
		// Se diversifica la clave con el Número de Pedido
		$key = $this->encrypt_3DES($numPedido, $key);
		// MAC256 del parámetro Ds_Parameters que envía Redsys
		$res = $this->mac256($datos, $key);
		// Se codifican los datos Base64
		return $this->encodeBase64($res);	
	}
}

//  end api 

	$Ds_Merchant_Order = sprintf( '%08s', $this->ATTRIBUTES['tr_id']).$crap;

	// Se crea Objeto
	$miObj = new RedsysAPI;

	$miObj->setParameter('DS_MERCHANT_AMOUNT',(int)($totalPrice*100));
	$miObj->setParameter('DS_MERCHANT_CURRENCY', '978');		// euros 
	$miObj->setParameter('DS_MERCHANT_ORDER', $Ds_Merchant_Order);
	$miObj->setParameter('DS_MERCHANT_PRODUCTDESCRIPTION', 'AcmeRockets Llamas');
	$miObj->setParameter('DS_MERCHANT_CARDHOLDER', "$first_name $last_name");
	$miObj->setParameter('DS_MERCHANT_MERCHANTCODE', $merchant_code);
	$miObj->setParameter('DS_MERCHANT_MERCHANTURL', 'http://www.acmerockets.com/sermepa2/confirm.php');
	$miObj->setParameter('DS_MERCHANT_URLOK', $ACK_URL);
	$miObj->setParameter('DS_MERCHANT_URLKO', $NACK_URL);
	$miObj->setParameter('DS_MERCHANT_MERCHANTNAME', 'acmerockets');
	$miObj->setParameter('DS_MERCHANT_CONSUMERLANGUAGE', '002');
	$miObj->setParameter('DS_MERCHANT_TERMINAL', '001');
	$miObj->setParameter('DS_MERCHANT_TRANSACTIONTYPE', 0);

	//Datos de configuración
	$version="HMAC_SHA256_V1";
	// Se generan los parámetros de la petición
	$params = $miObj->createMerchantParameters();
	$signature = $miObj->createMerchantSignature($SECRETCODE);



	query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Merchant Order reference now $Ds_Merchant_Order', NOW(), {$Q_Order['or_id']} )" );

/*
 

*/
?>
<HTML>
<HEAD>
<TITLE>P&aacute;gina de pago</TITLE>
</HEAD>
<SCRIPT language="javascript">

window.onload = function ()
{
document.forms.TheForm.submit();
}
</SCRIPT>
<BODY>
You are being redirected to our payment processor (Sermepa).<br />
<br />
<br />
<FORM NAME="TheForm" ACTION="<?=$POST_URL?>" METHOD="POST">
<input type="hidden" name="Ds_SignatureVersion" value="<?php echo $version; ?>"/><br/>
<input type="hidden" name="Ds_MerchantParameters" value="<?php echo $params; ?>"/><br/>
<input type="hidden" name="Ds_Signature" value="<?php echo $signature; ?>"/><br/>
<CENTER>
<INPUT TYPE="submit" VALUE="Continue">
</CENTER>
</FORM>
</BODY>
</HTML>
