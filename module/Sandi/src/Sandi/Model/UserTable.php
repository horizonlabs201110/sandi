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
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getUser($user_account)
    {
		$rowset = $this->tableGateway->select(array('user_account' => $user_account));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row '" . $user_account. "'");
        }
        return $row;
    }	
	
	
    public function saveUser(User $user)
    {
        
		
		
		$data = array(
            'alias' 		=> $user->alias,
            'password'  	=> $user->password,
			'user_account'  => $user->user_account,
			//'last_login' 	=> $user->last_login,
			'status'  		=> $user->status,
			//'avatar'  		=> $user->avatar,
			'profile'  		=> $user->profile,
			
        );

        $user_id = (int)$user->user_id;
        if ($user_id == 0) {
            $this->tableGateway->insert($data);
			
			//get last insert id number
			$this->lastInsertValue = $this->tableGateway->LastInsertValue;	
        } else {
            if ($this->getUser($user_id)) {
                $this->tableGateway->update($data, array('user_id' => $user_id));
            } else {
                throw new \Exception('User id does not exist');
            }
        }
    }	

    public function deleteUser($user_id)
    {
        $this->tableGateway->delete(array('user_id' => $user_id));
    }
}

