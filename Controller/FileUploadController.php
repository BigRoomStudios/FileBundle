<?php

namespace BRS\FileBundle\Controller;

use BRS\CoreBundle\Core\WidgetController;
use BRS\CoreBundle\Core\Utility as BRS;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use BRS\FileBundle\Entity\File;

/**
 * FileUploadController handles file upload posts
 * @Route("")
 */
class FileUploadController extends WidgetController
{
	/**
	 * handle a file upload post
	 *
	 * @Route("/file/upload")
	 * 
	 */
	public function uploadAction()
	{
		
		$file = new File();
	    $form = $this->createFormBuilder($file)
	        ->add('file')
	        ->getForm();
	
	    if ($this->getRequest()->getMethod() === 'POST') {
	        $form->bindRequest($this->getRequest());
	        if ($form->isValid()) {
	            $em = $this->getDoctrine()->getEntityManager();
	
	            $em->persist($file);
	            $em->flush();
	
	            return $this->jsonResponse((array)$file);
	        }
	    }
		
		$status = 'success';
		
		$values = array(
			'status' => $status,
		);
	
		return $this->jsonResponse($values);
	}
}