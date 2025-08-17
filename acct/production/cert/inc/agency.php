
<style>
	#formclass input{ width:300px;padding:3px;}
	#formclass select{ width:300px;padding:3px;}
</style>
<div style="height:220px;" id="formclass">
<form action="settings.php" method="POST">
<h3>Add New User - <?=date('m/d/y')?></h3>
<hr>
<table>
<tr>
<td>Name</td>
<td><input type="text" name="name" /></td>
</TR>
	
<td colspan="1"></td>
<td><input type="submit" id="button" name="agencys" value="ADD USER"/></td>
</tr>
</table>
</form>
</div>