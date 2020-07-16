<html>
<body>
<script language="Javascript">
theErrors = parent.document.getElementById('errors');
theErrors.innerHTML += '<?=ss_JSStringFormat($errorMessages);?>';
parent.updateErrorCount(<?=$errorCount;?>);
parent.doNextRecipient();
</script>
</body>
</html>