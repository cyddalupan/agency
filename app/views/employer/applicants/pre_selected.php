		<script>
function exportF(elem) {
  var table = document.getElementById("example-checkbox");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>	


  <style>
table {
  border-spacing: 0;
  width: 120%;
  border: 1px solid #ddd;
}

th {
  cursor: pointer;
}

th, td {
  text-align: left;
  padding: 16px;
}

tr:nth-child(even) {
  background-color: #f2f2f2
}
</style>
 <div class="col-md-12 col-sm-12">
    <div class="row">
     <div class="col-md-12">
        
        <div class="clients-page">
       
           <div class="row">
              <div class="col-md-12">
               
                 <div class="hero">
                    <!-- Title. Don't forget the <span> tag -->
                    <h3><span> Cancelled Applicants</span></h3>
                    <!-- para -->
                 </div> 
                 
                 <div class="row">
                    <div class="col-md-16"  style="padding-bottom:20px;">
                    
                        <div class="table-responsive">
                       

					   
<a id="downloadLink" onclick="exportF(this)" style="color:Red">Export to excel</a>
					   


<?php
if(isset($_POST['Search']))
{
		if($_POST['mydata']==1)
		{
			echo"<h3>Cancelled</h3>";
		}
		if($_POST['mydata']==22)
		{
			echo"<h3>Repat</h3>";
		}
		else
		{
		echo"<h3>Cancelled & Repat</h3>";	
		}
}		
?>					   
					   
  <table class="table table-hover table-striped table-bordered">
<thead>
<tr>

<th style="font-size:10px;width:20%">Name</th>
<th style="font-size:10px;width:20%">FRA</th>
<th style="font-size:10px;width:20%">REMARKS</th>
<th style="font-size:10px">STATUS</th>	
<th style="font-size:10px">STATUS DATE</th>						
</tr>
</thead>
<tbody>

<?php


$dbhost = 'localhost';
$dbuser = 'abbaphil_jinhel';
$dbpass = '67PtE01S4KL4hKIqUQyoP2aKhDwud7BV';
$dbname = 'abbaphil_maanyag';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname);

?>   		
<?php

	$app = "SELECT *  FROM applicants_logs_view 

		LEFT JOIN  statuses
		ON applicants_logs_view.log_status = statuses.number
		where
		employer_id=".$_SESSION['employer']['user']['employer_id']."
		AND log_status in (1,25,14)
		group by log_applicant order by log_date desc ";
		$row1 = $conn->query($app);
		$flightrowdate="";




while($row= $row1->fetch_assoc() ){



echo"<td>
".$row['applicant_first']."  ".$row['applicant_last']."</td>";
echo"<td>".$_SESSION['employer']['user']['employer_name']."</td>";
echo"<td></td>";
echo"<td style='color:Red'>".$row['statusText']."</td>";
echo"<td>".$row['log_date']."</td>";
echo"</tr>";
}				

?>	

</tbody>
</table>		   
					   
					   
					   
					   
					   
					   
					   
    						<div style="clear:both;height:40px"></div>
                        </div>
                        
                    </div>
                 </div>
                 
              </div>
           </div>
        </div>
         
        
     </div>
    </div>
  </div>
</div>