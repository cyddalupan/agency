/**
 * Main JavaScript File
 * Author: Cyd Dalupan (cydmdalupan@gmail.com)
 */

/*
 * test If file_type is a Stepup File
 */
function is_stepup_files(file_type){
	console.log('function condition/is_stepup_files');

	if(file_type == 'Step Up Files 1')
		return true;
	if(file_type == 'Step Up Files 2')
		return true;
	if(file_type == 'Step Up Files 3')
		return true;
	else
		return false;

}

/*
 * Converts 'Date in timestamp' to Age.
 * Works With date_to_timestamp tool
 */
function calculateAge(birthday_timestamp) { // birthday is a date
    var ageDifMs = Date.now() - birthday_timestamp;
    var ageDate = new Date(ageDifMs); // miliseconds from epoch
    return Math.abs(ageDate.getUTCFullYear() - 1970);
}
/*
 * Converts a Datetime Format "1990-08-10" to a TimeStamp 
 * For Easier Compotation of date
 * Example, Getting Age
 * Can be Used in calculateAge() tool
 */
function date_to_timestamp(oldDate){
	myDate=oldDate.split("-");
	var newDate= myDate[1]+"/"+myDate[2]+"/"+myDate[0];
	return (new Date(newDate).getTime());
}
/*
 * simple remove dash on string function
 */
function remove_dash(str){
	return str.replace(/-/g, "");
}

function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}

function isValidDate(dateString) {
	if (dateString != null) {
	  var regEx = /^\d{4}-\d{2}-\d{2}$/;
	  return dateString.match(regEx) != null;
	}else{
		return false;
	}
}







