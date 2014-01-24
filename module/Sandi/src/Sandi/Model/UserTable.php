<?php

namespace Sandi\Model;

use Zend\Db\TableGateway\TableGateway;

class UserTable 
{
	protected $tableGateway;
	public $lastInsertValue;
	
	public function __construct(TableGateway $tableGateway) 
	{
		$this->tableGateway = $tableGateway;
	}
	public function fetchAll() 
	{
		$resultSet = $this->tableGateway->select ();
		return $resultSet;
	}
	
	public function getUser($user_account) 
	{
		$rowset = $this->tableGateway->select ( array (
				'user_account' => $user_account 
		) );
		
		$row = $rowset->current ();
		if (! $row) 
		{
			throw new \Exception ( "此用户不存在: '" . $user_account . "'" );
		}
		return $row;
	}
	
	public function saveUser(User $user) 
	{

		
		$user_id = ( int ) $user->user_id;
		
		if ($user_id == 0) 
		{
			$data = array (
					'alias' => $user->alias,
					'password' => $user->password,
					'user_account' => $user->user_account,
					'status' => 1,
					'profile' => $user->profile
			);
			
			
			$this->tableGateway->insert ( $data );
			
			// get last insert id number
			$this->lastInsertValue = $this->tableGateway->LastInsertValue;
		} 
		else 
		{
			if ($this->getUser ( $user_id )) 
			{
				
				$data = array ( 'profile' => $user->profile );
				$this->tableGateway->update ( $data, array (
						'user_id' => $user_id 
				) );
			} 
			else 
			{
				throw new \Exception (  "此用户不存在！"  );
			}
		}
	}
	
	
	public function deleteUser($user_id) 
	{
		$this->tableGateway->delete ( array (
				'user_id' => $user_id 
		) );
	}
	
	
	public function changePassword(User $user)
	{
		if ($this->getUser ( $user->user_id ))
		{
		
			$data = array ( 'profile' => $user->profile );
			$this->tableGateway->update ( $data, array (
					password => $user->password
			) );
		}
		else
		{
			throw new \Exception (  "此用户不存在！"  );
		}
	}
}

