<?php
echo "As soon as possible, log onto your neteller account and transfer {$chargeCurrency['CurrencyCode']} ".number_format($totalPrice,2)." into our merchant account ID MYHO6442.<br/>";
echo "Use {$this->ATTRIBUTES['tr_id']} as any reference number for this transfer.<br/>";
echo "Once this is done, please contact us via your members page to let us know to reserve stock for your order<br/>";
echo "<a href='".rawurldecode($backURL)."'>Continue</a>";
?>
