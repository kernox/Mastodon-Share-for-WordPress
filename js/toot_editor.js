var $ = jQuery;

$(document).ready(function(){	

	var template = $('#mastoshare_toot_template');

	var title = $('#title');
	var tags =  $('#post_tag .tagchecklist span');
	var content = '';
	var excerpt = $('#excerpt');
	var permalink = $('#sample-permalink');
	var final_permalink = permalink.text();
	var slug = $('#editable-post-name').text();
	var message = '';

	var toot = document.getElementById('mastoshare_toot');

	function generate_hashtags() {

		var tags = $('#tagsdiv-post_tag .tagchecklist span.screen-reader-text');
		var hashtags = '';
		tags.each(function(index, item){
			hashtags+='#' + $(item).text().split(':')[1].trim() + ' ';
		});

		return hashtags.trim();
	}

	function generate_toot() {

		message = template.val();
		content = tinymce.editors.content.getContent({format : 'text'});		

		if(excerpt.val().length != 0) {
			final_excerpt = excerpt.val();
		} else {
			final_excerpt = content;
		}

		var new_slug = $('#editable-post-name').text();

		if(new_slug.length > 0) {
			final_permalink = final_permalink.replace(slug, new_slug);
			slug = new_slug;
		}		

		var metas = [
			{name: 'title', value: title.val()},
			{name: 'excerpt', value: final_excerpt},
			{name: 'permalink', value: final_permalink},
			{name: 'tags', value: generate_hashtags()}
		];

		for(i in metas) {
			var item = metas[i];

			message = message.replace('[' + item.name + ']', item.value);
		}		

		toot.value = message;

	};

	title.on('keyup', function(){
		generate_toot();
	});

	excerpt.on('keyup', function(){
		generate_toot();
	});

	var watcher = setInterval(function() {
		
		if(tinymce.editors.length > 0) {
			var contentEditor = tinymce.editors.content;

			var tagsListReady = $('#tagsdiv-post_tag .tagchecklist span.screen-reader-text').length > 0;

			if( contentEditor != undefined && tagsListReady) {
				
				tinymce.editors.content.on('keyup', function(){
					generate_toot();
				});

				$('#tagsdiv-post_tag').on('DOMSubtreeModified', function() {
					generate_toot();
				});	

				$('#edit-slug-box').on('DOMSubtreeModified', function(event) {
					generate_toot();					
				});

				generate_toot();
				clearInterval(watcher);	
				
			}
			
		}
	}, 500);
	
});