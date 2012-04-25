var FileList = ListWidget.create({

	initialize: function(config) {
		
		this._super( config );
		
		var $this = this;
		
		this.file_input = this.container.find('input.list-file-input');
		
		this.file_input.attr('value', '');
		
		this.files = new Array();
		
		this.started = 0;
		
		this.jqXHR = this.file_input.fileupload({
			
			dataType: 'json',
			
			maxChunkSize: 0,
			
			done: function (e, data) {
				
				$this.started --;
				
				if($this.started == 0){
				
					$this.refresh_data();
				}
				
				/*$.each(data.result, function (index, file) {
					
					alert('here');
					//$('<p/>').text(file.name).appendTo(document.body);
				});*/
			},
			
			add: function (e, data) {
				
				$.each(data.files, function (index, file) {
					
					$this.started ++;
					
					var $new_row = $this.add_row();
					
					$new_row.find('.name-placeholder').replaceWith(file.name);
					
					var progress = '<div class="progress progress-striped active" style="width:100px;"><div class="bar" style="width: 0%;"></div></div>';
					
					
					$new_row.find('.edit-placeholder').replaceWith(progress);
					
					file.$row = $new_row;
					
				   	$this.files.push(file);
					
				});
				
				data.url = $this.file_input.data('url');
				
				var jqXHR = data.submit()
					.success(function (result, textStatus, jqXHR) {})
					.error(function (jqXHR, textStatus, errorThrown) {
						
						$this.refresh_data();
						
						alert(jqXHR.responseText);
						
						/*var upload_file = jqXHR.data.files[0];
						$.each($this.files, function (index, file) {
					
							if(file.name == upload_file.name){
								
								file.$row.detach();
								
								//console.log(upload_file.name + ': progress: ' + progress);
							}
						});*/
					})
					.complete(function (result, textStatus, jqXHR) {});
			},
			
			progress: function (e, data) {
				
				var progress = parseInt(data.loaded / data.total * 100, 10);
				
				
				
				var upload_file = data.files[0];
				
				$.each($this.files, function (index, file) {
					
					if(file.name == upload_file.name){
						
						file.$row.find('.bar').width(progress + '%');
						
						//console.log(upload_file.name + ': progress: ' + progress);
					}
				});
			}
		});

		
	},
	
	upload: function(target){
		
		//$(target).click();
		
		/*this.file_input.attr('value', '');
		
		this.file_input.click();*/
	}
});