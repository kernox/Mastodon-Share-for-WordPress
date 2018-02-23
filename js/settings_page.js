jQuery(document).ready(function($) {  
	//Variable to know if advanced config is shown
	let advancedConfigShown = false;
	
	//Select the right radio button for message
	let textareaValue = $('textarea[name=message]').val();
	$('input:radio[name=message_template]').prop("checked",false);
	$('input[value="'+textareaValue+'"]').prop("checked", true);	

	//Toogle for advanced config
	$("#show_advanced_configuration").click(function(){ 
		if(advancedConfigShown){
			//Hide advanced config
			$(".advanced_setting").fadeOut("fast");
			$(".not_advanced_setting").fadeIn("slow");
			advancedConfigShown = false;
		}else{
			$(".not_advanced_setting").fadeOut("fast");
			$("td.advanced_setting").fadeIn("slow");
			$("tr.advanced_setting").fadeIn("slow").css("display","block");
			advancedConfigShown = true;
		}
	});

	//Set the message value on radio select
	$('input:radio[name=message_template]').change(function(){
			let value = $('input:radio[name=message_template]:checked').val();
			$('textarea[name=message]').val(value);
	});
});
