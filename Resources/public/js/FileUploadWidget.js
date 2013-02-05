var FileUploadWidget = Class.create({

	initialize: function(config) {
		
		var $this = this;
		
		this.container = $(config.container);
		
		this.file_input = this.container.find('input.file-input');
		
		this.file_input.attr('value', '');
		
		this.upload_button = this.container.find('.btn-file');
		
		this.file_id_field = this.container.find('input[name="form[file_id]"]');
		
		if(config.image_container){
		
			this.image_container = $(config.image_container);
		}
		
		this.files = new Array();
		
		this.started = 0;
		
		this.max_file_size = config.max_file_size;
		
		this.parent_id = config.parent_id;
		
		this.progress = this.container.find('.progress');
		
		this.jqXHR = this.file_input.fileupload({
			
			dataType: 'json',
			
			maxChunkSize: 0,
			
			done: function (e, data) {
				
				$this.started --;
				
				if(data.result.status == 'fail' && data.result.errors.length > 0){
					
					alert(data.result.errors[0]);
				}
				
				if($this.started == 0){
					
					$this.container.find('.progress .bar').width('100%');
					
					var $progress = 
					
					$this.progress.addClass('progress-success');
					
					$this.progress.removeClass('active');
					
					$this.progress.hide();
					
					var file_id = data.result.file.id;
					
					if($this.image_container){
						
						$this.image_container.html('<img src="/image/' + file_id + '/200/200/quality:75"/>');
					}
					
					//alert(file_id);
					
					$this.file_id_field.attr('value', file_id);
					
					$this.container.find('.close').remove();
					
					$this.upload_button.show();
				}
			},
			
			add: function (e, data) {
				
				$.each(data.files, function (index, file) {
					
					if(file.size > $this.max_file_size){
						
						var size_in_mb = Math.round($this.max_file_size / 1024 / 1024 * 100)/100;
						
						alert('The file ' + file.name + ' is too big.  The max allowed file size is ' + size_in_mb + 'MB');
						
						return;	
					}
					

					$this.started ++;
					
					$this.upload_button.hide();
					$this.progress.show();
					$this.container.find('.close').show();
					
					data.url = $this.file_input.data('url');
					
					var jqXHR = data.submit()
						.success(function (result, textStatus, jqXHR) {
							
							//alert('success');
						})
						.error(function (jqXHR, textStatus, errorThrown) {
							
							$this.started --;
							
							if($this.started == 0){
								
								//$this.refresh_data();
							}
							
							if (errorThrown === 'abort') {
								
					            //alert('File Upload has been canceled');
					        
					        }else{
					        	
								alert(jqXHR.responseText);
							}
					
						})
						.complete(function (result, textStatus, jqXHR) {
							
							//alert('here');
							
						});
					
					file.jqXHR = jqXHR;
					
					$this.container.find('.close').click(function(e){
						
						e.preventDefault();
						
						$this.container.find('.close').hide();
						$this.container.find('.progress').hide();
						
						$this.upload_button.show();
						
						jqXHR.abort();
						
					});
					
				
					$this.files.push(file);
					
				});
				
				
			},
			
			progress: function (e, data) {
				
				
				
				var progress = parseInt(data.loaded / data.total * 100, 10);
			
				$this.container.find('.bar').width(progress + '%');
				
				
			}
		});	
	},
	
});