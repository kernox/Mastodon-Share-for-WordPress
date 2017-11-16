var $ = jQuery;

$(document).ready(function(){	

	var template = $('#mastoshare_toot_template');
	var toot = $('#mastoshare_toot');
	var post_type = $('#post_type').val();

	var title = $('#title');
	var tags =  $('#post_tag .tagchecklist span');
	var content = '';
	var excerpt = $('#excerpt');
	var permalink = $('#sample-permalink');
	var final_permalink = permalink.text();
	var slug = $('#editable-post-name').text();
	var message = '';
	var toot_limit_size = toot.attr('maxlength');
	var toot_limit_size_span = $('#toot_limit_size');
	var toot_current_size_span = $('#toot_current_size');
	var final_excerpt = '';

	toot_limit_size_span.text(toot_limit_size);

	function generate_hashtags() {

		var tags = $('#tagsdiv-post_tag .tagchecklist span.screen-reader-text');
		var hashtags = '';
		tags.each(function(index, item){
			hashtags+='#' + $(item).text().split(':')[1].trim() + ' ';
		});

		return hashtags.trim();
	}

	function generate_toot(reduce_of = 0) {

		message = template.val();
		content = tinymce.editors.content.getContent({format : 'text'});		

		if(post_type == 'page'){
			final_excerpt = content;
		} else {

			if(excerpt.val().length != 0) {
				final_excerpt = excerpt.val();
			} else {
				final_excerpt = content;
			}
		}
	

		var new_slug = $('#editable-post-name').text();

		if(new_slug.length > 0) {
			final_permalink = final_permalink.replace(slug, new_slug);
			slug = new_slug;
		}
		
		//If toot reduce needed
		if(reduce_of !== 0) {
			var words = final_excerpt.split(' ');
			words = words.slice(0, reduce_of);	
			final_excerpt = words.join(' ');
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
		
		if (message.length > toot_limit_size) {

			if(reduce_of == 0){
				reduce_of = -1;
			} else {
				reduce_of = reduce_of -1;
			}
			generate_toot(reduce_of);
		} else {
			toot_current_size_span.text(message.length);
			toot.val(message);

		}	

	};

	toot.bind('input propertychange', function() {
		toot_current_size_span.text(toot.val().length);
	});

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
			
			//Force tagsListReady to true for page
			if(post_type == 'page') {
				tagsListReady = true;
			}

			if( contentEditor != undefined && tagsListReady) {
				
				tinymce.editors.content.on('keyup', function() {
					generate_toot();
				});

				if(post_type == 'post') {
					$('#tagsdiv-post_tag').on('DOMSubtreeModified', function() {
						generate_toot();
					});	
				}

				$('#edit-slug-box').on('DOMSubtreeModified', function(event) {
					generate_toot();					
				});

				generate_toot();
				clearInterval(watcher);				
			}					
		}
	}, 500);
	
});