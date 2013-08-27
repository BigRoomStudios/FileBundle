var FileUpload = Class.create({
	
	initialize: function(config) {
		
	},
	
	add: function (e, data) {
		
	},
	
	progress: function (e, data) {
		
	},
	
});

$(function() {
	
	//find all upload areas
	$('.file_upload').each(function() {
		// create form components
		new JiveForm({
			container: this.id
		});
	}); 
	
});