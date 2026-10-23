jQuery(function ($) {
	var cfg = window.fktr_license_object;
	$('.fktr-licenses').on('click', '.fktr-license-act', function () {
		var $btn = $(this), $card = $btn.closest('.fktr-license-card'), $msg = $card.find('.fktr-license-message');
		$card.find('.fktr-license-act').prop('disabled', true);
		$msg.attr('class', 'fktr-license-message').text(cfg.working);
		$.post(cfg.ajax_url, {
			action: 'fktr_license',
			nonce: cfg.nonce,
			plugin: $card.data('plugin'),
			do: $btn.data('do')
		}).done(function (res) {
			if (res && res.success) {
				$card.find('.fktr-license-status').html(res.data.html);
				$msg.text(res.data.message || '');
			} else {
				$msg.addClass('err').text(res && res.data ? res.data.message : cfg.failed);
				$card.find('.fktr-license-act').prop('disabled', false);
			}
		}).fail(function () {
			$msg.addClass('err').text(cfg.failed);
			$card.find('.fktr-license-act').prop('disabled', false);
		});
	});
});
