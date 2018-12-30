var api = new $.RestClient('/api/', {
	stringifyData: true
});
api.add('games');
api.games.add('vars');
api.games.add('cmd');
api.games.add('maps');
api.games.add('map');
api.games.add('players');
