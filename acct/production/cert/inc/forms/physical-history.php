<style>
#medical-history{padding:0; margin:0; margin-top:-10px}
#medical-history td{padding-left:10px;font-size:11px}
select{ font-size:10px; margin-right:30px}
input{ padding:8px; font-size:14px}
</style>

<?php 
include '../inc/functions.php';
$apps = mysql_query("SELECT * FROM p_information where p_id=".$_GET['p_id']);
$name = mysql_fetch_array($apps);
$appss = mysql_query("SELECT * FROM physical_2 where p_id=".$_GET['p_id']);
$update = mysql_fetch_array($appss);
?>
<div style="width:850px;margin-top:-20px;">
<h3 style="font-weight:bold;text-transform:capitalize">
PATIENT ID : <?=$_GET['p_id']?>    |  
PATIENT NAME : <?=$name['lname']?> ,  <?=$name['fname']?>  <?=$name['mname']?> | 
DATE REGISTERD : <?=$update['dateadded']?> </h3>
<h4 style="background:#000000; color:#FFFFFF; padding:3px;margin-top:-10x;">MEDICAL HISTORY : This applicant suffered from or been tod he/she had any of the following conditions:</h4>
<form method="post" action="?p_id=<?=$_GET['p_id']?>&p=9">
<?php 
$p_id="";
if(isset($_GET['p_id'])){
$p_id=$_GET['p_id'];
}
?>

<table id="medical-history">

<?php 
$exam_name="";
$exam_result="";
$list="";
$ifexist=mysql_query("SELECT * FROM patient_result_exam where p_id='".$_GET['p_id']."' and exam_type='physicalexam'");
			$ifexistjud=mysql_num_rows($ifexist);
			if($ifexistjud>=1){
			$sql=mysql_fetch_array($ifexist);
			$mlist=explode(';',$sql['exam_name']);
			$count=1;
			//var_dump($mlist);
			$app=select_db('physical_exam','*');
			
				$count=1;
				$arrcount=0;
				$values="";
				foreach ($mlist as $lkey => $lvalue) {
					$lists = explode(':',$lvalue);
				}
		
				
				while($row=mysql_fetch_array($app)){
				//echo $mlist[$arrcount];
				
					$listss=explode(':',$mlist[$arrcount]);
					
					//var_dump($listss);
					//$listss[1][$arrcount];
				?>
				<td><?=$row['name']?> </td>
				<td>
					<select name="<?=$row['name']?>">
						<?php 
							if ($listss[1] == 'YES'){
								$yes = "selected='selected'";
								$no="";
							}else{
								$no = "selected='selected'";
								$yes="";
							}
						?>
						<option <?=$no?>>NO</option>
						<option <?=$yes?>>YES</option>
					</select>
				</td>

				<?php 
				$count++;
				if($count==4){
				$count=1;
				echo '</tr><tr>';
				}
$arrcount++;
				}
			}else{
				
				$app=select_db('physical_exam','*');
			
				$count=1;
				while($row=mysql_fetch_array($app)){?>
				<td><?=$row['name']?></td>
				<td>
					<select name="<?=$row['name']?>">
						<option>NO</option>
						<option>YES</option>
					</select>
				</td>

				<?php 
				$count++;
				if($count==4){
				$count=1;
				echo '</tr><tr>';
				}

				}
}
?>
</table>
<hr />
<input type="hidden" name="p_id" value="<?=$p_id?>">
<input type="submit" name="save-physical-exam" value="SAVE" style="float:right" />
</form>
</div>
<div style="height:20px; clear:both"></div>
