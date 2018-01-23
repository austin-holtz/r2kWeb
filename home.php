<?php 
	require_once('PostGrabber.php');
	$grabber = new PostGrabber();
	$str=$grabber->getPosts();	
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