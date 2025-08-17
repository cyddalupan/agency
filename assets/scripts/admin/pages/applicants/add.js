$(document).ready(function(){
    //get cyd-exam-date original value
    cydIssueDate = $('.cyd-issue-date').val();

    //run a script every second
    window.setInterval(function(){
    	console.log(cydIssueDate);
        //if param1 change, get value of param2, add 90 days and put to param3
        add90days2result(cydIssueDate,'.cyd-issue-date','.cyd-expired-date');
    }, 1000);
});

//add 90 days to resultClass from gclass, and test orinal if change
function add90days2result(orinal,gclass,resultClass){
    //check if original value of orinal change
    if(orinal != $(gclass).val()){
        //update new value of orinal
        orinal = $(gclass).val();
        //add 90 days to orinal
        orinal = addDays(orinal,1825);
        //change resultClass value to orinal with 90 days addition
        $(resultClass).val(orinal);
    }
}

//date day adder
function addDays(date, days) {
    var result = new Date(date);
    result.setDate(result.getDate() + days);
    return formatDate(result);
}

//convert date to Y-m-d
function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}

cyd_exp_cnt = 0;
var addWorkExperience = function(event) {
	cyd_exp_cnt++;
	console.log('prev work count = '+cyd_exp_cnt);
	event.preventDefault();
	
	var row = $('<div>').attr({
		'class': 'row work-experience'
	}).appendTo( $('.work-experience-start') );
	
	var col = $('<div>').addClass('col-sm-2');
	
	var companyGroup = $('<div>').addClass('form-group');   
    var companyLabel = $('<label>').text('Company').appendTo(companyGroup); 
    var company = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][company][]',
        'class': 'form-control',
    }).appendTo(companyGroup);
    
    var positionGroup = $('<div>').addClass('form-group');  
    var positionLabel = $('<label>').text(extraExperience1).appendTo(positionGroup);  
    var position    = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][experience_position][]',
        'class': 'form-control',
    }).appendTo(positionGroup);
    
    var salaryGroup = $('<div>').addClass('form-group');    
    var salaryLabel = $('<label>').text(extraExperience2).appendTo(salaryGroup);  
    var salary  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][salary][]',
        'class': 'form-control',
    }).appendTo(salaryGroup);

    var hospitalLevelGroup = $('<div>').addClass('form-group');    
    var salaryLabel = $('<label>').text(extraExperience4).appendTo(hospitalLevelGroup);  
    var salary  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][hospital_level][]',
        'class': 'form-control',
    }).appendTo(hospitalLevelGroup);

    var countryGroup = $('<div>').addClass('form-group');   
    var countryLabel = $('<label>').text('Country / Address').appendTo(countryGroup); 
    var country = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][country][]',
        'class': 'form-control',
    }).appendTo(countryGroup);

    /* test area */
    var extraGroup1 = $('<div>').addClass('form-group');    
    var extraLabel1 = $('<label>').text(extraExperience5).appendTo(extraGroup1);  
    var extraOnly1  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][experience_salary][]',
        'class': 'form-control',
    }).appendTo(extraGroup1);
    var extraGroup2 = $('<div>').addClass('form-group');    
    var extraLabel2 = $('<label>').text(extraExperience3).appendTo(extraGroup2);  
    var extraOnly2  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][bed_capacity][]',
        'class': 'form-control',
    }).appendTo(extraGroup2);
    var extraGroup3 = $('<div>').addClass('form-group');    
    var extraLabel3 = $('<label>').text(extraExperience6).appendTo(extraGroup3);  
    var extraOnly3  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][reasonOfLeaving][]',
        'class': 'form-control',
    }).appendTo(extraGroup3);
    var extraGroup4 = $('<div>').addClass('form-group');    
    var extraLabel4 = $('<label>').text(extraExperience7).appendTo(extraGroup4);  
    var extraOnly4  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][typeOfResidence][]',
        'class': 'form-control',
    }).appendTo(extraGroup4);
    var extraGroup5 = $('<div>').addClass('form-group');    
    var extraLabel5 = $('<label>').text(extraExperience8).appendTo(extraGroup5);  
    var extraOnly5  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][nationality][]',
        'class': 'form-control',
    }).appendTo(extraGroup5);
    var extraGroup6 = $('<div>').addClass('form-group');    
    var extraLabel6 = $('<label>').text(extraExperience9).appendTo(extraGroup6);  
    var extraOnly6  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][NoFamilyMembers][]',
        'class': 'form-control',
    }).appendTo(extraGroup6);
    var extraGroup7 = $('<div>').addClass('form-group');    
    var extraLabel7 = $('<label>').text(extraExperience10).appendTo(extraGroup7);  
    var extraOnly7  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][extraExperience10][]',
        'class': 'form-control',
    }).appendTo(extraGroup7);
    var extraGroup8 = $('<div>').addClass('form-group');    
    var extraLabel8 = $('<label>').text(extraExperience11).appendTo(extraGroup8);  
    var extraOnly8  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][extraExperience11][]',
        'class': 'form-control',
    }).appendTo(extraGroup8);
    var extraGroup9 = $('<div>').addClass('form-group');    
    var extraLabel9 = $('<label>').text(extraExperience12).appendTo(extraGroup9);  
    var extraOnly9  = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][extraExperience12][]',
        'class': 'form-control',
    }).appendTo(extraGroup9);
    
    var fromGroup = $('<div>').addClass('form-group'); 
    var fromLabel = $('<label>').text('From').appendTo(fromGroup);  
    var from    = $('<input>').attr({
        'name': 'applicant[work-experience][from][]',
        'type': 'text',
        'data-date-format': 'yyyy-mm-dd',
        'class': 'form-control input-sm date-picker work-started cyd-work-started-'+cyd_exp_cnt,
        'placeholder': 'yyyy-mm-dd',
        'value': '',
    }).appendTo(fromGroup);
    
    from.on('keyup', computeWorkExperienceYears );
    
    var toGroup = $('<div>').addClass('form-group');    
    var toLabel = $('<label>').text('To').appendTo(toGroup);    
    var to  = $('<input>').attr({
        'name': 'applicant[work-experience][to][]',
        'type': 'text',
        'data-date-format': 'yyyy-mm-dd',
        'class': 'form-control input-sm date-picker work-ended cyd-work-ended-'+cyd_exp_cnt,
        'placeholder': 'yyyy-mm-dd',
        'value': '',
    }).appendTo(toGroup);
    
    to.on('keyup', computeWorkExperienceYears );
    
    var yearGroup = $('<div>').addClass('form-group');  
    var yearLabel = $('<label>').text('Years').appendTo(yearGroup); 
    var year    = $('<input>').attr({
        'type': 'text',
        'name': 'applicant[work-experience][years][]',
        'class': 'form-control work-years',
        'placeholder': '0',
        'readonly':'readonly'
    }).appendTo(yearGroup);
    
    var yearGroupEx = $('').addClass('');  
    var yearLabelEx = $('').text('').appendTo(yearGroupEx); 
    var yearEx    = $('').attr({ }).appendTo(yearGroupEx);
    
    var remove = $('<a>').attr({
        'href': '#',
        'role': 'button',
        'class': 'btn btn-danger btn-xs'
    }).text('Remove').on('click', function(event) {
        event.preventDefault();
        row.fadeOut(function() { $(this).remove(); });
    });
	
	var remove = $('<a>').attr({
		'href': '#',
		'role': 'button',
		'class': 'btn btn-danger btn-xs'
	}).text('Remove').on('click', function(event) {
		event.preventDefault();
		row.fadeOut(function() { $(this).remove(); });
	});

	var separator = $('<div>').addClass('work-experience-separator');
	

    row.append(col.clone().attr('class', 'col-sm-4').append(companyGroup));
    if(extraExperience1 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(positionGroup));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(positionGroup));
    }

    if(extraExperience2 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(salaryGroup));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(salaryGroup));
    }

    row.append(col.clone().attr('class', 'col-sm-4').append(countryGroup));
    row.append(col.clone().attr('class', 'col-sm-2').append(fromGroup));
    row.append(col.clone().attr('class', 'col-sm-2').append(toGroup));
    row.append(col.clone().attr('class', 'clearfix').append(yearGroupEx));
    row.append(col.clone().attr('class', 'col-sm-4 cyd-work-years').append(yearGroup));
    if(extraExperience3 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(extraGroup2));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(extraGroup2));
    }

    if(extraExperience4 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(hospitalLevelGroup));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(hospitalLevelGroup));
    }

    if(extraExperience5 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(extraGroup1));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(extraGroup1));
    } 
    if(extraExperience6 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(extraGroup3));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(extraGroup3));
    }

    if(extraExperience7 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(extraGroup4));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(extraGroup4));
    }

    if(extraExperience8 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(extraGroup5));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(extraGroup5));
    }

    if(extraExperience9 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(extraGroup6));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(extraGroup6));
    }

    if(extraExperience10 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(extraGroup7));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(extraGroup7));
    }

    if(extraExperience11 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(extraGroup8));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(extraGroup8));
    }

    if(extraExperience12 != ''){
        row.append(col.clone().attr('class', 'col-sm-4').append(extraGroup9));
    }else{
        row.append(col.clone().attr('class', 'col-sm-4 hidden').append(extraGroup9));
    } 
   row.append(col.clone().attr('class', 'col-sm-1').append(remove));
	
	$('.work-experience-start').append( separator );
    $('.date-picker').datepicker();

  
}; 

