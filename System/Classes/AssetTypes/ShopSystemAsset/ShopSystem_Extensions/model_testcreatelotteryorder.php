<?
	$FreeBox = getRow("
		SELECT * FROM lottery_winners
		WHERE lotw_id = 8
	");
	
	$winningOrderID = $FreeBox['lotw_or_id'];

	require('inc_createLotteryOrder.php');
	
	
?>