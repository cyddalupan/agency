
<div style="width:850px;margin-top:-20px;">
<?php 
$p_id="";
if(isset($report_pid)){
$p_id=$report_pid;
}
?>
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
			$app=select_db(' physical_exam','*');
			
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
				
				$app=select_db(' physical_exam','*');
			
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


</div>