
<style>
	#formclass input{ width:300px;padding:3px;}
	#formclass select{ width:300px;padding:3px;}
</style>
<div style="height:520px;" id="formclass">
<form action="users.php" method="POST">
<h3>Add New User m/d/y- <?=date('m/d/y')?></h3>
<hr>
<table>
<tr>
<td>Name</td>
<td><input type="text" name="name" /></td>
</TR>

<tr>
<td>Address</td>
<td><input type="text" name="address" /></td>
</TR>
<tr>
<td>usertype</td>
<td>
<select name="usertype">
<option value="">----</option>
<option value="staff">MEDICAL HISTORY AND PHYSICAL</option>
<option value="lab">LAB</option>
<option value="psycho">PSYCHOLOGY</option>
<option value="admin">ADMIN</option>
<option value="cashier">CASHIER</option>
<option value="agency">AGENCY</option>
</select>
</td>
</TR>
<tr>
<td>Username</td>
<td><input type="text" name="username" /></td>
</TR>
<tr>
<td>Password</td>
<td><input type="text" name="password" /></td>
</TR>
<td colspan="1"></td>
<td><input type="submit" id="button" name="user" value="ADD USER"/></td>
</tr>
</table>
</form>
</div>