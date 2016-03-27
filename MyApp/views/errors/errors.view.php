
<table style="margin:40px;border:1px solid #FF7F74;border-collapse:separate;">
   <tr>
       <th align='left'> <h3 style="font-weight: bold;font-size: 19px;color: #858c91;">An Error was encountered</h3></th>
   </tr>
 <tr><th align='left'><?php echo $severity; ?> : <?php echo $message; ?></th></tr>
<tr><th align='left'>Filename:    <?php echo $filepath; ?><br/></th></tr>
<tr><th align='left'>Line Number: <?php echo $line; ?><br/></th></tr>
<tr><th align='left' ><br/>Backtrace:<br/></th></tr>
    <tr style="border-spacing:5px;outline:1px solid #B12626;">
        <th align='left'>File</th><th align='left' style="width:15px;">Line</th><th align='left'>Function</th>
    </tr>
<?php	foreach (debug_backtrace() as $error): ?>
<?php		if (isset($error['file'])): ?>
	<tr style="border-spacing:5px;outline:1px solid #B12626;">
    <td><?php echo $error['file']; ?></td>
	<td style="width:50px;"><?php echo $error['line']; ?></td>
	<td style="min-width:250px;"><?php echo $error['function']; ?></td>
    </tr>
<?php		endif ?>
<?php	endforeach ?>
</table>
</body>
</html>