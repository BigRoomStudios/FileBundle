<?php

namespace BRS\FileBundle\Controller;

use BRS\CoreBundle\Core\WidgetController;
use BRS\CoreBundle\Core\Utility as BRS;

use Symfony\Component\HttpFoundation\Request;
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
		
		$request = $this->getRequest();
		
		$file_repo = $this->getRepository('BRSFileBundle:File');
		
		$values = $file_repo->hanldeUploadRequest($request, $form);
	
		return $this->jsonResponse($values);
	}
	
	/**
	 * handle a file upload post
	 *
	 * @Route("/file/upload/{directory}", name="file_upload")
	 * 
	 */
	public function fileUploadAction(File $directory) {
		
		$file = new File();
		
		$form = $this->createFormBuilder($file)
			->add('file')
			->getForm();
		
		$request = $this->getRequest();
		
		$file_repo = $this->getRepository('BRSFileBundle:File');
		
		$values = $file_repo->hanldeUploadRequest($request, $form);
	
		return $this->jsonResponse($values);
	}
	
	
	/**
	 * handle a file download 
	 *
	 * @Route("/file/download/{id}", requirements={"id" = "\d+"}, name="brs_file_download")
	 * 
	 */
	public function downloadAction($id)
	{
		$file = $this->getRepository('BRSFileBundle:File')->findOneById($id);
		
		if(is_object($file)){
		
			$real_path = $file->getAbsolutePath();
			
			header("X-Sendfile: $real_path");
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $file->name . '"');
			header('Content-Transfer-Encoding: binary');
			exit;
			
		}else{
			
			$this->fileNotFound();	
		}
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
		
		if(is_object($file)){
			
			$original_path = $file->getAbsolutePath();
			
			$this->sendFile($original_path,  $file->type, $file->name);
			
		}else{
			
			$this->fileNotFound();
		}
	}
	
	/**
	 * handle a file display request
	 *
	 * @Route("/file/delete/{id}", requirements={"id" = "\d+"}, name="brs_file_delete")
	 * 
	 */
	public function deleteAction($id)
	{
		
		$file = $this->getRepository('BRSFileBundle:File')->findOneById($id);
		
		if(is_object($file)){
			
			//get the owner of the file
			$owner_id = $file->getOwnerId();
			
			if($owner_id) {
				
				//get the user id
				$user_id = $this->getUser()->getId();
				
				//if they don't match
				if($user_id != $owner_id) {
					
					//fuck no you can't delete this!
					$response = array(
						'success' => false,
						'message' => "You do not have permission to delete this!",
					);
					
					$response = new Response(json_encode($response));
					$response->headers->set('Content-Type', 'application/json');
					
					return $response;
					
				}
				
			}
			
			$em = $this->getDoctrine()->getManager();
			
			$em->remove($file);
			$em->flush();
			
			//generate an error message
			$response = array(
				'success' => true,
			);
			
		}
		else{
			
			//generate an error message
			$response = array(
				'success' => false,
				'message' => "Could not find record",
			);
			
		}
		
		$response = new Response(json_encode($response));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
		
	}


	/**
	 * get a resized image for a given file
	 * params is a set of image modifications separated with slashes like so:
	 * image/163/502/502/crop/blur:3/quality:75/asdasda.jpg
	 * 
	 * @Route("/image/{id}/{width}/{height}/{params}", requirements={"id" = "\d+", "width" = "\d+", "height" = "\d+", "params" = ".*"})
	 * 
	 */
	public function imageAction($id, $width, $height, $params = null)
	{
		$type =	'image/jpeg';
			
		$file = new File();
		
		$param_parts = explode('/', $params);
		
		$name = array_pop($param_parts);
		
		$pass_params = array();
		
		foreach($param_parts as $part){
			
			$part_parts = explode(':', $part);
			
			$pass_params[$part_parts[0]] = (isset($part_parts[1])) ? $part_parts[1] : true;
		}
		
		$file->id = $id;
		
		$cache_path = $file->getResizedCachePath($width, $height, $pass_params);
		
		if(file_exists($cache_path)){
				
			$this->sendFile($cache_path, $type, $name);
		
		}else{
				
			$file = $this->getRepository('BRSFileBundle:File')->findOneById($id);
			
			if(is_object($file)){
			
				$cache_path = $file->getResizedImage($width, $height, $pass_params);
				
				$this->sendFile($cache_path, $type, $name);
				
			}else{
				
				$this->fileNotFound();
			}
		}
	}

	
	protected function fileNotFound(){
		
		throw $this->createNotFoundException("This is not the file you're looking for...");
	}
	
	protected function sendFile($path, $type, $name){
		
		if((!$path) || (!file_exists($path))){
			
			$this->fileNotFound();
			
		}else{
			
			header("X-Sendfile: $path");
			header("Content-Type: $type");
			header('Content-Disposition: filename="' . $name . '"');
			header('Content-Transfer-Encoding: binary');
			exit;	
		}
	}
	
}