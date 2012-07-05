<?php

namespace BRS\FileBundle\Widget;

use BRS\CoreBundle\Core\Widget\ListWidget;
use BRS\FileBundle\Entity\File;


/**
 * File list widget
 * 
 */
class FileList extends ListWidget
{
		
	protected $template = 'BRSFileBundle:Widget:file.list.html.twig';
		
	public function __construct()
	{
		parent::__construct();	
		
		$this->setEntityName('BRSFileBundle:File');
		
		$list_fields = array(
	
			'edit' => array(
				'type' => 'link',
				'route' => array(
					'name' => 'brs_file_fileadmin_edit',
					'params' => array('id'),
				),
				'nav' => true,
				'label' => 'edit',
				'width' => 120,
				'nonentity' => true,
				'class' => 'btn btn-mini',
			),
			
			'thumb' => array(
				'type' => 'thumbnail',
				'width' => 55,
				'nonentity' => true,
				'file_id_field' => 'id',
			),
			
			'name' => array(
				'type' => 'link',
				'route' => array(
					'name' => 'brs_file_file_download',
					'params' => array('id'),
				),
			),
			
			'type' => array(
				'type' => 'text',
			),
			
			'title' => array(
				'type' => 'text',
			),
		);
		
		$this->setListFields($list_fields);
	}
	
	public function getVars($render = true){
		
		$form = $this->getFileUploadForm();
		
		//$upload_url = $this->generateUrl('brs_file_file_upload');
		$upload_url = $this->getActionUrl('upload');
		
		$add_vars = array(
			'form' => $form->createView(),
			'max_file_size' => (int)ini_get('upload_max_filesize') * 1024 * 1024,
			'upload_url' => $upload_url,
		);
		
		$vars = array_merge(parent::getVars($render), $add_vars);
		
		return $vars;
	}
	
	public function getFileUploadForm(){
		
		$file = new File();
		
		$form = $this->createFormBuilder($file)
			->add('file')
			->getForm();
			
		return $form;
	}
	
	public function uploadAction()
	{	
		$form = $this->getFileUploadForm();
		
		$request = $this->getRequest();
		
		$file_repo = $this->getRepository('BRSFileBundle:File');
		
		$values = $file_repo->hanldeUploadRequest($request, $form);
	
		return $this->jsonResponse($values);
	}

}
	