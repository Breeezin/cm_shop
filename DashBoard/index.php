<?php
	$Title = "Dashboard Index";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";
?>
<html>
<a href='summary.php'>New Summary Dashboard</a><br><br>
<a href='summary.php?country=840'>New Summary Dashboard (USA only)</a><br><br>
<a href='summary.php?notcountry=840'>New Summary Dashboard (NOT USA only)</a><br><br>
<a href='reships.php'>Reshipments Details</a><br><br>
<a href='reships_summary.php'>Reshipments Summary</a><br><br>
<a href='refunds.php'>Refunds Details</a><br><br>
<a href='refunds_summary.php'>Refunds Summary</a><br><br>
<a href='who_has_credit.php'>Customers with credit</a><br><br>
<a href='notes.php'>Notes</a><br><br>
<a href='payments.php'>payments</a><br><br>
<a href='entry.php'>Enter the days business statistics</a><br><br>
<a href='keyword_edit.php'>Manage search engine keywords</a><br><br>
<a href='specify_stock_report.php'>Define and/or run a stock report</a><br><br>
<a href='specify_se_report.php'>Define and/or run a search engine report</a><br><br>
<a href='specify_pr_report.php'>Define and/or run a profitability report</a><br><br>
<a href='webdruid'>View Access statistics</a><br><br>
<a href='/index.php?act=ShopSystem.AcmeAutoDashboard&AccessCode=&HashMeIn=1_4653e832aedb067d5ad797461ca279cb'>Old Automatic Dashboard</a><br><br>
<a href='custom_reports.php'>View Custom Reports</a><br><br>
<a href='send_newsletter.php'>Send Main Newsletter Now</a><br><br>
<a href='lookup_admin/'>Lookup Tables Administration</a>
</html>
