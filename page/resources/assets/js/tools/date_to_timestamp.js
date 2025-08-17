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