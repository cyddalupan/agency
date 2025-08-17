
<style>
	#formclass input{ width:300px;padding:3px;}
	#formclass select{ width:300px;padding:3px;}
</style>
<div style="height:200px;" id="formclass">
<form action="payments-cat.php" method="POST">
<h3>Add New User - <?=date('m/d/y')?></h3>
<hr>
<table>
<tr>
<td>Price</td>
<td><input type="text" name="price" /></td>
</TR>
<tr>
<td>Packages</td>
<td><input type="text" name="packages" /></td>
</TR>	
<td colspan="1"></td>
<td><input type="submit" id="button" name="package" value="ADD PACKAGES"/></td>
</tr>
</table>
</form>
</div>