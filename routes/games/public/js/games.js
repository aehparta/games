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
				setTimeout(this.refresh, 5000);
			},
		}
	});
} else if ($('#game').length) {
	var app = new Vue({
		el: '#game',
		data: {
			id: null,
			game: { metadata: {} },
			vars: [],
			maps: [],
			players: [],
			cmd: {
				input: '',
				output: null
			},
			timer: null,
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
				if (this.maps.length < 1) {
					api.games.maps.read(this.id).done(function(data) {
						app.maps = Object.assign({}, app.maps, data.data);
					});
				}
				api.games.players.read(this.id).done(function(data) {
					data.data.sort(function(a, b) {
						if (a.score > b.score) {
							return -1;
						} else if (a.score < b.score) {
							return 1;
						}
						return 0;
					});
					app.players = Object.assign({}, data.data);
				});
				api.games.vars.read(this.id).done(function(data) {
					app.vars = Object.assign({}, app.vars, data.data);
				});
				this.timer = setTimeout(this.refresh, 5000);
			},
			sendCommand: function() {
				app.cmd.output = null;
				api.games.cmd.create(this.id, {
					cmd: this.cmd.input
				}).done(function(data) {
					app.cmd.output = data.data;
				});
			},
			sendAction: function(cmd) {
				api.games.cmd.create(this.id, {
					cmd: cmd
				});
			},
			setVar: function(e) {
				clearTimeout(this.timer);
				api.games.vars.update(this.id, e.target.name, {
					value: e.target.value
				}).done(function() {
					app.refresh();
				});
			},
			setMap: function(e) {
				clearTimeout(this.timer);
				api.games.map.update(this.id, e.target.value).done(function() {
					app.refresh();
				});
			},
			kick: function(id) {
				api.games.players.destroy(this.id, id);
			},
		}
	});
}