<?php

namespace BRS\FileBundle\Repository;

use BRS\FileBundle\Entity\File;
use BRS\CoreBundle\Core\Utility;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * FileRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FileRepository extends NestedTreeRepository
{
		
	public function getRootByName($name)
	{
		$files = $this->getEntityManager()
			->createQuery("SELECT f FROM BRSFileBundle:File f WHERE f.name = :name AND f.tree_level = 0")
			->setParameter('name', $name)
			->setMaxResults(1)
			->getResult();
		
		
		//Utility::die_pre($files);
		
		if($files){
			
			return $files[0];
		}
	}	
	
	public function hanldeUploadRequest(\Symfony\Component\HttpFoundation\Request $request, $form){
		
		if ($request->getMethod() === 'POST') {
			
			
			
			
			//strip everything but the csrf token from the request and just handle the file
			
			$form_post = $request->get('form');
			
			$params = array('form' => array('_token' => $form_post['_token']));
			
			$new_request = Request::create($request->getUri(), 'POST', $params, $_COOKIE, $_FILES, $_SERVER, $request->getContent());
			
			
			$form->bindRequest($new_request);
			
			if ($form->isValid()) {
				
				$file = $form->getData();
				
				$em = $this->getEntityManager();
	
				$parent_id = $form_post['parent_id'];
				
				if($parent_id){
					
					$parent = $em->getReference('\BRS\FileBundle\Entity\File', $parent_id);
				
					$file->setParent($parent);
				}
				
				$em->persist($file);
				
				$em->flush();
	
				$values = array(
					'status' => 'success',
					'file' => $file,
				);
				
				return $values;
				
			}else{
				
				$errors = Utility::get_form_errors($form);
				
				$values = array(
					'status' => 'fail',
					'errors' => $errors,
				);
				
				return $values;
			}
		}
		
		$values = array(
			'status' => 'fail',
		);
		
		return $values;
	}
}