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

use Sandi\Model\User;    
use Sandi\Model\UserTable;
use Sandi\Form\UserForm;

use Zend\Form\FormInterface;
use Zend\Db\Sql\Sql;

class UserController extends AbstractActionController
{
    protected $userTable;
	
	public function init()
	{
		// 取得登录状态，状态存储在Zend_Session中
/* 		$session=new Zend_Session_Namespace();
		$login=$session->login;

		if (isset($login))
		{
			// 给view中相关变量赋值
			$this->view->aid=$login['AId'];
			$this->view->priv=$login['Priv'];
			$this->view->name=$login['Name'];
		}
		else 
		{
			$this->view->aid=$this->view->priv=$this->view->name=0;
		} */

		// 默认标题
		$this->view->title='Web首页';
	}
	
	public function indexAction()
    {
		$id = (int) $this->params()->fromRoute('id', 0);
		
        if (!$id) 
		{
            return $this->redirect()->toRoute('user', array(
                'action' => 'add'
            ));
        }

        // Get the user with the specified id.  An exception is thrown
        // if it cannot be found, in which case go to the index page.
        try
		{
            $user = $this->getUserByID($id);
        }
        catch (\Exception $ex)
		{
            return $this->redirect()->toRoute('user', array(
                'action' => 'index'
            ));
        }
		
		return  new ViewModel(array('user' => $user));
	
    }

    public function addAction()
    {
 	    $form = new UserForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {

			$post = array_merge_recursive(
				$request->getPost()->toArray(),
				$request->getFiles()->toArray()
			);
			$form->setData($post);

            if ($form->isValid()) {
			
                $data = $form->getData();
				
				//1. save user regist data
				$user = new User();
				$user->exchangeArray($form->getData());
                $this->getUserTable()->saveUser($user);

				
				//2. set session as current register user
				$sessionUser = new Container('user');
				$sessionUser->name= $user->user_account;
				
				//3. save file
				$lastInsertUserID = $this->getUserTable()->lastInsertValue;
				$data_imgFile = $data["avatar-file"];
				$name = $data_imgFile["name"];
				$tmp_name = $data_imgFile["tmp_name"];
				$savePath = './avatar/' . $lastInsertUserID . '/';
			
				if(!file_exists($savePath))
				{
					mkdir($savePath, 0777);
				}
				$ret = move_uploaded_file($tmp_name,  $savePath.$name);
				
                //return $this->redirect()->toRoute('home');
            }
        }
        return array('form' => $form);

    }

    // Add content to this method:
    public function editAction()
    {
		$id = (int) $this->params()->fromRoute('id', 0);
		
        if (!$id) 
		{
            return $this->redirect()->toRoute('user', array(
                'action' => 'add'
            ));
        }

        // Get the user with the specified id.  An exception is thrown
        // if it cannot be found, in which case go to the index page.
        try
		{
            $user = $this->getUserByID($id);
        }
        catch (\Exception $ex)
		{
            return $this->redirect()->toRoute('user', array(
                'action' => 'index'
            ));
        }

        $form  = new UserForm();
        $form->bind($user[0]);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            //$form->setInputFilter($user->getInputFilter());
			$post = array_merge_recursive(
				$request->getPost()->toArray(),
				$request->getFiles()->toArray()
			);
			$form->setData($post);

            if ($form->isValid()) {
				$data = $form->getData(FormInterface::VALUES_AS_ARRAY);
                $data["user_id"] = $id;
				
				
				//1. update user data
				$this->UpdateUser($data);
				
				//2. update avatar fie
				$data_imgFile = $data["avatar-file"];
				$name = $data_imgFile["name"];
				$tmp_name = $data_imgFile["tmp_name"];
				$savePath = './avatar/' . $id . '/';
			
				if(!file_exists($savePath))
				{
					mkdir($savePath, 0777);
				}
				$ret = move_uploaded_file($tmp_name,  $savePath.$name);
			
                // Redirect to list of albums
                return $this->redirect()->toUrl('/user/success');
            }
        }

       return array(
           'id' => $id,
           'form' => $form,
       );
    }

	
	private function UpdateUser($user)
	{
		
		$adapter = $this->getAdapter();
		$sql = new Sql($adapter);
		$update = $sql->update();	
		
		$update->table('t_user');
		$update->set(array(
			'alias' 		=> $user["alias"],
            'password'  	=> $user["password"],
			//'user_account'  => $user->user_account,
			//'last_login' 	=> $user->last_login,
			//'status'  		=> $user->status,
			//'avatar'  		=> $user->avatar,
			'profile'  		=> $user["profile"],
			));
		$update->where( array ('user_id' => $user["user_id"]));
		
		$updateString = $sql->getSqlStringForSqlObject($update);
		$results = $adapter->query($updateString, $adapter::QUERY_MODE_EXECUTE);
	}


    public function loginAction()
    {

        $request = $this->getRequest();

        if ($request->isPost()) {
            $user_account = $request->getPost('user_account');
			$password = $request->getPost('password');
			
			// if it cannot be found, in which case go to the index page.
			try {
				$user = $this->getUserTable()->getUser($user_account);
				if(!strcmp($password,$user->password))
				{
					$sessionUser = new Container('user');
					$sessionUser->name= $user_account;
					$sessionUser->alias = $user->alias;	
					$sessionUser->user_id = $user->user_id;	

					//session storage
					 $adapter = new Adapter(array(
						'driver' => 'Mysqli',
						'host' => 'localhost',
						'dbname' => 'sandi',
						'username' => 'root',
						'password' => 'root',
						'options' => array(
							'buffer_results' => true,
						),

					));
					
					$gwOpts = new DbTableGatewayOptions();
					$gwOpts->setDataColumn('data');
					$gwOpts->setIdColumn('id');
					$gwOpts->setLifetimeColumn('lifetime');
					$gwOpts->setModifiedColumn('modified');
					$gwOpts->setNameColumn('name');

					$tableGateway = new TableGateway('session', $adapter);
					$saveHandler = new DbTableGateway($tableGateway, $gwOpts);
					$sessionManager = new SessionManager();
					$sessionManager->setSaveHandler($saveHandler);
					Container::setDefaultManager($sessionManager);

					
					$this->redirect()->toUrl('/sandi/index');
				}
				else
				{
					echo "invalide user name or password";
					return;
					
					
				}
			}
			catch (\Exception $ex) {
/* 				return $this->redirect()->toRoute('usermanage', array(
					'action' => 'index'
				)); */
				
				$this->redirect()->toUrl('/sandi/index');
			}
		}		
    }

	 public function logoutAction(){
	 
		$sessionUser = new Container('user');
		$sessionUser->name= NULL;
		$sessionUser->user_id = NULL;
					
		$this->redirect()->toUrl('/sandi/index');
	 }
	
	
	public function successAction(){
		
		$this->redirect()->toUrl('/sandi/index');

	 }
	
	public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Sandi\Model\UserTable');
        }
        return $this->userTable;
    } 
	
	private function getUserByID($user_id)
	{
		$adapter = $this->getAdapter();
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from('t_user');
		$select->where( array ('user_id' => $user_id));
		$selectString = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);		
		$rows = array_values(iterator_to_array($results));
		return $rows;
	
	}
	
	public function getAdapter()
	{
			$adapter = new Adapter(array(
			'driver' => 'Mysqli',
			'hostname' => 'localhost',
			'database' => 'sandi',
			'username' => 'root',
			'password' => 'root'
		 ));
		
		return $adapter;
	}	

}

