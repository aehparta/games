Vue.component('game', {
	template: '#game-template',
	props: {
		game: Object
	}
});
var games = new Vue({
	el: '#games',
	data: {
		games
	},
	created: function() {
		this.refresh();
	},
	methods: {
		refresh: function() {
			api.games.read().done(function(data) {
				this.games = data.data;
				console.log(this.games);
			});
			setTimeout(this.refresh, 1000);
		}
	}
});