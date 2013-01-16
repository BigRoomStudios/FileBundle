<?php

namespace BRS\FileBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use BRS\CoreBundle\Core\Widget\ListWidget;
use BRS\CoreBundle\Core\Widget\EditFormWidget;
use BRS\CoreBundle\Core\Widget\PanelWidget;
use BRS\AdminBundle\Controller\AdminController;
use BRS\FileBundle\Entity\File;
use BRS\FileBundle\Widget\FileList;

/**
 * File admin controller.
 *
 * @Route("/admin/files")
 */
class FileAdminController extends AdminController
{
		
	protected function setup()
	{
		parent::setup();
				
		$this->setRouteName('brs_file_fileadmin');
		$this->setEntityName('BRSFileBundle:File');
		$this->setEntity(new File());
		
		$list_widget = new FileList();
		$this->addWidget($list_widget, 'list_files');
	
		$edit_fields = array(
			
			array(
			
				'type' => 'group',
				'fields' => array(
			
					'name' => array(
						'type' => 'text',
						'required' => false,
						'attr' => array(
							'class' => 'extra-large'
						)
					),
				),
			),
			
			array(
			
				'type' => 'group',
				'fields' => array(
			
					'title' => array(
						'type' => 'text',
						'attr' => array(
							'class' => 'extra-large'
						)
					),
				),
			),
			
			array(
			
				'type' => 'group',
				'fields' => array(
			
					'description' => array(
						'type' => 'textarea',
						'attr' => array(
							'class' => 'extra-large'
						)
					),
				),
			),
		);
		
		$edit_widget = new EditFormWidget();
		$edit_widget->setFields($edit_fields);
		$edit_widget->setSuccessRoute('brs_file_fileadmin_edit');
		$this->addWidget($edit_widget, 'edit_file');
		
		
		$this->addView('index', $list_widget);
		$this->addView('new', $edit_widget);
		$this->addView('edit', $edit_widget);
	}
	
	/**
	 * Lists files for the root directory
	 *
	 * @Route("/")
	 * @Template("BRSAdminBundle:Admin:default.html.twig")
	 */
	public function indexAction()
	{
		//die('test');	
			
		$view = $this->getView('index');
		
		$view->setFilters(
			array(
				array(
					'filter' => 'f.parent_id is null',
				)
			)
		);
		
		$view->handleRequest();
		
		$values = array(
		
			'view' => $view->render(),
		);
		
		if($this->isAjax()){
			
			return $this->jsonResponse($values);
		}
		
		$values = array_merge( $this->getViewValues(), $values );
		
		return $values;
	}
	
	
	/**
	 * Lists files for a specified directory
	 *
	 * @Route("/{id}/list")
	 * @Template("BRSAdminBundle:Admin:default.html.twig")
	 */
	public function listAction($id)
	{
		//die('test');	
			
		$view = $this->getView('index');
		
		$view->getById($id);
		
		$view->handleRequest();
		
		$values = array(
		
			'view' => $view->render(),
		);
		
		if($this->isAjax()){
			
			return $this->jsonResponse($values);
		}
		
		$values = array_merge( $this->getViewValues(), $values );
		
		return $values;
	}
	
}