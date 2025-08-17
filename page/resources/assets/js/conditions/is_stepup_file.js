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