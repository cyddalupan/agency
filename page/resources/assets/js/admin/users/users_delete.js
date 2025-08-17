myApp.controller('admin-user', ['$scope', function($scope) {

	console.log('admin user page');
	
	the_u_id = 0;
	$('.cyd_save_user_id').click(function(){
		the_u_id = $(this).attr('data-user');
		console.log('User selected is ' + the_u_id);
	});

	$('.cyd_delete_user').click(function(){
		console.log('function user_delete.js');
		var r = confirm("Are you Sure you want to Delete User?");
		if (r == true) {
		    $.post( base_url + 'page/delete_user',{user_id:the_u_id},function(user_delete_result){
				console.log(user_delete_result);
			})
			.fail(function() {
			    alert( "Server Error, Please Refresh." );
			})
			.done(function() {
			    $('.cyd_hide_me_'+the_u_id).fadeOut('slow');
			});
		}
	});
}]);