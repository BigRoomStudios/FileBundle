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
			
			'name' => array(
				'type' => 'text',
				'required' => false,
			),
			
			'title' => array(
				'type' => 'text',
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
	
}