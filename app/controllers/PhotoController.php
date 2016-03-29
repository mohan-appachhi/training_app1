<?php 

use \Phalcon\Mvc\Controller;

class PhotoController extends Controller
{
	public function PhotoAction()
	{
		$this->logger->log("[filckr] photoAction() : START ");
		//$this->view->disable();
		$name_id = $this->dispatcher->getParam("name_id");
		$name_id_de = urldecode($name_id);
		$name = explode('-', $name_id_de);
		$this->tag->setTitle("Details Of Photo");
		$this->logger->log("'[Flickr]photoAction():action found'");
		$get_details = $this->session->get('details');
		foreach ($get_details as $key => $value) 
		{	
			if( $name[1] == $value['photo_id'] )
			{
				$this->view->setVar('details',[
					$value['title'],
					$value['owner_name'],
					$value['photo_id'],
					$value['date_taken'],
					$value['date_upload'],
					$value['last_update'],
					$value['views'],
					$value['tags'],
					$value['media'],
					$value['preview']
					]);
				var_dump("dispatcher :".$name_id);
				var_dump($name);
			}
		}
		$this->logger->log('[filckr] photoAction() : END');
	}
}