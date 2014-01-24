<?php

namespace Sandi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Sandi\Model\Model;
use Sandi\Model\ModelFile;
use Sandi\Form\UploadModelForm;
use Sandi\Form\ModelImageFileUploadForm;
use Sandi\Form\ModelProjectFileUploadForm;
use Zend\Filter\Null;
use Zend\Validator\File\Size;

use Zend\Filter\Compress;

use Sandi\src\Sandi\Util\TestClass;

class SandiController extends AbstractActionController {

	protected $modelTable;
	protected $modelFileTable;
	protected $modelFlagMappingTable;
	protected $categoryTable;

	
	public function indexAction() {
		
// 		$filter     = new Compress(array(
// 		    'adapter' => 'Zip',
// 		    'options' => array(
// 		        'archive' => 'd:/111.zip'
// 		    ),
// 		));
// 		$compressed = $filter->filter('d:/qqq.txt');
		
		
		
		$category_id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		if (! $category_id) {
			$category_id = 0;
		}
		
		$paginated = true;
		if ($paginated) {
			
			// create a new Select object
			$select = new Select ();
			$select->from ( 't_model' );
			$select->join ( 't_model_category_mapping', 't_model.model_id = t_model_category_mapping.model_id', array (
					'category_id' 
			) );
			$select->where ( array (
					'category_id' => $category_id 
			) );
			
			// create a new result set based on the Model entity
			$resultSetPrototype = new ResultSet ();
			$resultSetPrototype->setArrayObjectPrototype ( new Model () );
			// create a new pagination adapter object
			$paginatorAdapter = new DbSelect ( 
					// our configured select object
					$select, 
					// the adapter to run it against
					$this->getAdapter (), 
					// the result set to hydrate
					$resultSetPrototype );
			$paginator = new Paginator ( $paginatorAdapter );
			
			// set the current page to what has been passed in query string, or to 1 if none set
			$paginator->setCurrentPageNumber ( ( int ) $this->params ()->fromQuery ( 'page', 1 ) );
			// set the number of items per page to 10
			$paginator->setItemCountPerPage ( 20 );
		} else {
			$paginator = $this->getModelByCategory ( $category_id );
		}
		
		return new ViewModel ( array ('paginator' => $paginator ) );
	}
	
	
	public function editAction() {
		$model_id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		if (! $model_id) {
			return $this->redirect ()->toRoute ( 'sandi', array (
					'action' => 'uploadModel' 
			) );
		}
		
		// Get the model details with the specified id. An exception is thrown
		// if it cannot be found, in which case go to the index page.
		try {
			$model = $this->getModelTable ()->getModel ( $model_id );
		} catch ( \Exception $ex ) {
			return $this->redirect ()->toRoute ( 'sandi', array (
					'action' => 'index' 
			) );
		}
		
		$form = new UploadModelForm ();
		$form->bind ( $model );
		
		// add category
		$form->categoryData = $this->getModelCategoryTable ()->fetchAll ();
		$form->addCategory ();
		
		$form->get ( 'submit' )->setAttribute ( 'value', 'Edit' );
		
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$form->setInputFilter ( $model->getInputFilter () );
			// Make certain to merge the files info!
			$post = array_merge_recursive ( $request->getPost ()->toArray (), $request->getFiles ()->toArray () );
			
			$form->setData ( $post );
			
			if ($form->isValid ()) {
				
				$data = $form->getData ( FormInterface::VALUES_AS_ARRAY );
				
				$adapter = $this->getAdapter ();
				$sql = new Sql ( $adapter );
				$update = $sql->update ();
				
				// 1. update model table
				$profile = $data ["profile"];
				echo $profile;
				$update->table ( 't_model' );
				$update->set ( array (
						'profile' => $profile 
				) );
				$update->where ( array (
						'model_id' => $model_id 
				) );
				
				$updateString = $sql->getSqlStringForSqlObject ( $update );
				$results = $adapter->query ( $updateString, $adapter::QUERY_MODE_EXECUTE );
				
				// 2. update grant table
				$offer = $data ["offer"];
				$update->table ( 't_grant' );
				$update->set ( array (
						'grant_bitmap' => $offer 
				) );
				$update->where ( array (
						'model_id' => $model_id 
				) );
				
				$updateString = $sql->getSqlStringForSqlObject ( $update );
				echo $updateString;
				$results = $adapter->query ( $updateString, $adapter::QUERY_MODE_EXECUTE );
				
				// 3. update model-category-mapping table
				$categoryID = $data ["category"];
				$update->table ( 't_model_category_mapping' );
				$update->set ( array (
						'category_id' => $data ["category"] 
				) );
				$update->where ( array (
						'model_id' => $model_id 
				) );
				
				$updateString = $sql->getSqlStringForSqlObject ( $update );
				echo $updateString;
				$results = $adapter->query ( $updateString, $adapter::QUERY_MODE_EXECUTE );
				
				// 3. update offer table
				$offer = $data ["offer"];
				$price = $data ["price"];
				$update->table ( 't_offer' );
				$update->set ( array (
						'contents' => $price,
						'grant_bitmap' => $offer 
				) );
				$update->where ( array (
						'model_id' => $model_id 
				) );
				
				$updateString = $sql->getSqlStringForSqlObject ( $update );
				echo $updateString;
				$results = $adapter->query ( $updateString, $adapter::QUERY_MODE_EXECUTE );
				
				return $this->redirect ()->toUrl ( '/sandi/inform/2' );
			}
		}
		
