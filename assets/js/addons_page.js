jQuery(function ($) {
	var cfg = window.fktr_addons_object || {};

	// The views of the table link to the plugins list: keep them on this screen.
	$('.subsubsub a').each(function () {
		var href = $(this).attr('href');
		if (href && href.indexOf('page=' + cfg.page) < 0) {
			$(this).attr('href', href + (href.indexOf('?') < 0 ? '?' : '&') + 'page=' + cfg.page);
		}
	});

	// Narrows the rows on screen while typing; Enter still runs the server-side search.
	var $box = $('#fktr-addons-search-input'),
		$table = $('#bulk-action-form table.plugins'),
		$live = $('.fktr-addons-filter-live');
	if (!$box.length || !$table.length) {
		return;
	}

	var $rows = $table.find('tbody tr').not('.plugin-update-tr, .no-items'),
		total = $rows.length,
		cols = $table.find('thead th, thead td').length || 1,
		$empty = $('<tr class="no-items fktr-addons-no-matches" hidden><td class="colspanchange"></td></tr>').appendTo($table.find('tbody').first());
	$empty.find('td').attr('colspan', cols).text(cfg.none);

	function filter() {
		var needle = $.trim($box.val()).toLowerCase(),
			shown = 0;

		$rows.each(function () {
			var $row = $(this),
				hay = $row.data('fktrHaystack');
			if (undefined === hay) {
				hay = $row.text().replace(/\s+/g, ' ').toLowerCase();
				$row.data('fktrHaystack', hay);
			}
			var hit = (needle === '' || hay.indexOf(needle) >= 0);
			$row.toggle(hit);
			// The update notice is a row of its own, tied to the one above it.
			$table.find('tbody tr.plugin-update-tr[data-plugin="' + $row.attr('data-plugin') + '"]').toggle(hit);
			if (hit) {
				shown++;
			}
		});

		$empty.attr('hidden', shown !== 0 || needle === '').toggle(shown === 0 && needle !== '');

		if (needle === '' || shown === total) {
			$live.attr('hidden', true).text('');
		} else {
			$live.removeAttr('hidden').text(cfg.live.replace('%1$s', shown).replace('%2$s', total));
		}
	}

	$box.on('keyup search input', function (e) {
		if (e.keyCode === 27) { // Esc clears.
			$box.val('');
		}
		filter();
	});
	filter();
});
