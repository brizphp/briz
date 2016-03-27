<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo $title; ?></title>
<style type="text/css">
body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}
	h2 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}
</style>
</head>
<body><h2 style="">
<?php
if(isset($title)){
echo $title; 
} ?></h2>
<br>
<p>
<?php echo $content; ?> <a href="<?php echo $link; ?>"><?php echo $link; ?></a></p>