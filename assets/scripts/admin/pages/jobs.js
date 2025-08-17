/* Script */
jQuery(function() {
	$(document).ready(function() {
        
        //Highlight on click
        $('.table-jobs tbody tr').on('click', function() {
            var highlightClass = 'warning';
            
            if ( $(this).hasClass( highlightClass ) ) {
                $(this).removeClass( highlightClass );
                return;
            }
            
            $('.table-jobs tbody tr').removeClass( highlightClass  );
            $(this).addClass( highlightClass  );
        });
        
        $('.table-jobs tbody tr').on('dblclick', function() {
            $(this).find('a.btn-review').click();
        });

        $('#filter-employer').on('change', function() {
            var eId = $(this).val();

            if ( eId > 0 ) {
                window.location.href = siteUrl + 'admin/jobs/all?filter[eid]=' + eId;
                return;
            }
            
            window.location.href = siteUrl + 'admin/jobs/all';
        });
        
	}); //endOf: $(document).ready
	
}); //endOf: jQuery