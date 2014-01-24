<?php

namespace Sandi\Model;

use Zend\Db\TableGateway\TableGateway;

class ModelTable {
	protected $tableGateway;
	public $lastInsertValue;
	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}
	public function fetchAll() {
		$resultSet = $this->tableGateway->select ();
		return $resultSet;
	}
	public function getModel($model_id) {
		$rowset = $this->tableGateway->select ( array (
				'model_id' => $model_id 
		) );
		$row = $rowset->current ();
		if (! $row) {
			throw new \Exception ( "Could not find row '" . $model_id . "'" );
		}
		return $row;
	}
	public function saveModel(Model $model) {
		$data = array (
				'designer_id' => $model->designer_id,
				'owner_id' => $model->owner_id,
				'profile' => $model->profile 
		)
		;
		
		$model_id = ( int ) $model->model_id;
		if ($model_id == 0) {
			$this->tableGateway->insert ( $data );
			
			// get last insert id number
			$this->lastInsertValue = $this->tableGateway->LastInsertValue;
		} else {
			if ($this->getModel ( $model_id )) {
				$this->tableGateway->update ( $data, array (
						'model_id' => $model_id 
				) );
			} else {
				throw new \Exception ( '3d-model id does not exist' );
			}
		}
	}
	public function deleteModel($model_id) {
		$this->tableGateway->delete ( array (
				'model_id' => $model_id 
		) );
	}
}

// Define processes to t_model_flag_mapping table
class ModelFlagMappingTable {
	protected $tableGateway;
	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}
	public function saveModelFlagMapping($model_id, $flag_id) {
		$data = array (
				'model_id' => $model_id,
				'flag_id' => $flag_id 
		);
		
		$this->tableGateway->insert ( $data );
	}
}

