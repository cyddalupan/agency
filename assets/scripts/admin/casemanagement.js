$(document).ready(function(){
	//auto hitting applicants every ___ seconds
	setTimeout(function(){ getCaseManagement()}, 2000);

	//Shows How many applicants has been hit on the side bar
	setTimeout(function(){ updateHitCount()}, 1000);

	//check for upcomming hearing, and shows on the header
	setTimeout(function(){ checkHearing()}, 3000);
});

function getCaseManagement(){
	$.post( base_url + 'page/case/hit-applicants',function(caseManagementResult){
		console.log(caseManagementResult);
		setTimeout(function(){ getCaseManagement()}, 11000);
	})
	.fail(function() {
		console.log('Connect to API Failed, Retry in 10...');
	    setTimeout(function(){ getCaseManagement()}, 15000);
	});
}

function updateHitCount(){
	$.post( base_url + 'page/case/get-hit-count',function(totalHitCount){
		console.log(totalHitCount);
		$('.hit-count').text(totalHitCount);
		if(totalHitCount == 0){
			$('.hit-count').hide(1);
		}
	})
	.fail(function() {
		$('.hit-count').text('?');
	});
}

function checkHearing(){
	$.post( base_url + 'page/case/check-hearing-date',function(checkHearingDateData){
		console.log('Check for Upcomming Hearing');
		if (typeof checkHearingDateData[0]['applicant_first'] != 'undefined') {
			$('.hearing_alert .fullname').text(checkHearingDateData[0]['applicant_first']+' '+checkHearingDateData[0]['applicant_last']);
			$('.hearing_alert .hearing_date').text("Hearing Date: "+checkHearingDateData[0]['hit_hearing_date']);
			$('.hearing_alert').slideDown('slow');
		}
	},'json');
}