<?php
namespace Sandi\Model;

use Zend\Db\TableGateway\TableGateway;

//Define processes to t_category table
class ModelCategoryTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
	
   public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }
}
