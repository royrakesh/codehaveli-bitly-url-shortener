(function($, window, undefined) {




	$('body').on('click', '.generate_bitly', function(event) {
		event.preventDefault();
		$wbitly_generate_button = $(this);
		let wbitly_post_id = $(this).attr('data-post_id');
		let wbitly_nonce   = $(this).attr('data-nonce');
		if (!wbitly_post_id) {
			$('.generate_bitly').addClass('generate_bitly_disable');
		}
		$.ajax({
			url: wbitlyJS.ajaxurl,
			data: {
				'action': 'generate_wbitly_url_via_ajax',
				'post_id': wbitly_post_id,
				_wpnonce: wbitly_nonce
			},
			method: 'POST',
			//Post method
			beforeSend: function() {
				$('.generate_bitly').addClass('generate_bitly_disable');
			},
			success: function(response) {
				console.log(response)
				if (response.data.status) {
					$main_container = $wbitly_generate_button.parent().parent();
					$main_container.html('').html(response.data.bitly_link_html)
				}
			},
			error: function(error) {
				$('.generate_bitly').removeClass('generate_bitly_disable');
			},
			complete: function() {
				$('.generate_bitly').removeClass('generate_bitly_disable');
			}
		})
	});
	
}(jQuery, window));