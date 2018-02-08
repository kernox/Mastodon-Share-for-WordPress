function(ed){

	ed.on('init', function(e){
		toot_editor.init(e);
	})

	ed.on('keyup', function(e){
		console.log('keyup');
		toot_editor.generate_toot();
	})	
}