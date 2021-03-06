<?php

namespace Sandi;

use Sandi\Model\Model;
use Sandi\Model\ModelTable;
use Sandi\Model\ModelFlag;
use Sandi\Model\ModelFlagTable;
use Sandi\Model\ModelFlagMapping;
use Sandi\Model\ModelFlagMappingTable;
use Sandi\Model\ModelFile;
use Sandi\Model\ModelFileTable;
use Sandi\Model\ModelCategory;
use Sandi\Model\ModelCategoryTable;
use Sandi\Model\User;
use Sandi\Model\UserTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module {
	public function getAutoloaderConfig()
	{
		return array (
				'Zend\Loader\ClassMapAutoloader' => array (
						__DIR__ . '/autoload_classmap.php' 
				),
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ 
						) 
				) 
		);
	}
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
	public function getServiceConfig() {
		return array (
// 				'validators' => array(
// 				     'invokables' => array(
// 				         'NumericBetween' => 'Sandi\Validators\NumericBetween'
// 				     ),
// 				 ),
				
				'factories' => array (
						'Sandi\Model\ModelTable' => function ($sm) {
							$tableGateway = $sm->get ( 'ModelTableGateway' );
							$table = new ModelTable ( $tableGateway );
							return $table;
						},
						'ModelTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$resultSetPrototype = new ResultSet ();
							$resultSetPrototype->setArrayObjectPrototype ( new Model () );
							return new TableGateway ( 't_model', $dbAdapter, null, $resultSetPrototype );
						},
						
						'Sandi\Model\ModelFlagTable' => function ($sm) {
							$tableGateway = $sm->get ( 'ModelFlagTableGateway' );
							$table = new ModelFlagTable ( $tableGateway );
							return $table;
						},
						'ModelFlagTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$resultSetPrototype = new ResultSet ();
							$resultSetPrototype->setArrayObjectPrototype ( new ModelFlag () );
							return new TableGateway ( 't_model_flag', $dbAdapter, null, $resultSetPrototype );
						},						
						
						'Sandi\Model\ModelFlagMappingTable' => function ($sm) {
							$tableGateway = $sm->get ( 'ModelFlagMappingTableGateway' );
							$table = new ModelFlagMappingTable ( $tableGateway );
							return $table;
						},
						'ModelFlagMappingTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$resultSetPrototype = new ResultSet ();
							$resultSetPrototype->setArrayObjectPrototype ( new ModelFlagMapping () );
							return new TableGateway ( 't_model_flag_mapping', $dbAdapter, null, $resultSetPrototype );
						},
						'Sandi\Model\ModelFileTable' => function ($sm) {
							$tableGateway = $sm->get ( 'ModelFileTableGateway' );
							$table = new ModelFileTable ( $tableGateway );
							return $table;
						},
						'ModelFileTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$resultSetPrototype = new ResultSet ();
							$resultSetPrototype->setArrayObjectPrototype ( new ModelFile () );
							return new TableGateway ( 't_model_file', $dbAdapter, null, $resultSetPrototype );
						},
						
						'Sandi\Model\ModelCategoryTable' => function ($sm) {
							$tableGateway = $sm->get ( 'ModelCategoryTableGateway' );
							$table = new ModelCategoryTable ( $tableGateway );
							return $table;
						},
						'ModelCategoryTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$resultSetPrototype = new ResultSet ();
							$resultSetPrototype->setArrayObjectPrototype ( new ModelCategory () );
							return new TableGateway ( 't_category', $dbAdapter, null, $resultSetPrototype );
						},
						
						'Sandi\Model\UserTable' => function ($sm) {
							$tableGateway = $sm->get ( 'UserTableGateway' );
							$table = new UserTable ( $tableGateway );
							return $table;
						},
						'UserTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$resultSetPrototype = new ResultSet ();
							$resultSetPrototype->setArrayObjectPrototype ( new User () );
							return new TableGateway ( 't_user', $dbAdapter, null, $resultSetPrototype );
						}

						//'NumericBetween' => 'Sandi\Validator\NumericBetween'
				) 
		);
	}
}

