$(document).ready(function(){
	//loop endless every
	window.setInterval(function(){
		applicant_status = $('.calc-status-pay').val();
		if(applicant_status == 'Married')
			taxPercent = taxmarried;
		else if(applicant_status == 'Single w/ 1 Dependent')
			taxPercent = taxSinglew1Dependent;
		else if(applicant_status == 'Single w/ 2 Dependents')
			taxPercent = taxSinglew2Dependents;
		else if(applicant_status == 'Single w/ 3 Dependents')
			taxPercent = taxSinglew3Dependents;
		else if(applicant_status == 'Single w/ 4 Dependents')
			taxPercent = taxSinglew4Dependents;
		else if(applicant_status == 'Married w/ 1 Dependents')
			taxPercent = taxMarriedw1Dependents;
		else if(applicant_status == 'Married w/ 2 Dependents')
			taxPercent = taxMarriedw2Dependents;
		else if(applicant_status == 'Married w/ 3 Dependents')
			taxPercent = taxMarriedw3Dependents;
		else if(applicant_status == 'Married w/ 4 Dependents')
			taxPercent = taxMarriedw4Dependents;
		else
			taxPercent = taxsingle;

		//insert new data
		total_deductions = 
			Number($('.calc-sss').val()) +
			Number($('.calc-phil-health').val()) +
			Number($('.calc-pagibig').val())

		total_allowances =
			Number($('.calc-meal').val()) +
			Number($('.calc-transpo').val()) +
			Number($('.calc-cola').val()) +
			Number($('.calc-other').val()) +
			Number($('.calc-tax').val());

		total_misc =
			Number($('.calc-overtime').val()) +
			Number($('.calc-night-diff').val()) +
			Number($('.calc-holiday-pay').val()) -
			Number($('.calc-hmo').val()) -
			Number($('.calc-absnet').val()) -
			Number($('.calc-tardiness').val()) -
			Number($('.calc-undertime').val());

		taxable_income = 
			Number($('.calc-basic-salary').val()) -
			total_deductions;

		withholding_tax = Number($('.calc-basic-salary').val()) * (taxPercent / 100);

		net_income = taxable_income + total_misc + total_allowances - withholding_tax;


	   $('.res-total-deductions').text(total_deductions);
	   $('.res-total-allowances').text(total_allowances);
	   $('.res-total-misc').text(total_misc);
	   $('.res-taxable-income').text(taxable_income);
	   $('.res-withholding-tax').text(withholding_tax);
	   $('.res-net-income').text(net_income);
	}, 2000);
});