<?php
namespace Sandi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Form\FormInterface;

use Sandi\Model\Model;    
use Sandi\Model\ModelTable;
use Sandi\Model\ModelFlagMapping;
use Sandi\Model\ModelFlagMappingTable;
use Sandi\Model\ModelCategory;
use Sandi\Model\ModelCategoryTable;
use Sandi\Form\UploadModelForm;



class ModelData extends TableGateway {};

class SandiController extends AbstractActionController
{
	protected $modelTable;
	protected $modelFlagMappingTable;
	protected $categoryTable;
	
	
	public function init()
	{
/* 		// 取得登录状态，状态存储在Zend_Session中
		$session=new Zend_Session_Namespace();
		$login=$session->login;

		if (isset($login))
		{
			// 给view中相关变量赋值
			$this->view->user_id=$login['user_id'];
			$this->view->name=$login['Name'];
		}
		else 
		{
			$this->view->user_id=$this->view->name=0;
		} */

		// 默认标题
		//$this->view->title='Sandi首页 ';
	}
	
	public function indexAction()
    {
		$category_id = (int) $this->params()->fromRoute('id', 0);
        if (!$category_id) 
		{
            $category_id = 0;
        }		

		$results = $this->getModelByCategory($category_id);
		
		return new ViewModel(array('models' => $results));
		
    }
	
	
	public function editAction()
    {
		$model_id = (int) $this->params()->fromRoute('id', 0);
        if (!$model_id) {
            return $this->redirect()->toRoute('sandi', array(
                'action' => 'uploadModel'
            ));
        }
        
		// Get the model details with the specified id.  An exception is thrown
        // if it cannot be found, in which case go to the index page.
        try {
            $model = $this->getModelTable()->getModel($model_id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('sandi', array(
                'action' => 'index'
            ));
        }	
		
	    $form  = new UploadModelForm();
        $form->bind($model);

		//add category
		$form->categoryData = $this->getModelCategoryTable()->fetchAll();
		$form->addCategory();	
		
        $form->get('submit')->setAttribute('value', 'Edit');	
		
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($model->getInputFilter());
			// Make certain to merge the files info!
			$post = array_merge_recursive(
				$request->getPost()->toArray(),
				$request->getFiles()->toArray()
			);

			$form->setData($post);

            if ($form->isValid()) {
			
				$data = $form->getData(FormInterface::VALUES_AS_ARRAY);
				
				$adapter = $this->getAdapter();
				$sql = new Sql($adapter);
				$update = $sql->update();	
				
				
				
				//1. update model table
				$profile= $data["profile"];
				echo $profile;
				$update->table('t_model');
				$update->set(array(
					'profile' => $profile,
					));
				$update->where( array ('model_id' => $model_id));
				
				$updateString = $sql->getSqlStringForSqlObject($update);
				$results = $adapter->query($updateString, $adapter::QUERY_MODE_EXECUTE);
				
				//2. update grant table
				$offer= $data["offer"];
				$update->table('t_grant');
				$update->set(array(
					'grant_bitmap' => $offer,
					));
				$update->where( array ('model_id' => $model_id));
				
				$updateString = $sql->getSqlStringForSqlObject($update);
				echo $updateString;
				$results = $adapter->query($updateString, $adapter::QUERY_MODE_EXECUTE);
				
				//3. update model-category-mapping table
				$categoryID = $data["category"];
				$update->table('t_model_category_mapping');
				$update->set(array(
					'category_id' => $data["category"],
					));
				$update->where( array ('model_id' => $model_id));
				
				$updateString = $sql->getSqlStringForSqlObject($update);
				echo $updateString;
				$results = $adapter->query($updateString, $adapter::QUERY_MODE_EXECUTE);
				
				//3. update offer table
				$offer = $data["offer"];
				$price = $data["price"];
				$update->table('t_offer');
				$update->set(array(
					'contents' => $price,
					'grant_bitmap' => $offer,
					));
				$update->where( array ('model_id' => $model_id));
				
				$updateString = $sql->getSqlStringForSqlObject($update);
				echo $updateString;
				$results = $adapter->query($updateString, $adapter::QUERY_MODE_EXECUTE);
				
                return $this->redirect()->toUrl('/sandi/inform/2');
            }
        }

        return array(
            'id' => $model_id,
            'form' => $form,
        );
		
    }	
	
	
	public function uploadModelAction()
	{
		$sessionUser = new Container('user');
		if ($sessionUser->user_id ==NULL)	
		{
			return $this->redirect()->toUrl('/user/login');
		}
		
		$form     			= new UploadModelForm('upload-form');
		$form->categoryData = $this->getModelCategoryTable()->fetchAll();
		$form->addCategory();

		$request = $this->getRequest();
		if ($request->isPost()) {
			// Make certain to merge the files info!
			$post = array_merge_recursive(
				$request->getPost()->toArray(),
				$request->getFiles()->toArray()
			);

			$form->setData($post);
			if ($form->isValid()) {
				$data = $form->getData();
				
				$data_imgFile = $data["image-file"];
				$name = $data_imgFile["name"];
				$type = $data_imgFile["type"];
				$error = $data_imgFile["error"];
				$tmp_name = $data_imgFile["tmp_name"];
				
				switch ($type) 
				{
					case 'image/pjpeg' : $ok=1;
						break;
					case 'image/jpeg' : $ok=1;
						break;
					case 'image/gif' : $ok=1;
						break;
					case 'image/png' : $ok=1;
						break;
				}
				$ok = 1;
				if($ok && $error=='0')
				{
					
					//1. save model data
					$model = new Model();
					$model->exchangeArray($data);
					$model->owner_id = $sessionUser->user_id;
					
					//echo $sessionUser->user_id;
					$model->designer_id = $sessionUser->user_id;
					
					$this->getModelTable()->SaveModel($model);
					
					//2. save offer data
					$lastInsertModelID = $this->getModelTable()->lastInsertValue;
					$price = $data["price"];
					$offer = $data["offer"];
					//echo $offer. "<br>";
					
					if($price != NULL)
					{
						$adapter = $this->getAdapter();
						$sql = new Sql($adapter);
						$insert = $sql->insert();
						$insert->into('t_offer');
						$insert->values(array(
							'model_id' => $lastInsertModelID,
							'contents' => $price,
							'grant_bitmap' => $offer,
							));
							
						$insertString = $sql->getSqlStringForSqlObject($insert);
						$results = $adapter->query($insertString, $adapter::QUERY_MODE_EXECUTE);	
						
					}
					
					//3. save model-category-mapping
					$categoryID = $data["category"];
					
					$adapter = $this->getAdapter();
					$sql = new Sql($adapter);
					$insert = $sql->insert();
					$insert->into('t_model_category_mapping');
					$insert->values(array(
						'model_id' => $lastInsertModelID,
						'category_id' => $categoryID,
						));
							
					$insertString = $sql->getSqlStringForSqlObject($insert);
					$results = $adapter->query($insertString, $adapter::QUERY_MODE_EXECUTE);

					//4. save grant data
					$sql = new Sql($adapter);
					$insert = $sql->insert();
					$insert->into('t_grant');
					$insert->values(array(
						'model_id' => $lastInsertModelID,
						'grant_bitmap' =>  $offer + 2 + 1,
						));
							
					$insertString = $sql->getSqlStringForSqlObject($insert);
					$results = $adapter->query($insertString, $adapter::QUERY_MODE_EXECUTE);					
					
					
					//5. save file
					$data_imgFile = $data["image-file"];
					$name = $data_imgFile["name"];
					$tmp_name = $data_imgFile["tmp_name"];
					//echo $tmp_name. "<br>";
					
					//$savePath = './3d-model-file/' . $lastInsertModelID . '/';
					$savePath =  __DIR__ . '\\..\\..\\..\\public\\img\\' . $lastInsertModelID . '\\';
					//echo $savePath . $name . "<br>";
					
					if(!file_exists($savePath))
					{
						mkdir($savePath, 0777);
					}
					$ret = move_uploaded_file($tmp_name,  $savePath.$name);
					
					return $this->redirect()->toUrl('/sandi/inform/1');
				}
			
			}
		}
		
		return array(
			'form'     => $form
			);

	}
	
