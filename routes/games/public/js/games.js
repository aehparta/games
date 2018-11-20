if ($('#games').length) {
	var app = new Vue({
		el: '#games',
		data: {
			games: [],
		},
		created: function() {
			this.refresh();
		},
		methods: {
			refresh: function() {
				api.games.read().done(function(data) {
					app.games.length = 0;
					data.data.forEach(function(e) {
						app.games.push(e);
					});
				});
				setTimeout(this.refresh, 2000);
			},
		}
	});
} else if ($('#game').length) {
	var app = new Vue({
		el: '#game',
		data: {
			id: null,
			game: {},
			vars: [],
			cmd: {
				input: '',
				output: null
			},
		},
		created: function() {
			this.id = window.location.pathname.replace(/\//g, '');
			this.refresh();
		},
		methods: {
			refresh: function() {
				api.games.read(this.id).done(function(data) {
					app.game = Object.assign({}, app.game, data.data);
				});
				setTimeout(this.refresh, 2000);
			},
			sendCommand: function() {
				app.cmd.output = null;
				api.games.cmd.create(this.id, {
					cmd: this.cmd.input
				}).done(function(data) {
					app.cmd.output = data.data;
				});
			},
		}
	});
}