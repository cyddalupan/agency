/* Script */

jQuery(function() {
	$(document).ready(function() { 
	
		$('.alert-modal').each(function() {
			$(this).modal();
		})

		//Alert Messages Fade Out
	   	$('div.alert.fadeOut').click(function() {
			$(this).fadeOut('fast');
		}); 
		
		//Live time ago
		if( $('.timeago').length > 0 ) {
			$('.timeago').livequery(function() { $(this).timeago();	});
		}
			
		var countDownTimer = setTimeout(function() {
			countDown();
		}, 1000);
		
		function countDown() {
			var timer = $('.alert .alert-label .timer');
			
			var time = parseInt(timer.html());
			time--;
			timer.html(time);
			
			if(time > 0) {
				setTimeout(function() {countDown()}, 1000);
				return;
			} 
			
			$('div.alert.fadeOut').fadeOut('slow');
		}
	}); //endOf: $(document).ready
	
}); //endOf: jQuery


/* Support list */

$("#slist a").click(function(e){
   e.preventDefault();
   $(this).next('p').toggle(200);
});

/* Portfolio */

// filter items when filter link is clicked
$('#filters a').click(function(){
  var selector = $(this).attr('data-filter');
  $container.isotope({ filter: selector });
  return false;
});


jQuery(".prettyphoto").prettyPhoto({
overlay_gallery: false, social_tools: false
});

/* Carousel */

$('.carousel').carousel();

/* *************************************** */ 
/* Scroll to Top */
/* *************************************** */  
		
$(document).ready(function() {
	$(".totop").hide();
	
	$(window).scroll(function(){
		if ($(this).scrollTop() > 300) {
			$('.totop').fadeIn();
		} else {
			$('.totop').fadeOut();
		}
	});
	$(".totop a").click(function(e) {
		e.preventDefault();
		$("html, body").animate({ scrollTop: 0 }, "slow");
		return false;
	});
		
});
/* *************************************** */
