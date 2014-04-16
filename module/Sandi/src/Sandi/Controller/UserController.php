<?php
namespace Sandi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\SessionManager;
use Zend\Form\FormInterface;
use Zend\Db\Sql\Sql;

use Sandi\Model\User;
use Sandi\Form\UserForm;
use Sandi\Form\UserAvatarForm;


class UserController extends AbstractActionController {
	
	protected $userTable;
	protected $categoryTable;

	public function indexAction() {
		$id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		
		if (! $id) 
		{
			return $this->redirect ()->toRoute ( 'user', array (
					'action' => 'add' 
			) );
		}
		
		// Get the user with the specified id. An exception is thrown
		// if it cannot be found, in which case go to the index page.
		try 
		{
			$user = $this->getUserByID ( $id );
		} 
		catch ( \Exception $ex ) 
		{
			return $this->redirect()->toRoute( 'user', array ('action' => 'index' ) );
		}
		
		
		$category = $this->getModelCategoryTable()->fetchAll();
		return new ViewModel ( array ('user' => $user,
				'category' => $category
	 			) );
	}
	
	
	
	public function addAction() 
	{
		$form = new UserForm();
		$form->get ( 'submit' )->setValue ( '注册账户' );
		
		$request = $this->getRequest();
		if ($request->isPost()) 
		{
			
			//$post = array_merge_recursive ( $request->getPost ()->toArray (), $request->getFiles ()->toArray () );
			$post =  $request->getPost()->toArray();
			$form->setData ( $post );
			
			if ($form->isValid ()) {
				
				$data = $form->getData ();
				
				// 1. save user regist data
				$user = new User ();
				$user->exchangeArray ( $form->getData () );
				$this->getUserTable ()->saveUser ( $user );
				
				// 2. set session as current register user
				$sessionUser = new Container ( 'user' );
				$sessionUser->name = $user->user_account;
				
				// 3. save file
				$lastInsertUserID = $this->getUserTable ()->lastInsertValue;
				$sessionUser->user_id = $lastInsertUserID;
				
				return $this->redirect()->toRoute ( 'user', array (
							'action' => 'index',
							'id' =>$lastInsertUserID
					) );
			}
		}
		
		$category = $this->getModelCategoryTable()->fetchAll();
		
		return array ( 'form' => $form,
					'category' => $category );
	}
	
	
	
	
	// Add content to this method:
	public function editAction() {
		$id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		
		if (! $id) 
		{
			return $this->redirect ()->toRoute ( 'user', array (
					'action' => 'add' 
			) );
		}
		
		// Get the user with the specified id. An exception is thrown
		// if it cannot be found, in which case go to the index page.
		try 
		{
			$user = $this->getUserByID ( $id );
		} 
		catch ( \Exception $ex ) 
		{
			return $this->redirect ()->toRoute ( 'user', array (
					'action' => 'index' 
			) );
		}
		
		$form = new UserForm ();
		$form->bind ( $user [0] );
		$form->get ( 'submit' )->setAttribute ( 'value', 'Edit' );
		
		$request = $this->getRequest ();
		
		if ($request->isPost ()) 
		{
			// $form->setInputFilter($user->getInputFilter());
			$post = $request->getPost ()->toArray ();
			$form->setData ( $post );
			
			if ($form->isValid ()) {
				$data = $form->getData ( FormInterface::VALUES_AS_ARRAY );
				$data ["user_id"] = $id;
				
				// 1. update user data
				$this->UpdateUser ( $data );
				
			
				// Redirect to list of albums
				return $this->redirect ()->toUrl ( '/user/success' );
			}
		}
		
		$category = $this->getModelCategoryTable()->fetchAll();
		return array (
				'id' => $id,
				'form' => $form,
				'category' => $category
		);
	}
	
	
	private function UpdateUser($user) {
		$adapter = $this->getAdapter ();
		$sql = new Sql ( $adapter );
		$update = $sql->update ();
		
		$update->table ( 't_user' );
		$update->set ( array (
				'alias' => $user ["alias"],
				//'password' => $user ["password"],
				// 'user_account' => $user->user_account,
				// 'last_login' => $user->last_login,
				// 'status' => $user->status,
				// 'avatar' => $user->avatar,
				'profile' => $user ["profile"] 
		) );
		$update->where ( array (
				'user_id' => $user ["user_id"] 
		) );
		
		$updateString = $sql->getSqlStringForSqlObject ( $update );
		$results = $adapter->query ( $updateString, $adapter::QUERY_MODE_EXECUTE );
	}
	
	
	//2013-12-29
	public function uploadAvatarAction()
	{
		$sessionUser = new Container ( 'user' );
		$session_user_id = $sessionUser->user_id;
		if ($session_user_id == NULL)
		{
			return $this->redirect ()->toUrl ( '/user/login' );
		}
		
		$user_id = ( int ) $this->params ()->fromRoute ( 'id', 0 );
		if (!$user_id) 
		{
			return $this->redirect ()->toRoute ( '/user/login' );
		}
		
		if($session_user_id != $user_id)
		{
			return $this->redirect ()->toRoute ( '/user/login' );
		}
		
		$form = new UserAvatarForm();
		
		$request = $this->getRequest ();
		if ($request->isPost ()) 
		{
			$post = $request->getFiles ()->toArray ();
			$form->setData ( $post );
			if ($form->isValid ()) 
			{
				$data = $form->getData ();
		
				$data_imgFile = $data ["avatar-file"];
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
		
				$uploadPath = $this->getAvatarUploadLocation ();
		
				$uploadPath = $uploadPath . '/' . $user_id;
				if (! file_exists ( $uploadPath )) {
					mkdir ( $uploadPath, 0777 );
				}
				else 
				{
					$this->cleanFolder($uploadPath);
				}
		
				// Save Uploaded file
				$name_2312 = iconv("UTF-8","GB2312", $name);   //字符转码
				if (move_uploaded_file($tmp_name, "$uploadPath/$name_2312"))
				{
					// generate thumbnail image
					$tn_img = $this->generateThumbnail ( $name_2312, $user_id );
					return $this->redirect ()->toUrl ( "/user/index/$user_id" );
				}
			}
		}

		return new ViewModel ( array ('form' => $form ) );		
		
	}
	
	
	// 2013-12-29
	public function generateThumbnail($imageFileName, $user_id) 
	{
		$path = $this->getAvatarUploadLocation ();
		$sourceImageFileName = "$path/$user_id/$imageFileName";
		$thumbnailFileName = 'ava_' . $imageFileName;
		$imageThumb = $this->getServiceLocator ()->get ( 'WebinoImageThumb' );
		$thumb = $imageThumb->create ( $sourceImageFileName, $options = array () );
		$thumb->resize ( 75, 75 );
		$thumb->save (  "$path/$user_id/$thumbnailFileName" );
		return $thumbnailFileName;
	}
	
