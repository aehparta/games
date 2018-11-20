var api = new $.RestClient('/api/', {
	stringifyData: true
});
api.add('games');
api.games.add('vars');
api.games.add('cmd');