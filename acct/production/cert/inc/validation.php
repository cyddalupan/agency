<script type="text/javascript">
function validateForm()
{

var b=document.forms["addfood"]["lname"].value;
if (b==null || b=="")
  {
  alert("Pls. Enter LastName");
  return false;
  }

var d=document.forms["addfood"]["fname"].value;
if (d==null || d=="")
  {
 alert("Pls. Enter FirstName");
  return false;
  }
 var e=document.forms["addfood"]["address"].value;
if (e==null || e=="")
  {
 alert("Pls. Enter Address");
  return false;
  }
   var e=document.forms["addfood"]["marital"].value;
if (e==null || e=="")
  {
 alert("Pls. Enter Marital Status");
  return false;
  }
   var e=document.forms["addfood"]["country"].value;
if (e==null || e=="")
  {
 alert("Pls. Enter Country Destination");
  return false;
  }
     var e=document.forms["addfood"]["jobdesc"].value;
if (e==null || e=="")
  {
 alert("Pls. Enter Job Description");
  return false;
  }
/*if (c.which!=8 && c.which!=0 && (c.which<48 || c.which>57))
  {
  alert("The input U enter in Quantity field is not valid, only numbers are accepted (ex. 1, 2, 3, 4.......)");
  return false;
  }
if (b.which!=8 && b.which!=0 && (b.which<48 || b.which>57))
  {
  alert("The input U enter in Quantity field is not valid, only numbers are accepted (ex. 1, 2, 3, 4.......)");
  return false;
  }*/
}
</script>