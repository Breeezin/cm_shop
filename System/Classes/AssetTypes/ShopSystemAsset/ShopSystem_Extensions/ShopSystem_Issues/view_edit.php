<?php
	$data = array(
		'Issue'	=>	$Issue,
		'Q_Issues'	=>	$Q_Issues,
		'Q_OtherIssues'	=>	$Q_OtherIssues,
		'Q_Attachments'	=>	$Q_Attachments,
		'Q_Audit'	=>	$Q_Audit,
		'Q_Others'	=>	$Q_Others,
		'othersTotal'	=>	$othersTotal,
		'Q_Edits'	=>	$Q_Edits,
		'ci_id'		=>  $ci_id,
		'response'	=> $response,
		'Q_VisibleOrders'	=>	$Q_VisibleOrders,
		'Q_Administrators'	=>	$Q_Administrators,
		'Q_CannedQuestions'	=>	$Q_CannedQuestions,
		'Q_CannedResponses'	=>	$Q_CannedResponses,
		'BackURL'	=>	$this->ATTRIBUTES['BackURL'],
	);
	$this->display->title = 'Issue Edit';
	
	$this->useTemplate("IssueEdit",$data);
?>
