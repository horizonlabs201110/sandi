<?php

namespace Sandi\Model;

use Zend\Db\TableGateway\TableGateway;

// Define processes to t_model_file table
class ModelFileTable {
	protected $tableGateway;
	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}
	public function saveModelFile($modelFile) {
		$data = array (
				
				'model_id' => $modelFile->model_id,
				'file_name' => $modelFile->file_name,
				'file_type' => $modelFile->file_type 
		)
		;
		
		$this->tableGateway->insert ( $data );
	}
	public function getModelFile($model_id, $file_type) {
		$resultSet = $this->tableGateway->select ( array (
				'model_id' => $model_id,
				'file_type' => $file_type 
		) );
		return $resultSet;
	}
	public function getModelFileById($id) {
		$resultSet = $this->tableGateway->select ( array (
				'id' => $id 
		) );
		return $resultSet;
	}
}


