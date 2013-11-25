var FileUpload = Class.create({
	
	initialize: function(config) {
		this.container 		= $(config.container);
		this.maxFileSize 	= config.maxFileSize 			|| 0; //bytes
		this.dropSelector 	= config.dropSelector 			|| '.drop_area';
		this.dropArea 		= this.container.find(this.dropSelector);
		this.destinationUrl 	= config.destinationUrl 		|| this.container.data('upload-url');
		this.input 		= config.input 				|| this.container.find('input').first();
		this.postData 		= config.postData 			|| null;
		this.start 		= config.start 				|| this.startDefault;
		this.doUpload 		= config.doUpload 			|| function() {return true;};
		// callbacks
		this.success 		= config.success 	|| this.successDefault;
		this.fail 		= config.fail 		|| this.failDefault;
		this.progress 		= config.progress 	|| function(){};

		this.list = [];
		this.totalSize = 0;
		this.totalProgress = 0;
		
		var that = this;
		
		this.dropArea.on('drop', function (e) {
			that.handleDrop(e, that);
		});
		
		this.dropArea.on('dragover', function (e) {
			that.handleDragOver(e, that);
		});
		
		this.input.on('click', function(e) {
			this.value = null;
		});
		
		this.input.on('change', function(e) {
			that.processFiles(e.target.files);
		});
		
	},
    
	// draw progress
	drawProgress: function(progress) {
		this.container.find('.loading_bar').css('width', progress*100+'%');
	},

	// drag over
	handleDragOver: function(event, that) {
		//event.stopPropagation();
		event.preventDefault();
		
		that.dropArea.addClass('hover');
	},

	// drag drop
	handleDrop: function(event, that) {
		//event.stopPropagation();
		event.preventDefault();
		
		that.processFiles(event.originalEvent.dataTransfer.files);
	},

	// process bunch of files
	processFiles: function(filelist) {
		if (!filelist || !filelist.length || this.list.length) return;
	
		this.totalSize = 0;
		this.totalProgress = 0;
		//result.textContent = '';
		
		// note, there is a max of 5 files at a time here!
		for (var i = 0; i < filelist.length && i < 5; i++) {
			this.list.push(filelist[i]);
			this.totalSize += filelist[i].size;
		}
		this.uploadNext();
	},

	// on complete - start next file
	// xhr holds the response in responseText/responseJSON
	handleComplete: function(success, xhr, event, fileSize) {
		
		if (success) {
			
			try {
				response = JSON.parse(xhr.responseText);
			} catch(err) {
				response = {errors: 'JSON parse error.'};
			}
			
			if (response.status == 'success') {
				this.success(response);
			} else {
				this.fail(response);
			}
			
		} else {
			this.fail({errors: 'POST error.'});
		}
		
		this.totalProgress += fileSize;
		this.drawProgress(this.totalProgress / this.totalSize);
		this.uploadNext();
	},

	successDefault: function(response) {
		
		fileElement = $('<div class="file grid-item one_fourth">'    +
					'<div class="file_thumbnail"></div>' +
					'<div class="file_name"></div>'      +
				'</div>');
		
		fileObj = response.file;
		
		if (fileObj.id) {
			fileElement.data('file-id', fileObj.id);
			fileElement.data('delete-link', response.delete_link);
			fileElement.children('.file_name').html(fileObj.name);
		}
		
		if (response.thumb_url) {
			fileElement.children('.file_thumbnail').css({backgroundImage: "url('"+response.thumb_url+"')"});
		}
		
		this.container.find('.file_list').append(fileElement);
	},
	
	
	failDefault: function(response) {
		
	},
	
	startDefault: function(event) { },
	
	// update progress
	handleProgress: function(event) {
		
		var progress = this.totalProgress + event.loaded;
		this.drawProgress(progress / this.totalSize);
		
		this.progress(event, progress);
		
	},

	// upload file
	uploadFile: function(file) {
		
		// prepare XMLHttpRequest
		var that = this;
		var xhr = new XMLHttpRequest();
		xhr.open('POST', this.destinationUrl);
		xhr.onload = function(event) {
			//result.innerHTML += this.responseText;
			that.handleComplete(true, this, event, file.size);
		};
		xhr.onerror = function(event) {
			//result.textContent = this.responseText;
			that.handleComplete(false, this, event, file.size);
		};
		xhr.upload.onprogress = function(event) {
			that.handleProgress(event);
		}
		xhr.upload.onloadstart = function(event) {
			that.start(event);
		}
	
		// prepare FormData
		var formData = new FormData();
		formData.append('file', file);
		for (o in this.postData) {
			formData.append(o, this.postData[o])
		}
		
		xhr.send(formData);
	},

	// upload next file
	uploadNext: function() {
		if (this.list.length) {
			//count.textContent = list.length - 1;
			this.dropArea.addClass('uploading');
	    
			var nextFile = this.list.shift();
			
			// if there's a max file size and this file is too big, fail.  otherwise upload that sucka.
			if (!this.doUpload() ||
			    (this.maxFileSize && nextFile.size >= this.maxFileSize)) {
				//result.innerHTML += '<div class="f">Too big file (max filesize exceeded)</div>';
				this.handleComplete(false, null, null, nextFile.size);
			} else {
				this.uploadFile(nextFile);
			}
		} else {
			this.dropArea.removeClass('uploading hover');
		}
	}
	
	
});

