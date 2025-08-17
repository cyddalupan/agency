/* Script */

jQuery(function() {
	$(document).ready(function() {

        $('.btn-applicant-review').on('click', function( event ) {
            var $this = $(this);
            var href = $(this).attr('data-url');

            $.ajax({
                async:false,
                url: href,
                type:'GET',
                cache:false,
                dataType: 'html',
                success:function( response ) {
                     $('#modalApplicantReview').find('.modal-body').html( response );
                }
            });
        });
        
        //Highlight on click
        $('.table-applicants tbody tr').on('click', function() {
            var highlightClass = 'warning';
            
            if ( $(this).hasClass( highlightClass ) ) {
                $(this).removeClass( highlightClass );
                return;
            }
            
            $('.table-applicants tbody tr').removeClass( highlightClass  );
            $(this).addClass( highlightClass  );
        });
        
        $('.table-applicants tbody tr:not(.record-except)').on('dblclick', function() {
            $(this).find('button.btn-applicant-review').click();
        });

        $('.btn-advanced-search').on('click', function( event ) {
            var searchForm = $('.advanced-search');

            if ( searchForm.hasClass('hide') ) {
                searchForm.hide().removeClass('hide').slideDown();
                return;
            }

            searchForm.slideToggle();
        });

        $('a.btn-show-photo').on('click', function( event ) {
            event.preventDefault();

            if( $('.applicant-photo:visible').length > 0 ) {
                $('.applicant-photo:visible').addClass('hide');
                return;
            }

            $('.applicant-photo img').each(function() {                
                $(this).attr( 'src',  $(this).attr('data-source'));                
            });

            $('.applicant-photo').removeClass('hide');

        });

        initApplicantSendFunc( $('#btn-send-applicant'), 'applicants-selection', $('#employers-selection') );
        
	}); //endOf: $(document).ready
	
}); //endOf: jQuery