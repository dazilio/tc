jQuery(document).ready(function($){
	$('.tc-ajax').on('submit', function(e){
		e.preventDefault();
		$('.tc_form_submitbtn').html('');
		$('.tc-form-loading').css("display","block");
		var firstname = $('#fname').val();
		var lastname = $('#lname').val();
		var email_id = $('#email_id').val();
		var list_id	= $('#list_id').val();
		
		$.ajax({
			url: tc_forms.ajax_url,
			method:"POST",
			data: {
				action:'tc_form',
				fname:firstname,
				lname:lastname,
				email:email_id,
				list:list_id,
			}, 
			success: function(response){
				$('.tc_form_submitbtn').html('Subscribe');
				$('.tc-form-loading').css("display","none");
				$(".success_msg").css("display","block");
				$('.tc-ajax')[0].reset();
			},
			error: function(data){
				$('.tc_form_submitbtn').html('Subscribe');
				$('.tc-form-loading').css("display","none");		
				$(".error_msg").css("display","block");  
			}
		});
	});
});