var computeWorkExperienceYears = function() {
    var totalYears = 0;
    
    $('.work-experience').each(function(index, element) {
        
        var row = $(this);
        
        var years = 0;    
        var from  = row.find('.work-started');
        var to    = row.find('.work-ended');
        
        from = $.isNumeric( parseFloat( from.val() ) ) ? parseFloat( from.val() ) : 0;
        to   = $.isNumeric( parseFloat( to.val() ) ) ? parseFloat( to.val() ) : 0;
        
        years       = ( to - from ) <= 0 ? 0 : ( to - from );        
        totalYears += years;
        
        row.find('.work-years').val( years );
    });
    
    $('.work-experience-years').text( totalYears );
};

var previewImage = function() {
	var $input = $(this);
	
	var allowedTypes = [ "image/jpg", "image/jpeg", "image/png", "image/gif", "image/bmp" ];
	
	if ( typeof this.files[0] == 'undefined' || this.files[0].size == 0 || !$.inArray(this.files[0].type, allowedTypes ) ) { 
		alert('Invalid image file');
		return false;
	}
	
	var file = this.files[0];
	var reader = new FileReader();
	
	reader.onload = function(event) {
		$('.profile-photo').attr("src", event.target.result);
	}; 
	reader.onerror = function(event) {
		alert("Error: " + event.target.error.code);
	};
	reader.readAsDataURL(file);  
}

