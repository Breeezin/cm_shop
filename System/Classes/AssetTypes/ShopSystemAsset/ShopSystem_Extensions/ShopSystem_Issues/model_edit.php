<?php
$this->param('ci_id');
$this->param('BackURL');

$ci_id = (int)$this->ATTRIBUTES['ci_id'];
$cq_id = NULL;

if( array_key_exists( 'newQuestion', $_POST ) )
{
	if( strlen( $_POST['newQuestion'] ) )		// non hostile, admin
	{
		query( "insert into canned_question (cq_text) values('".escape( $_POST['newQuestion'] )."')" );
		$cq_id = getLastAutoIncInsert();
		query( "update client_issue set ci_cq_id = $cq_id where ci_id = $ci_id" );
	}
}

if( array_key_exists( 'CannedQuestion', $_POST ) )
{
	if( strlen( $_POST['CannedQuestion'] ) )
	{
		$cq_id = (int) $_POST['CannedQuestion'];
		query( "update client_issue set ci_cq_id = $cq_id where ci_id = $ci_id" );
	}
}
else
{
//	print_r( $_POST );
//	die;
}

if( array_key_exists( 'order_number', $_POST ) )
{
	if( strlen( $_POST['order_number'] ) )
	{
		$tr_id = (int) $_POST['order_number'];
		query( "update client_issue set ci_transaction_number = $tr_id where ci_id = $ci_id" );
	}
}
$response = '';

if( array_key_exists( 'newResponseName', $_POST ) &&  array_key_exists( 'newResponseText', $_POST ) && array_key_exists( 'newResponse_cq_id', $_POST ))
{
	if( strlen( $_POST['newResponseName'] ) &&  strlen( $_POST['newResponseText'] ) &&  strlen( $_POST['newResponse_cq_id'] ))		// non hostile, admin
	{
		$cq_id = (int)$_POST['newResponse_cq_id'];
		query( "insert into canned_responses (cr_cq_id, cr_name, cr_text) values($cq_id, '".escape( $_POST['newResponseName'] )."', '".escape( $_POST['newResponseText'] )."')" );
		$response = $_POST['newResponseText'];
	}
	else
		if( array_key_exists( 'CannedResponse', $_POST ) )
		{
			$cr_id = (int) $_POST['CannedResponse'];
			$response = getField( "select cr_text from canned_responses where cr_id = $cr_id" );
		}
}

?>
