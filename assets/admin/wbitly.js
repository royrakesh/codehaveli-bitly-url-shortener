(function($, window, undefined) {

	// Reset tooltip text on mouseout
	$('body').on('mouseout', '.copy_bitly', function () {
		$(this).find('.wbitly_tooltiptext').text("Click to Copy");
	});

	// Copy URL to clipboard on click
	$('body').on('click', '.copy_bitly', function (event) {
		event.preventDefault();

		const $url = $(this).find('.copy_bitly_link').text().trim();
		navigator.clipboard.writeText($url).then(() => {
			$(this).find('.wbitly_tooltiptext').text("Copied: " + $url);
		}).catch(() => {
			$(this).find('.wbitly_tooltiptext').text("Failed to Copy");
		});
	});


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