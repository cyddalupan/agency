/* Script */

jQuery(function() {
	$(document).ready(function() {
		
         $('input.fee-amount').on('keup', function() {
                var amount = 0;
                $('input.fee-amount').each(function(index, element) {
                    amount += $.isNumeric( parseFloat( $(this).val() ) ) 
                              ? parseFloat( $(this).val() ) 
                              : 0;
                });
                
                $('.total-revenue').text( amount.toFixed(2) );
            });
            
	}); //endOf: $(document).ready
	
}); //endOf: jQuery