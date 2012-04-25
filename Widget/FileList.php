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
		
		$file = new File();
	    $form = $this->createFormBuilder($file)
	        ->add('file')
	        ->getForm();
		
		$add_vars = array(
			'form' => $form->createView(),
		);
		
		$vars = array_merge(parent::getVars($render), $add_vars);
		
		return $vars;
	}
}
	