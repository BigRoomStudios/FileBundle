<?php

namespace BRS\FileBundle\Widget;

use BRS\CoreBundle\Core\Widget\ListWidget;
use BRS\FileBundle\Entity\File;
use BRS\CoreBundle\Core\Utility as BRS;

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
			
			'tree' => array(
				'type' => 'file.tree',
				'width' => 120,
				'nonentity' => true,
			),
			
			'thumb' => array(
				'type' => 'thumbnail',
				'width' => 40,
				'nonentity' => true,
				'file_id_field' => 'id',
			),
			
			'name' => array(
				'type' => 'file.link',
			),
			
			'type' => array(
				'type' => 'text',
			),
			
			'title' => array(
				'type' => 'text',
			),
			
			'is_dir' => array(
				'type' => 'hidden',
			),
			
			'parent_id' => array(
				'type' => 'hidden',
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
		
		if($vars['entity_id']){
			
			$em = $this->getEntityManager();

			$file_repo = $this->getRepository('BRSFileBundle:File');
			
			$file = $em->getReference('\BRS\FileBundle\Entity\File', $vars['entity_id']);
			
			//$file = $file_repo->findOneById($vars['entity_id']);
			
			$path = $file_repo->getPath($file);
			
			$vars['path'] = $path;
			
			//BRS::die_pre(count($path));
			
		}
		
		return $vars;
	}
	
	public function getById($id){
		
		parent::getById($id);
		
		$this->setFilters(
			array(
				array(
					'filter' => 'f.parent_id = :id',
					'params' => array('id' => $id),
				)
			)
		);
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
	
	public function newFolderAction()
	{
		$request = $this->getRequest();	
			
		if($request->getMethod() == 'GET'){
			
			$folder_name = $request->get('folder_name');
			
			$dir_id = $request->get('dir_id');	
			
			$file = new File();
			
			$em = $this->getEntityManager();
			
			if($dir_id){
				
				$parent = $em->getReference('\BRS\FileBundle\Entity\File', $dir_id);
				
				$file->setParent($parent);
			}
			
			$file->setIsDir(true);
			
			$file->setName($folder_name);
				
			$em->persist($file);
			
			$em->flush();
			
			$values = array('name' => $folder_name, 'id' => $file->getId(), 'is_dir' => true);
		}
		
		return $this->jsonResponse($values);
	}
	

}
	