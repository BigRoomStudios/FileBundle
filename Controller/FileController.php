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
 * FileController handles file upload posts
 * @Route("")
 */
class FileController extends WidgetController
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
	
				$values = array(
					'status' => 'success',
					'file' => $file,
				);
				
				return $this->jsonResponse($values);
				
			}else{
				
				//$errors = $form->getErrors();
				$errors = $this->getErrorMessages($form);
				
				
				$values = array(
					'status' => 'fail',
					'errors' => $errors,
				);
				
				return $this->jsonResponse($values);
			}
		}
		
		$values = array(
			'status' => 'fail',
		);
	
		return $this->jsonResponse($values);
	}
	
	
	
	/**
	 * handle a file download 
	 *
	 * @Route("/file/download/{id}", requirements={"id" = "\d+"})
	 * 
	 */
	public function downloadAction($id)
	{
		
		$file = $this->getRepository('BRSFileBundle:File')->findOneById($id);
		
		$real_path = $file->getAbsolutePath();
		
		//die($real_path);
		
		header("X-Sendfile: $real_path");
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $file->name . '"');
		header('Content-Transfer-Encoding: binary');
		exit;
		
		/*
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $file->name . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $file->size);
		ob_clean();
		flush();
		@readfile($real_path);
		exit;
		*/ 	
	}
	
	/**
	 * handle a file display request
	 *
	 * @Route("/file/{id}/{name}", requirements={"id" = "\d+", "name" = ".*"})
	 * 
	 */
	public function fileAction($id, $name = null)
	{
		
		$file = $this->getRepository('BRSFileBundle:File')->findOneById($id);
		
		$real_path = $file->getAbsolutePath();
	
		header("X-Sendfile: $real_path");
		header('Content-Type: ' . $file->type);
		header('Content-Disposition: filename="' . $file->name . '"');
		exit;
		
		/*
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $file->type);
		//header('Content-Disposition: attachment; filename="' . $file->name . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $file->size);
		ob_clean();
		flush();
		@readfile($real_path);
		exit;
		*/
	}
	
	
}