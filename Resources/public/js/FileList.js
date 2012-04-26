var FileList = ListWidget.create({

	initialize: function(config) {
		
		this._super( config );
		
		var $this = this;
		
		this.file_input = this.container.find('input.list-file-input');
		
		this.file_input.attr('value', '');
		
		this.files = new Array();
		
		this.started = 0;
		
		this.max_file_size = config.max_file_size;
		
		this.jqXHR = this.file_input.fileupload({
			
			dataType: 'json',
			
			maxChunkSize: 0,
			
			done: function (e, data) {
				
				$this.started --;
				
				if(data.result.status == 'fail' && data.result.errors.length > 0){
					
					alert(data.result.errors[0]);
				}
				
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
					
					if(file.size > $this.max_file_size){
						
						var size_in_mb = Math.round($this.max_file_size / 1024 / 1024 * 100)/100;
						
						alert('The file ' + file.name + ' is too big.  The max allowed file size is ' + size_in_mb + 'MB');
						
						return;	
					}
					

					$this.started ++;
					
					var $new_row = $this.add_row();
					
					$new_row.find('.name-placeholder').replaceWith(file.name);
					
					$new_row.find('.type-placeholder').replaceWith(file.type);
					
					var progress = '<div class="progress progress-striped active"><div class="bar" style="width: 0%;"></div></div> <a class="btn btn-mini close" href="#">&times;</a>';
					
					$new_row.find('.edit-placeholder').replaceWith(progress);
					
					
					
					data.url = $this.file_input.data('url');
					
					var jqXHR = data.submit()
						.success(function (result, textStatus, jqXHR) {
							
						})
						.error(function (jqXHR, textStatus, errorThrown) {
							
							$this.started --;
							
							if($this.started == 0){
								
								$this.refresh_data();
							}
							
							if (errorThrown === 'abort') {
								
					            //alert('File Upload has been canceled');
					        
					        }else{
					        	
								alert(jqXHR.responseText);
							}
					
						})
						.complete(function (result, textStatus, jqXHR) {
							
							$new_row.find('.progress .bar').width('100%');
							$new_row.find('.progress').addClass('progress-success');
							$new_row.find('.close').remove();
							
						});
					
					file.jqXHR = jqXHR;
					
					$new_row.find('.close').click(function(e){
						
						e.preventDefault();
						
						$new_row.detach();
						
						jqXHR.abort();
						
					});
					
					file.$row = $new_row;
					
					$this.files.push(file);
					
				});
				
				
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