	public function informAction()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
		return new ViewModel(array('informID' => $id));
	}
	
	
	public function modelDetailAction()
	{
		
		$model_id = (int) $this->params()->fromRoute('id', 0);
        if (!$model_id) {
            return $this->redirect()->toRoute('home', array(
                'action' => 'index'
            ));
        }
        
		// Get the model details with the specified id.  An exception is thrown
        // if it cannot be found, in which case go to the index page.
        try
		{
            $model = $this->getModel($model_id);
			$grant = $this->getGrant($model_id);
			
			return new ViewModel(array('model' => $model,
									'grant' => $grant,
								));

        }
        catch (\Exception $ex)
		{
            return $this->redirect()->toRoute('home', array(
                'action' => 'index'
            ));
        }	
	}
		
	private function getGrant($model_id)
	{
		$adapter = $this->getAdapter();
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from('t_grant');
		//$select->join('t_offer', 
		//'t_model.model_id = t_offer.model_id',
		//array('contents'));
		$select->where( array ('model_id' => $model_id));
		$selectString = $sql->getSqlStringForSqlObject($select);
	
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);		
		//$resultArray = $results->toArray();
		$rows = array_values(iterator_to_array($results));
		return $rows;
		
	}
	
		private function getModel($model_id)
	{
		$adapter = $this->getAdapter();
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from('t_model');
		$select->where( array ('model_id' => $model_id));
		$selectString = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);		
		$rows = array_values(iterator_to_array($results));
		return $rows;
		
	}
	
	private function getModelByCategory($category_id)
	{
		$adapter = $this->getAdapter();
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from('t_model');
		$select->join('t_model_category_mapping', 
		't_model.model_id = t_model_category_mapping.model_id',
		array('model_id'));		
		$select->where( array ('category_id' => $category_id));
		$selectString = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);		
		$rows = array_values(iterator_to_array($results));
		return $rows;
	}
	
	
	
	public function getModelTable()
    {
        if (!$this->modelTable) {
            $sm = $this->getServiceLocator();
            $this->modelTable = $sm->get('Sandi\Model\ModelTable');
        }
        return $this->modelTable;
    } 
	
	public function getModelFlagMappingTable()
    {
        if (!$this->modelFlagMappingTable) {
            $sm = $this->getServiceLocator();
            $this->modelFlagMappingTable = $sm->get('Sandi\Model\ModelFlagMappingTable');
        }
        return $this->modelFlagMappingTable;
    }
	
	public function getModelCategoryTable()
    {
        if (!$this->categoryTable) {
            $sm = $this->getServiceLocator();
            $this->categoryTable = $sm->get('Sandi\Model\ModelCategoryTable');
        }
        return $this->categoryTable;
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

