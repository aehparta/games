$(document).ready(function()
{
	$('body').on('click', '.game-map-set', function(e)
	{
		e.preventDefault();
		var urlset = $(this).attr('map-set');
		var urlget = $(this).attr('map-get');
		BootstrapDialog.show(
		{
			closable: false,
			title: 'Loading map',
			message: 'Please wait',
		});
		$.get(urlset).done(function()
		{
			setInterval(function()
			{
				$.getJSON(urlget, function(data)
				{
					if (data.data !== null)
					{
						setTimeout(function()
						{
							location.reload();
						}, 500);
					}
				});
			}, 1000);
		});
	});

	$('body').on('click', '#game-cmd-send', function(e)
	{
		e.preventDefault();
		var url = $(this).attr('url');
		var data = {
			cmd: $('#game-cmd-input').val(),
		};
		$('#game-cmd-output').hide();
		if (data.cmd.length > 0)
		{
			$.post(url, data, function(json)
			{
				var data = JSON.parse(json);
				if (data.data && data.data.length > 0)
				{
					$('#game-cmd-output').html(data.data);
				}
				else
				{
					$('#game-cmd-output').html('no data received');
				}
				$('#game-cmd-output').show();
			});
		}
	});

	$('body').on('change', '.game-var-select', function(e)
	{
		var url = $(this).attr('var-url');
		var data = {
			value: $(this).val(),
		};
		BootstrapDialog.show(
		{
			closable: false,
			title: 'Setting new value',
			message: 'Please wait',
		});
		$.post(url, data, function(json)
		{
			location.reload();
		});
	});
});