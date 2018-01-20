<?php 
	require_once('RedditAPI.php');
	$API = new RedditAPI;
	$str = $API->HelloWorld();
 ?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<?php echo $str; ?>

</body>
</html>