	// 2013-12-29
	public function showAvartaAction() {
	
		$user_id = $this->params()->fromRoute('id');			//user id

		$imagePath = $this->getAvartaFile($user_id);

		$file = file_get_contents($imagePath);
		
	
		// Directly return the Response
		$response = $this->getEvent()->getResponse();
		$response->getHeaders()->addHeaders ( array (
				'Content-Type' => 'application/octet-stream',
				'Content-Disposition' => "attachment;filename=avarta.png"
		) );
		$response->setContent ( $file );
		return $response;
	}	
	
	
	
	public function loginAction() 
	{
		$request = $this->getRequest ();
		
		if ($request->isPost ()) 
		{
			$user_account = $request->getPost ( 'user_account' );
			$password = $request->getPost ( 'password' );
			
			// if it cannot be found, in which case go to the index page.
			try 
			{
				$user = $this->getUserTable ()->getUser ( $user_account );
				if (! strcmp ( $password, $user->password )) 
				{
					$sessionUser = new Container ( 'user' );
					$sessionUser->name = $user_account;
					$sessionUser->alias = $user->alias;
					$sessionUser->user_id = $user->user_id;
					
					// session storage
					$adapter = new Adapter ( array (
							'driver' => 'Mysqli',
							'host' => 'localhost',
							'dbname' => 'sandi',
							'username' => 'root',
							'password' => 'root',
							'options' => array (
									'buffer_results' => true 
							) 
					) );
					
					$gwOpts = new DbTableGatewayOptions ();
					$gwOpts->setDataColumn ( 'data' );
					$gwOpts->setIdColumn ( 'id' );
					$gwOpts->setLifetimeColumn ( 'lifetime' );
					$gwOpts->setModifiedColumn ( 'modified' );
					$gwOpts->setNameColumn ( 'name' );
					
					$tableGateway = new TableGateway ( 'session', $adapter );
					$saveHandler = new DbTableGateway ( $tableGateway, $gwOpts );
					$sessionManager = new SessionManager ();
					$sessionManager->setSaveHandler ( $saveHandler );
					Container::setDefaultManager ( $sessionManager );
					
					$this->redirect ()->toUrl ( '/sandi/index' );
				} 
				else 
				{
					echo "无效的用户名或密码！";
					return;
				}
			} catch ( \Exception $ex ) 
			{
				/*
				 * return $this->redirect()->toRoute('usermanage', array( 'action' => 'index' ));
				 */
				
				$this->redirect ()->toUrl ( '/sandi/index' );
			}
		}
		
		
		$category = $this->getModelCategoryTable()->fetchAll();
		
		return new ViewModel ( array (
				'category' => $category
		) );
		
		
	}
	public function logoutAction() {
		$sessionUser = new Container ( 'user' );
		$sessionUser->name = NULL;
		$sessionUser->user_id = NULL;
		
		$this->redirect ()->toUrl ( '/sandi/index' );
	}
	public function successAction() {
		$this->redirect ()->toUrl ( '/sandi/index' );
	}
	
