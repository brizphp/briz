<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600" rel="stylesheet">
    <style>
        #br_err_body {
            margin:40px;
	        background-color: #fff;
	        font: 13px/1.7em 'Open Sans';
        }
    
        .br_err_p { 
            
	        font: 16px/1.7em 'Open Sans'; 	
        }
        #br_err_h3 {
            font-weight: bold;
            font-size: 19px;
            color: #858c91;
        }
        #br_err_content {
            border:1px solid #FF7F74;
            padding-left:20px;
        }
        .br_err_backtrace{
            margin-left:20px;
        }
    </style>
</head>
<body>
<div id="br_err_body">
<div id="br_err_content">
    <h3 id="br_err_h3">An uncaught Exception was encountered</h3>
<p class="br_err_p"> Type:        <?php echo get_class($exception); ?> </p>
<p class="br_err_p"> Message:     <?php echo $exception->getMessage(); ?> </p>
<p class="br_err_p"> Filename:    <?php echo $exception->getFile(); ?> </p>
<p class="br_err_p"> Line Number: <?php echo $exception->getLine(); ?> </p>


Backtrace:
<?php	foreach ($exception->getTrace() as $error): ?>
<?php	    if (isset($error['file'])): ?>
    <p class="br_err_backtrace">
	File: <?php echo $error['file']; ?><br/>
	Line: <?php echo $error['line']; ?><br/>
	Function: <?php echo $error['function']; ?><br/>
    </p>
<?php	    endif ?>
<?php	endforeach ?>
</div></div>
</body>
</html>
