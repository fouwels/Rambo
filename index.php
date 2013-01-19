<?php
	header('Content-type: text/xml');
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	$from = $_POST['From'];
	$to = $_POST['To'];
	$body = $_POST['Body'];
?>
<Response>
	<Sms><?php echo $body; ?></Sms>
</Response>
