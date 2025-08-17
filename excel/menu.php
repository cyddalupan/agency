    <?php         
    
 
    echo'<ul style="width:90%;font-size:12px">';
    
            if($_GET['type']=='23'){
            echo'<li><a  href="index.php?type=23">Home</a></li>';
            echo'<li><a href="prio.php?stat=CONTINUE WORKING&&type=23">CONTINUE WORKING </a></li>';
            echo'<li><a href="prio.php?stat=TRANSFERRED&&type=23">TRANSFERRED </a></li>';
            echo'<li><a href="prio.php?stat=RESOLVED&&type=23">RESOLVED </a></li>';
            echo'<li><a href="prio.php?stat=UNDER CUSTODY&&type=23">UNDER CUSTODY </a></li>';
            echo'<li><a href="prio.php?stat=UNDER LOCAL AUTORITIES&&type=23">UNDER LOCAL AUTORITIES </a></li>';
            echo'<li><a href="prio.php?stat=NO RESPONSE">NO RESPONSE </a></li>';
             echo'<li><a href="prio.php?stat=ARRIVED&&type=23">ARRIVED </a></li>';
            echo'<li><a href="all.php?type=23">SEARCH</a></li>';
            }   
            
            
                
                if($_GET['type']=='32'){
                echo'<li><a  href="index.php?type=32">Home</a></li>';
                echo'<li><a href="prio.php?stat=CONTINUE WORKING&&type=32">CONTINUE WORKING </a></li>';
                echo'<li><a href="prio.php?stat=RESOLVED&&type=32">RESOLVED </a></li>';
                echo'<li><a href="prio.php?stat=ACTIVE&&type=32">ACTIVE COMPLAINT </a></li>';
                echo'<li><a href="prio.php?stat=CASE DETAILS&&type=32">CASE DETAILS </a></li>';
                echo'<li><a href="prio.php?stat=UNDER CUSTODY&&type=32">UNDER CUSTODY </a></li>';
                echo'<li><a href="prio.php?stat=ARRIVED&&type=32">ARRIVED </a></li>';
                echo'<li><a href="all.php?type=32">SEARCH</a></li>';
                }
            
            
            if($_GET['type']=='33'){ 
                echo'<li><a  href="index.php?type=33">Home</a></li>';
                echo' <li><a href="prio.php?stat=ACTIVE CASE&&type=33">ACTIVE CASE </a></li>';
           
            }
            
            
            
            if($_GET['type']=='34'){
            echo'<li><a  href="index.php?type=34">Home</a></li>';
            echo'<li><a href="prio.php?stat=CONTINUE WORKING&&type=34">CONTINUE WORKING </a></li>';
            echo'<li><a href="prio.php?stat=TRANSFERRED&&type=34">TRANSFERRED </a></li>';
            echo'<li><a href="prio.php?stat=RESOLVED&&type=34">RESOLVED </a></li>';
            echo'<li><a href="prio.php?stat=UNDER CUSTODY&&type=34">UNDER CUSTODY </a></li>';
            echo'<li><a href="prio.php?stat=UNDER LOCAL AUTORITIES&&type=34">UNDER LOCAL AUTORITIES </a></li>';
            echo'<li><a href="prio.php?stat=NO RESPONSE">NO RESPONSE </a></li>';
             echo'<li><a href="prio.php?stat=ARRIVED&&type=34">ARRIVED </a></li>';
            echo'<li><a href="all.php?type=34">SEARCH</a></li>';
            }  
            
            
            
               if($_GET['type']=='4'){
                echo'<li><a  href="index.php?type=4">Home</a></li>';
                echo'<li><a href="prio.php?stat=CONTINUE WORKING&&type=4">CONTINUE WORKING </a></li>';
                echo'<li><a href="prio.php?stat=RESOLVED&&type=4">RESOLVED </a></li>';
                echo'<li><a href="prio.php?stat=ACTIVE&&type=4">ACTIVE COMPLAINT </a></li>';
                echo'<li><a href="prio.php?stat=CASE DETAILS&&type=4">CASE DETAILS </a></li>';
                echo'<li><a href="prio.php?stat=UNDER CUSTODY&&type=4">UNDER CUSTODY </a></li>';
                echo'<li><a href="prio.php?stat=ARRIVED&&type=4">ARRIVED </a></li>';
                echo'<li><a href="all.php?type=4">SEARCH</a></li>';
                
                echo'<li><a  href="index.php?type=4">Home</a></li>';
                echo' <li><a href="prio.php?stat=ACTIVE CASE&&type=4">ACTIVE CASE </a></li>';
           
            }
      
            
            echo'</ul>';
    
    
    ?>