		return array (
				'id' => $model_id,
				'form' => $form 
		);
	}
	
	
	public function uploadModelAction() {
		$sessionUser = new Container ( 'user' );
		if ($sessionUser->user_id == NULL) {
			return $this->redirect ()->toUrl ( '/user/login' );
		}
		
		$form = new UploadModelForm ( 'upload-form' );
		$form->categoryData = $this->getModelCategoryTable()->fetchAll();
		$form->addCategory ();
		
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			// Make certain to merge the files info!
			$post = array_merge_recursive ( $request->getPost ()->toArray (), $request->getFiles ()->toArray () );
			
			$form->setData ( $post );
			if ($form->isValid ()) {
				
				$data = $form->getData ();
				
				// 1. save model data
				$model = new Model ();
				$model->exchangeArray ( $data );
				$model->owner_id = $sessionUser->user_id;
				$model->designer_id = $sessionUser->user_id;
				
				$this->getModelTable ()->SaveModel ( $model );
				
				$lastInsertModelID = $this->getModelTable ()->lastInsertValue;
				$price = $data ["price"];
				$offer = $data ["offer"];
				
				// 2. save offer data
				if ($price != NULL) {
					$adapter = $this->getAdapter ();
					$sql = new Sql ( $adapter );
					$insert = $sql->insert ();
					$insert->into ( 't_offer' );
					$insert->values ( array (
							'model_id' => $lastInsertModelID,
							'contents' => $price,
							'grant_bitmap' => $offer 
					) );
					
					$insertString = $sql->getSqlStringForSqlObject ( $insert );
					$results = $adapter->query ( $insertString, $adapter::QUERY_MODE_EXECUTE );
				}
				
				// 3. save model-category-mapping
				$categoryID = $data ["category"];
				
				$adapter = $this->getAdapter ();
				$sql = new Sql ( $adapter );
				$insert = $sql->insert ();
				$insert->into ( 't_model_category_mapping' );
				$insert->values ( array (
						'model_id' => $lastInsertModelID,
						'category_id' => $categoryID 
				) );
				
				$insertString = $sql->getSqlStringForSqlObject ( $insert );
				$results = $adapter->query ( $insertString, $adapter::QUERY_MODE_EXECUTE );
				
				// 4. save grant data
				$sql = new Sql ( $adapter );
				$insert = $sql->insert ();
				$insert->into ( 't_grant' );
				$insert->values ( array (
						'model_id' => $lastInsertModelID,
						'grant_bitmap' => $offer + 2 + 1 
				) );
				
				$insertString = $sql->getSqlStringForSqlObject ( $insert );
				$results = $adapter->query ( $insertString, $adapter::QUERY_MODE_EXECUTE );
				
				return $this->redirect ()->toRoute ( 'sandi', array (
						'action' => 'upload-Model-Image-File',
						'id' => $lastInsertModelID 
				) );
			}
		}
		
		return array (
				'form' => $form 
		);
	}
	
	// 2013-12-21
	public function uploadModelImageFileAction() {
		$sessionUser = new Container ( 'user' );
		if ($sessionUser->user_id == NULL) {
			return $this->redirect ()->toUrl ( '/user/login' );
		}
		
		$model_id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		if (! $model_id) {
			return $this->redirect ()->toRoute ( 'sandi', array (
					'action' => 'uploadModel' 
			)
			 );
		}
		
		$form = new ModelImageFileUploadForm ( 'Image-File-Upload' );
		
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$post = $request->getFiles ()->toArray ();
			$form->setData ( $post );
			if ($form->isValid ()) {
				$data = $form->getData ();
				
				$data_imgFile = $data ["image-file"];
				$name = $data_imgFile ["name"];
				$type = $data_imgFile ["type"];
				$error = $data_imgFile ["error"];
				$tmp_name = $data_imgFile ["tmp_name"];
				
				switch ($type) {
					case 'image/pjpeg' :
						$ok = 1;
						break;
					case 'image/jpeg' :
						$ok = 1;
						break;
					case 'image/gif' :
						$ok = 1;
						break;
					case 'image/png' :
						$ok = 1;
						break;
				}
				
				if ($ok != 1 || $error != 0) {
					return;
				}
				
				$uploadPath = $this->getFileUploadLocation ();
				
				$uploadPath = $uploadPath . '/' . $model_id;
				if (! file_exists ( $uploadPath )) {
					mkdir ( $uploadPath, 0777 );
				}
				
				$uploadPath = $uploadPath . '/img';
				if (! file_exists ( $uploadPath )) {
					mkdir ( $uploadPath, 0777 );
				}
				
				// Save Uploaded file
				$name_2312 = iconv("UTF-8","GB2312", $name);   //字符转码
				//$adapter = new \Zend\File\Transfer\Adapter\Http();
				//$adapter->setDestination ( $uploadPath );
				//if ($adapter->receive ( $name )) {
				if (move_uploaded_file($tmp_name, "$uploadPath/$name_2312"))
				{
					
					// generate thumbnail image
					$tn_img = $this->generateThumbnail ( $name_2312, $model_id );
						
					// save model file into db
					$modelFile = new ModelFile ();
					$modelFile->model_id = $model_id;
					$modelFile->file_name = "tn_" . $name;
					$modelFile->file_type = 1;
					$this->getModelFileTable ()->saveModelFile ( $modelFile );
										
					return $this->redirect ()->toUrl ( "/sandi/upload-model-image-file/$model_id" );
				}
			}
		}
		
		$modelImages = $this->getModelFileTable ()->getModelFile ( $model_id, 1 );
		return array (
				'form' => $form,
				'model_id' => $model_id,
				'modelImageFiles' => $modelImages 
		);
	}
	
	// 2013-12-22
	public function uploadModelProjectFileAction() {
		$sessionUser = new Container ( 'user' );
		if ($sessionUser->user_id == NULL) {
			return $this->redirect ()->toUrl ( '/user/login' );
		}
		
		$model_id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		if (! $model_id) {
			return $this->redirect ()->toRoute ( 'sandi', array (
					'action' => 'uploadModelFile' 
			) );
		}
		
		$form = new ModelProjectFileUploadForm ( 'Model-Project-File-Upload' );
		
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$post = $request->getFiles ()->toArray ();
			$form->setData ( $post );
			if ($form->isValid ()) {
				$data = $form->getData ();
				
				$data_imgFile = $data ["model-project-file"];
				$name = $data_imgFile ["name"];
				$type = $data_imgFile ["type"];
				$error = $data_imgFile ["error"];
				$tmp_name = $data_imgFile ["tmp_name"];
				
				if ($error != 0) {
					return;
				}
				
				$uploadPath = $this->getFileUploadLocation ();
				
				$uploadPath = $uploadPath . '\\' . $model_id;
				if (! file_exists ( $uploadPath )) {
					mkdir ( $uploadPath, 0777 );
				}
				
				$uploadPath = $uploadPath . '\\project';
				if (! file_exists ( $uploadPath )) {
					mkdir ( $uploadPath, 0777 );
				}
				
				// Save Uploaded file
				$name_2312 = iconv("UTF-8","GB2312", $name);   //字符转码
				if (move_uploaded_file($tmp_name, "$uploadPath/$name_2312"))
				{
					return $this->redirect ()->toUrl ( "/sandi/upload-model-project-file/$model_id" );
				}
			}
		}
		
		return array (
				'form' => $form,
				'model_id' => $model_id 
		)
		;
	}
	
	// 2013-12-16
	public function generateThumbnail($imageFileName, $model_id) {
		$path = $this->getFileUploadLocation ();
		$sourceImageFileName = $path . '/' . $model_id . '/img/' . $imageFileName;
		$thumbnailFileName = 'tn_' . $imageFileName;
		$imageThumb = $this->getServiceLocator ()->get ( 'WebinoImageThumb' );
		$thumb = $imageThumb->create ( $sourceImageFileName, $options = array () );
		$thumb->resize ( 75, 75 );
		$thumb->save ( $path . '/' . $model_id . '/img/' . $thumbnailFileName );
		return $thumbnailFileName;
	}
	
	// 2013-12-15
	public function modelManageAction() {
		$model_id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		if (! $model_id) {
			return $this->redirect ()->toRoute ( 'sandi', array (
					'action' => 'uploadModel' 
			) );
		}
		
		$sessionUser = new Container ( 'user' );
		if ($sessionUser->user_id == NULL) {
			return $this->redirect ()->toUrl ( '/user/login' );
		}
		
		$modelTable = $this->getServiceLocator ()->get ( 'Sandi\Model\ModelTable' );
		$model = $modelTable->getModel ( $model_id );
		
		if ($sessionUser->user_id != $model->owner_id) {
			return $this->redirect ()->toUrl ( '/user/login' );
		}
		
		return new ViewModel ( array (
				'model' => $model 
				)

		 );
		
	}
	
	
	/*
	 * this action used to output the contents if image to web image control tag
	 * 
	 * */
	public function showImageAction() {

		$id 			= $this->params()->fromRoute('subaction');	//image id
		$modelId 		= $this->params()->fromRoute('id');			//model id
		
		$modelImageRS 	= $this->getModelFileTable ()->getModelFileById($id);
		$row 			= $modelImageRS->current();

		$imageName 		= $row->file_name;
		$imageName = iconv("UTF-8", "GB2312", $imageName);		//need converting the encode of image file's name from charset 
																//utf-8 to gb2312, otherwise it will be corrupted
																//character and cannot be returned to web client.
		
		// Fetch Configuration from Module Config
		$filePath = $this->getFileUploadLocation ();
		$imagePath = "$filePath/$modelId/img/$imageName";
		
		$file = file_get_contents($imagePath);
		
		// Directly return the Response
		$response = $this->getEvent()->getResponse();
		$response->getHeaders()->addHeaders ( array (
				'Content-Type' => 'application/octet-stream',
				'Content-Disposition' => "attachment;filename=$imageName" 
		) );
		$response->setContent ( $file );
		return $response;
	}
	
	public function showPrincipalModelImageAction()
	{
		$modelId	= $this->params()->fromRoute('id');			//model id
		$imgArray	= $this->getPrincipalModelmageFiles($modelId);
		
		// Fetch Configuration from Module Config
		$filePath = $this->getFileUploadLocation ();
		
		if(count($imgArray) > 0)
		{
			$imageName = $imgArray[0];
			$imagePath = "$filePath/$modelId/img/$imageName";
		}
		else 
		{
			$imageName = "pro.jpg";
			$imagePath = "$filePath/$imageName";
		}
		
		
		
		$file = file_get_contents($imagePath);
		
		// Directly return the Response
		$response = $this->getEvent()->getResponse();
		$response->getHeaders()->addHeaders ( array (
				'Content-Type' => 'application/octet-stream',
				'Content-Disposition' => "attachment;filename=$imageName"
		) );
		$response->setContent ( $file );
		return $response;
	}

	
	public function informAction() {
		$id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		return new ViewModel ( array (
				'informID' => $id 
		) );
	}
	
	
	public function modelDetailAction() {
		
		$model_id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		if (! $model_id) {
			return $this->redirect ()->toRoute ( 'home', array (
					'action' => 'index' 
			) );
		}
		
		// Get the model details with the specified id. An exception is thrown
		// if it cannot be found, in which case go to the index page.
		try {
			$model = $this->getModel ( $model_id );
			$grant = $this->getGrant ( $model_id );
			$offer = $this->getOffer ( $model_id );
			
			//get if current user has bought this model's granted privilege 
			$bHasPurchased = false;
			
			$sessionUser = new Container ( 'user' );
			if ($sessionUser->user_id != NULL && $offer != NULL)
			{
				$customer_id = $sessionUser->user_id;
				$offer_id = $offer[0]["offer_id"];
				$purchase = $this->getPurchaseRecord($customer_id, $offer_id);
				if($purchase != NULL)
				{
					$bHasPurchased = true;
				}
			}
			
			return new ViewModel ( array (
					'model' => $model,
					'grant' => $grant,
			 		'offer' => $offer,
					'hasPurchased' => $bHasPurchased
			) );
		} catch ( \Exception $ex ) {
			return $this->redirect ()->toRoute ( 'home', array (
					'action' => 'index' 
			) );
		}
	}
	
	public function purchaseModelAction()
	{
		$sessionUser = new Container ( 'user' );
		if ($sessionUser->user_id == NULL)
		{
			return $this->redirect ()->toRoute ( 'sandi', array (
					'action' => 'index',
					
					));
		}

		$offer_id 			= $this->params()->fromRoute('id');	
				
		try 
		{
				$adapter = $this->getAdapter ();
				$sql = new Sql ( $adapter );
				$insert = $sql->insert ();
				$insert->into ( 't_purchase');
				$insert->values ( array (
						'customer_id' => $sessionUser->user_id,
						'offer_id' => $offer_id,
						'status'   => 1
				) );
				
				$insertString = $sql->getSqlStringForSqlObject ( $insert );
				$results = $adapter->query ( $insertString, $adapter::QUERY_MODE_EXECUTE );				
		} 
		catch ( \Exception $ex ) 
		{
			//return $this->redirect ()->toRoute ( 'sandi', array (
			//		'action' => 'index'
			//) );
			echo $ex->__toString();
			
		}
		
		
	}
	
	public function downloadModelAction()
	{
		$sessionUser = new Container ( 'user' );
		if ($sessionUser->user_id == NULL)
		{
			return $this->redirect ()->toRoute ( 'sandi', array (
					'action' => 'index'
			));
		}
		$customer_id = $sessionUser->user_id;
		$model_id 			= $this->params()->fromRoute('id');
		
		$adapter = $this->getAdapter ();
		$sql = new Sql ( $adapter );
		$select = $sql->select ();
		$select->from ( 't_offer');
		$select->join('t_purchase',
		 't_offer.offer_id = t_purchase.offer_id',
		 array('customer_id', 'status'));
		
		$select->where ( array (
				'customer_id' => $customer_id,
				'model_id' => $model_id
		) );
		$selectString = $sql->getSqlStringForSqlObject ( $select );
		
		$results = $adapter->query ( $selectString, $adapter::QUERY_MODE_EXECUTE );
		 //$resultArray = $results->toArray();
		 if ($results->count() == 1)
		 {
		 	$row = $results->current();
		 	//if($row->status == 2)
		 	{
		 		$iRet = $this->archiveModel($customer_id, $row->model_id);
		 		
		 		if($iRet == 1)
		 		{
		 			$this->downloadModel($customer_id, $row->model_id);
		 		}
		 	}

		 }
		 else
		 {
				echo "没有获得该用户对该模型的购买记录，您无权下载！";
		 }

		
	}
	
	private function archiveModel($customer_id, $model_id)
	{
		$modelFilePath = $this->getFileUploadLocation();
		$modelFilePath = "$modelFilePath/$model_id/project";
		
		$dest =  $this->getFileDownloadLocation();
		if (! file_exists ( $dest )) 
		{
			mkdir ( $dest, 0777 );
		}

		$dest = "$dest/$customer_id";
		if (! file_exists ( $dest ))
		{
			mkdir ( $dest, 0777 );
		}
		
		$dest = "$dest/$model_id";
		if (! file_exists ( $dest ))
		{
			mkdir ( $dest, 0777 );
		}
		
		if (is_dir ( $modelFilePath )) 
		{
			
			$filter     = new Compress(array(
					'adapter' => 'Zip',
					'options' => array(
							'archive' => "$dest/$model_id.zip"
					),
			));
			
			$dh = opendir ( $modelFilePath );
			if ($dh) 
			{
				while ( ($file = readdir ( $dh )) !== false ) 
				{
					if ($file != '.' && $file != '..') 
					{
						
						$compressed = $filter->filter("$modelFilePath/$file");
						copy("$modelFilePath/$file", "$dest/$file");
					}
				}
				closedir ( $dh );
			}
			
		}
		else
		{
			echo "没有发现模型文件所在的目录，下载模型文件失败！";
			return 0;
		}
		
		return 1;
	}
	
	
	private function downloadModel($customer_id, $model_id)
	{
		$dest =  $this->getFileDownloadLocation();
		$zipPath = "$dest/$customer_id/$model_id";
// 		$file = "";
// 		$dp = opendir ( $zipPath );
// 		if ($dp)
// 		{
// 			while ( ($file = readdir ( $dp )) !== false )
// 			{
// 				if ($file == "$model_id.png")
// 				{
					
// 					break;
					

// // 					$fileName = "$zipPath/$file";
// // 					$response = new \Zend\Http\Response\Stream();
// // 					$response->setStream(fopen($fileName, 'r'));
// // 					$response->setStatusCode(200);	

// // 					$headers = new \Zend\Http\Headers();
// // 					$headers->addHeaderLine('Content-Type', 'whatever your content type is')
// // 					->addHeaderLine('Content-Disposition', 'attachment; filename="' . $file . '"')
// // 					->addHeaderLine('Content-Length', filesize($fileName));
					
// // 					$response->setHeaders($headers);
// // 					return $response;					
					
					
// 				}
// 			}
// 			closedir ( $dp );
// 		}
		$file = "$model_id.png";
		$fileContents = file_get_contents("$zipPath/$file");
		
		// Directly return the Response
		$response = $this->getEvent()->getResponse();
			
		$response->getHeaders()->addHeaders ( array (
				//'Content-Type' => 'application/octet-stream',
				//'Content-Type' => 'image/png',
				'Content-Disposition' => "attachment;filename=$file",
				'Content-Length' => strlen($fileContents),
				//'Content-Length', filesize("$zipPath/$file")
				//'Content-Transfer-Encoding' => 'Binary'
					
		) );
			
		$response->setContent ( $fileContents );
		//echo $response;
		return $response;

	}
	
	
	private function getGrant($model_id) {
		$adapter = $this->getAdapter ();
		$sql = new Sql ( $adapter );
		$select = $sql->select ();
		$select->from ( 't_grant' );
		// $select->join('t_offer',
		// 't_model.model_id = t_offer.model_id',
		// array('contents'));
		$select->where ( array (
				'model_id' => $model_id 
		) );
		$selectString = $sql->getSqlStringForSqlObject ( $select );
		
		$results = $adapter->query ( $selectString, $adapter::QUERY_MODE_EXECUTE );
		// $resultArray = $results->toArray();
		$rows = array_values ( iterator_to_array ( $results ) );
		return $rows;
	}
	
	
	private function getModel($model_id) {
		$adapter = $this->getAdapter ();
		$sql = new Sql ( $adapter );
		$select = $sql->select ();
		$select->from ( 't_model' );
		$select->where ( array (
				'model_id' => $model_id 
		) );
		$selectString = $sql->getSqlStringForSqlObject ( $select );
		$results = $adapter->query ( $selectString, $adapter::QUERY_MODE_EXECUTE );
		$rows = array_values ( iterator_to_array ( $results ) );
		return $rows;
	}
	
	private function getOffer($model_id) {
		$adapter = $this->getAdapter ();
		$sql = new Sql ( $adapter );
		$select = $sql->select ();
		$select->from ( 't_offer' );
		$select->where ( array (
				'model_id' => $model_id
		) );
		$selectString = $sql->getSqlStringForSqlObject ( $select );
		$results = $adapter->query ( $selectString, $adapter::QUERY_MODE_EXECUTE );
		$rows = array_values ( iterator_to_array ( $results ) );
		return $rows;
	}

	public function getPurchaseRecord($customer_id, $offer_id) 
	{
		$adapter = $this->getAdapter ();
		$sql = new Sql ( $adapter );
		$select = $sql->select ();
		$select->from ( 't_purchase' );
		$select->where ( array (
				'customer_id' => $customer_id,
				'offer_id' => $offer_id
		) );
		$selectString = $sql->getSqlStringForSqlObject ( $select );
		$results = $adapter->query ( $selectString, $adapter::QUERY_MODE_EXECUTE );
		$rows = array_values ( iterator_to_array ( $results ) );
		return $rows;
	}	
	
	
	private function getModelByCategory($category_id) {
		$adapter = $this->getAdapter ();
		$sql = new Sql ( $adapter );
		$select = $sql->select ();
		$select->from ( 't_model' );
		$select->join ( 't_model_category_mapping', 't_model.model_id = t_model_category_mapping.model_id', array (
				'model_id' 
		) );
		$select->where ( array (
				'category_id' => $category_id 
		) );
		$selectString = $sql->getSqlStringForSqlObject ( $select );
		$results = $adapter->query ( $selectString, $adapter::QUERY_MODE_EXECUTE );
		$rows = array_values ( iterator_to_array ( $results ) );
		return $rows;
	}
	
	
	public function getModelTable() {
		if (! $this->modelTable) {
			$sm = $this->getServiceLocator ();
			$this->modelTable = $sm->get ( 'Sandi\Model\ModelTable' );
		}
		return $this->modelTable;
	}
	
	
	public function getModelFlagMappingTable() {
		if (! $this->modelFlagMappingTable) {
			$sm = $this->getServiceLocator ();
			$this->modelFlagMappingTable = $sm->get ( 'Sandi\Model\ModelFlagMappingTable' );
		}
		return $this->modelFlagMappingTable;
	}
	
	
	public function getModelFileTable() {
		if (! $this->modelFileTable) {
			$sm = $this->getServiceLocator ();
			$this->modelFileTable = $sm->get ( 'Sandi\Model\ModelFileTable' );
		}
		return $this->modelFileTable;
	}
	
	public function getModelCategoryTable()
	{
		if (! $this->categoryTable) {
			$sm = $this->getServiceLocator ();
			$this->categoryTable = $sm->get ( 'Sandi\Model\ModelCategoryTable' );
		}
		return $this->categoryTable;
	}
	
	
	public function getAdapter()
	{
		$adapter = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Adapter' );
		return $adapter;
	}
	
	public function getFileUploadLocation()
	{
		// Fetch Configuration from Module Config
		$config = $this->getServiceLocator ()->get ( 'config' );
		return $config ['module_config'] ['upload_location'];
	}

	public function getFileDownloadLocation()
	{
		$config = $this->getServiceLocator ()->get ( 'config' );
		return $config ['module_config'] ['download_location'];
	}
	
	
	private function getModelmageFiles($model_id)
	{
		$path = $this->getFileUploadLocation ();
		$path = "$path/$model_id/img";
		
		$imageFiles = array ();
		
		if (is_dir ( $path )) {
			$dh = opendir ( $path );
			if ($dh) {
				while ( ($file = readdir ( $dh )) !== false ) {
					if ($file != '.' && $file != '..' && strstr ( $file, 'tn_' ) != NULL) {
						array_push ( $imageFiles, $file );
					}
				}
				closedir ( $dh );
			}
		}
		
		return $imageFiles;
	}
	
	private function getPrincipalModelmageFiles($model_id)
	{
		$path = $this->getFileUploadLocation ();
		$path = "$path/$model_id/img";
	
		$imageFiles = array ();
	
		if (is_dir ( $path )) {
			$dh = opendir ( $path );
			if ($dh) {
				while ( ($file = readdir ( $dh )) !== false ) {
					if ($file != '.' && $file != '..' && strstr ( $file, 'tn_' ) == NULL) {
						array_push ( $imageFiles, $file );
					}
				}
				closedir ( $dh );
			}
		}
	
		return $imageFiles;
	}	
	

}