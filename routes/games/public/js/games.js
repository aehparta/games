$(document).ready(function() {
	$('body').on('click', '.game-map-set', function(e) {
		e.preventDefault();
		var urlset = $(this).attr('map-set');
		var urlget = $(this).attr('map-get');
		BootstrapDialog.show({
			closable: false,
			title: 'Loading map',
			message: 'Please wait',
		});
		$.get(urlset).done(function() {
			setInterval(function() {
				$.getJSON(urlget, function(data) {
					if (data.data !== null) {
						setTimeout(function() {
							location.reload();
						}, 500);
					}
				});
			}, 1000);
		});
	});
});