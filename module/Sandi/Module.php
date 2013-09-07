<?php
namespace Sandi;

use Sandi\Model\Model;
use Sandi\Model\ModelTable;
use Sandi\Model\ModelFlagMapping;
use Sandi\Model\ModelFlagMappingTable;
use Sandi\Model\ModelCategory;
use Sandi\Model\ModelCategoryTable;
use Sandi\Model\User;
use Sandi\Model\UserTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
	
	

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Sandi\Model\ModelTable' =>  function($sm) {
                    $tableGateway = $sm->get('ModelTableGateway');
                    $table = new ModelTable($tableGateway);
                    return $table;
                },
                'ModelTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model());
                    return new TableGateway('t_model', $dbAdapter, null, $resultSetPrototype);
                },
                'Sandi\Model\ModelFlagMappingTable' =>  function($sm) {
                    $tableGateway = $sm->get('ModelFlagMappingTableGateway');
                    $table = new ModelFlagMappingTable($tableGateway);
                    return $table;
				},
                'ModelFlagMappingTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ModelFlagMapping());
                    return new TableGateway('t_model_flag_mapping', $dbAdapter, null, $resultSetPrototype);
                },	
                'Sandi\Model\ModelCategoryTable' =>  function($sm) {
                    $tableGateway = $sm->get('ModelCategoryTableGateway');
                    $table = new ModelCategoryTable($tableGateway);
                    return $table;
				},
				'ModelCategoryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ModelCategory());
                    return new TableGateway('t_category', $dbAdapter, null, $resultSetPrototype);
                },
                'Sandi\Model\UserTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('t_user', $dbAdapter, null, $resultSetPrototype);
                },				
            ),
        );
    } 	
	
}