	public function getUserTable() 
	{
		if (! $this->userTable) {
			$sm = $this->getServiceLocator ();
			$this->userTable = $sm->get ( 'Sandi\Model\UserTable' );
		}
		return $this->userTable;
	}
	
	private function getUserByID($user_id) 
	{
		$adapter = $this->getAdapter ();
		$sql = new Sql ( $adapter );
		$select = $sql->select ();
		$select->from ( 't_user' );
		$select->where ( array (
				'user_id' => $user_id 
		) );
		$selectString = $sql->getSqlStringForSqlObject ( $select );
		$results = $adapter->query ( $selectString, $adapter::QUERY_MODE_EXECUTE );
		$rows = array_values ( iterator_to_array ( $results ) );
		return $rows;
	}
	
	private function getAdapter() 
	{
		$adapter = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Adapter' );
		return $adapter;
	}
	
	private function getAvatarUploadLocation()
	{
		// Fetch Configuration from Module Config
		$config = $this->getServiceLocator ()->get ( 'config' );
		return $config ['module_config'] ['avatar_location'];
	}

	public function getAvartaFile($user_id)
	{
		$path = $this->getAvatarUploadLocation ();
		
		$path1 = $path . '/' . $user_id;
		
		$imageFiles = null;
	
		if (is_dir ( $path1 )) 
		{
			$dh = opendir ( $path1 );
			if ($dh) 
			{
				while ( ($file = readdir ( $dh )) !== false ) 
				{
					if ($file != '.' && $file != '..' && strstr ( $file, 'ava_' ) != NULL) 
					{
						$imageFiles = "$path1/$file";
						break;
					}
				}
				closedir ( $dh );
			}
		}
		
		if ($imageFiles == null)
		{
			$imageFiles ="$path/default_avarta.png";  
		}
	
		return $imageFiles;
	}

	private function cleanFolder($path)  //clean all files under specified folder
	{
		if (is_dir ( $path ))
		{
			$dh = opendir ( $path );
			if ($dh)
			{
				while ( ($file = readdir ( $dh )) !== false )
				{
					if ($file != '.' && $file != '..')
					{
						$fullpath = "$path/$file";
						
						if(!is_dir($fullpath))
						{
							unlink($fullpath);
						}
					}
				}
				closedir ( $dh );
			}
		}
		
	}
	
	
	private function getModelCategoryTable()
	{
		if (! $this->categoryTable) {
			$sm = $this->getServiceLocator ();
			$this->categoryTable = $sm->get ( 'Sandi\Model\ModelCategoryTable' );
		}
		return $this->categoryTable;
	}
		
	
}