var docBlocks = [];
var imgBlocks = [];
var docCb = function(){};
var imgCb = function(){};
$(function() {
	

	$('.property-block.documents').each(docCb = function() {
		
		$(this).on('dragover', function(e) {
				
			e.stopPropagation();
			e.preventDefault();
				
			$(this).find('.drop_area').show();
			
		});
		
		$(this).on('dragleave', function(e) {
			
			if ($(this).is(e.target)) {
				
				e.stopPropagation();
				e.preventDefault();	
				
				$(this).find('.drop_area').hide();
			}
			
		});
		
		$(this).on('drop', function(e) {
			e.preventDefault();	
			
			$(this).find('.drop_area').hide();
		});
		
		fileUploader = new FileUpload({
			container: $(this),
			success: function(response) {
					
					this.container.find('.loading').remove();
					
					fileElement = $('<div class="document grid-item one_half lap-one_fourth">' 	+
								'<div class="document_preview">' 			+
									'<div class="document_thumb">'			+
										'<span class="icon-document"></span>'	+
										'<div class="remove icon-trash"></div>' +
									'</div>' 					+
									'<div class="document_ext">' 			+
										'<span></span>' 			+
									'</div>' 					+ 
								'</div>' 						+ 
								'<div class="document_upload_date"></div>' 		+
								'<div class="document_name"><a></a></div>' 		+
							'</div>');
					previewElement = fileElement.children('.document_preview');
					
					fileObj = response.file;
					
					if (fileObj.id) {
						
						fileElement.data('file-id', fileObj.id);
						fileElement.data('delete-link', response.delete_link);
						previewElement.addClass(fileObj.ext);
						if (response.thumb_url) {
							previewElement.children('.document_thumb').css({backgroundImage: "url('"+response.thumb_url+"')"});
						}
						previewElement.children('.document_ext').children('span').html(fileObj.ext);
						fileElement.children('.document_upload_date').html('Uploaded - ' + response.upload_date);
						fileElement.children('.document_name').children('a').html(fileObj.name);
						fileElement.children('.document_name').children('a').attr('href', response.download_link);
						
						this.container.find('.file_list').append(fileElement);
					}
					
					this.container.trigger('block.reflow');
					
				},
			fail: function(response) {
				
				this.container.find('.loading').remove();
				
				alert(response.errors);
			},
			start: function(e) {
				
				loadingElement = $('<div class="document grid-item one_half lap-one_fourth loading">' 		+
							'<div class="loading_container"><div class="loading_bar"></div></div>' 	+
						   '</div>');
				
				this.container.find('.file_list').append(loadingElement);
				
			}
		});
		
		docBlocks.push(fileUploader);
	});
	
	
	$('.property-block.photos').each(imgCb = function() {
		
		$(this).on('dragover', function(e) {
				
			e.stopPropagation();
			e.preventDefault();
				
			$(this).find('.drop_area').show();
			
		});
		
		$(this).on('dragleave', function(e) {
			
			if ($(this).is(e.target)) {
				
				e.stopPropagation();
				e.preventDefault();	
				
				$(this).find('.drop_area').hide();
			}
			
		});
		
		$(this).on('drop', function(e) {
			e.preventDefault();	
			
			$(this).find('.drop_area').hide();
		});
		
		fileUploader = new FileUpload({
			container: $(this),
			input: $(this).find('input.image_upload_input').first(),
			success: function(response) {
					
					loadingElement = this.container.find('.loading_container');
					
					loadingElement.siblings('.icon-plus-camera').removeAttr('style');
					loadingElement.remove();
					
					if (response.thumb_url) {
						
						fileObj = response.file;
						images = this.container.find('.image');
						
						new_image = images.filter('.image-next');
						new_image.data('file-id', fileObj.id);
						new_image.data('delete-link', response.delete_link);
						new_image.data('update-link', response.update_link);
						new_image.data('title', response.file.title);
						new_image.data('description', response.file.description);
						new_image.removeClass('image-next').addClass('image-active');
						new_image.css({backgroundImage: "url('"+response.thumb_url+"')"});
						new_image.children('.caption').html(fileObj.name);
						
						next_id = images.index(new_image) + 1;
						next_image = images.eq(next_id);
						
						if (next_image.length) {
							next_image.removeClass('image-empty').addClass('image-next');
						}
						
						this.container.trigger('block.reflow');
						
					}
					
				},
			fail: function(response) {
				
				loadingElement = this.container.find('.loading_container');
				
				loadingElement.siblings('.icon-plus-camera').removeAttr('style');
				loadingElement.remove();
				
				alert(response.errors);
			},
			start: function(e) {
				
				loadingElement = $('<div class="loading_container"><div class="loading_bar"></div></div>');
				
				this.container.find('.image-next').first().append(loadingElement);
				loadingElement.siblings('.icon-plus-camera').css('display', 'none');
				
			},
			doUpload: function() {
				if ( this.container.find('.image.image-active').length >= 5 ) {
					alert('5 images max.');
					return false;
				} else {
					return true;
				}
			}
		});
		
		docBlocks.push(fileUploader);
	});
	
	//find all upload areas
	$('.file_upload').each(function() {
		// create form components
		new JiveForm({
			container: this.id,
		});
	}); 
	
});