$(document).ready(function() {
	
	$('button.btn-photo-browse').on('click', function(event) {
		event.preventDefault();
		$('input[name="applicant[photo]"]').click();
	});
	
	$('input[name="applicant[photo]"]').change(previewImage);
	
    //--Bootstrap Date Picker--
    $('.date-picker').datepicker();
	
	$('select[name="applicant[position]"]').select2({
		placeholder: "Select a position",
		allowClear: true
	});
	
	$('select[name="applicant[other-preferred-countries][]"]').select2({
		placeholder: "Select country",
		allowClear: true
	});
	
	$('select[name="applicant[other-preferred-positions][]"]').select2({
		placeholder: "Select position",
		allowClear: true
	});
	
	$('#btn-add-work-experience').on('click', addWorkExperience);
	$('.row.work-experience a.btn').on('click', function(event) {
		event.preventDefault();
		$(this).closest('.row.work-experience').fadeOut(function() { $(this).remove(); });
	});
    
    $('.work-experience .work-started, .work-experience .work-ended').on('keyup', computeWorkExperienceYears );
});

//cyd Javascript
//Count User Experience
window.setInterval(function(){
	//total work years
	rgtrWrkStr = $('.cyd-work-started-1').val();
	rgtrWrEnd = $('.cyd-work-ended-'+cyd_exp_cnt).val();

	rgtrWrkStrYr  = rgtrWrkStr.substring(0,4);
	rgtrWrEndYr  = rgtrWrEnd.substring(0,4);

	rgtrWrkStrMn = rgtrWrkStr.substring(5,7);
	rgtrWrEndMn = rgtrWrEnd.substring(5,7);

	cydTotalYr = rgtrWrEndYr - rgtrWrkStrYr;

	cydRegMsg = cydTotalYr + " Year(s)";

	if(rgtrWrkStrMn < rgtrWrEndMn){
		cydRegMsg = cydRegMsg+" And "+(rgtrWrEndMn - rgtrWrkStrMn)+" Month(s)";
	}
	$('.cyd-work-experience-years').html(cydRegMsg);

	for(wrx = 1; wrx <= cyd_exp_cnt; wrx++){
		//console.log('wrx = '+ wrx + '.cyd-work-years-'+wrx);
		srgtrWrkStr = $('.cyd-work-started-'+wrx).val();
		srgtrWrEnd = $('.cyd-work-ended-'+wrx).val();
		srgtrWrkStrYr  = srgtrWrkStr.substring(0,4);
		srgtrWrEndYr  = srgtrWrEnd.substring(0,4);
		stotal =  srgtrWrEndYr - srgtrWrkStrYr;
		$('.cyd-work-years-'+wrx).val(stotal);	
	}

}, 5000);

