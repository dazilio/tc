jQuery(document).ready(function($){
	$('#tc_list').on('change',function(){
		$('.shortcode').val("[my_ac list='" + this.value + "']");
	});
	$('form.tc-ajax').on('submit', function(e){
		e.preventDefault();
		$(".spin").show('slow');
		var name = $('#tc_list_name').val();
		var desc = $('#tc_list_desc').val();
		$.ajax({
			url: ajaxurl,
			method:"POST",
			dataType: 'html',
			data: {
				action:'tc_create_list',
				list_name:name,
				list_desc:desc,
			}, 
			success: function(data){
				$('#tc_list').html(data);
				$('.shortcode').val("[my_ac list='" + $('#tc_list').value + "']");
				$('.tc-ajax')[0].reset();
				$('.spin').hide('slow');

			}
		});
		
	});
});