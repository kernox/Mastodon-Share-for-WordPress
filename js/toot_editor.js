var toot_editor = {

	title: '',
	excerpt: '',
	permalink: '',
	hashtags: '',
	message: '',

	field: {
		toot: document.getElementById('mastoshare_toot'),
		toot_current_size: document.getElementById('toot_current_size'),
		toot_limit_size: document.getElementById('toot_limit_size'),
		template: document.getElementById('mastoshare_toot_template'),

		title: document.getElementById('title'),
		excerpt: document.getElementById('excerpt'),
		permalink: document.getElementById('edit-slug-box'),
		tags: document.querySelector('ul.tagchecklist')
	},

	init: function(e) {

		this.field.toot_limit_size.innerText = this.field.toot.attributes.maxlength.value;
		this.bind_events();
		this.generate_toot();
	},
	generate_toot: function(reduce_of) {

		if(reduce_of == undefined)
			reduce_of = 0;

		this.message = this.field.template.value;

		this.title = this.field.title.value;
		this.excerpt = this.get_excerpt(reduce_of);
		this.permalink = this.get_permalink();
		this.hashtags = this.get_hashtags();

		var metas = [
			{name: 'title', value: this.title},
			{name: 'excerpt', value: this.excerpt},
			{name: 'permalink', value: this.permalink},
			{name: 'tags', value: this.hashtags}
		];

		for(var i in metas) {
			var item = metas[i];
			this.message = this.message.replace('[' + item.name + ']', item.value);
		}

		if(this.message.length > toot_limit_size){
			this.generate_toot(reduce_of - 1);
		}

		this.field.toot.value = this.message;
		this.update_chars_counter();

	},
	get_excerpt: function(reduce_of) {

		var content = tinymce.editors.content.getContent({format : 'text'});

		if(typenow != 'page'){

			if(this.field.excerpt.value.length != 0) {
				content = this.remove_html_tags(this.field.excerpt.value);
			}
		}

		if(reduce_of !==0)
		{
			content = content.split(/(\n|\s)/).slice(0,reduce_of);
			var last_word = content[content.length-1];

			content = content.join('').replace(/(\s|\n)+$/, '') + '...';
		}

		return content;
	},
	get_permalink: function() {

		var current_path = window.location.href;

		var sample_permalink = document.getElementById('sample-permalink').innerText;
		var editable_post_name =document.getElementById('editable-post-name').innerText;
		var editable_post_name_full = document.getElementById('editable-post-name-full').innerText;

		var permalink = sample_permalink.replace(editable_post_name, editable_post_name_full);

		return permalink;
	},
	get_hashtags: function() {
		var tags = document.querySelectorAll('#tagsdiv-post_tag .tagchecklist span.screen-reader-text');
		var hashtags = '';

		tags.forEach(function(item){
			hashtags +='#' + item.innerText.split(':')[1].trim() + ' ';
		});

		return hashtags.trim();
	},
	update_chars_counter: function(){
		this.field.toot_current_size.innerText = this.field.toot.value.length;
	},
	remove_html_tags: function(string) {
		return string.replace(/<(?!\/?>)[^>]*>/gm, '');
	},
	bind_events: function() {

		var that = this;

		var events = [
			{element: this.field.title, action: 'keyup'},
			{element: this.field.excerpt, action: 'keyup'},
			{element: this.field.permalink, action: 'DOMSubtreeModified'},
			{element: this.field.tags, action: 'DOMSubtreeModified'},
		];

		for (var i in events){
			events[i].element.addEventListener(events[i].action, function() {
				that.generate_toot();
			});
		}

		this.field.toot.addEventListener('keyup', function(){
			that.update_chars_counter();
		});
	}
};