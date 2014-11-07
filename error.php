<?php

include_once('functions.php');

?>

<html>
	<head>
	</head>
	<body>
		<div>
			<h1>I'm sorry but there was an error with your request.</h1>
			<p><?php echo getQueryData('message'); ?></p>
			<p><?php echo getPostData('error'); ?></p>
		</div>
	</body>
</html>