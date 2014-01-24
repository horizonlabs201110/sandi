<?php

namespace Usermanage\Form;
// ini_set('default_charset','gb2312');
use Zend\Form\Form;

class UserForm extends Form {
	public function __construct($name = null) {
		// we want to ignore the name passed
		parent::__construct ( 'user' );
		$this->setAttribute ( 'method', 'post' );
		$this->add ( array (
				'name' => 'user_id',
				'type' => 'Hidden' 
		) );
		
		$this->add ( array (
				'name' => 'user_account',
				'type' => 'Text',
				'options' => array (
						'label' => 'user account' 
				) 
		) );
		
		$this->add ( array (
				'name' => 'alias',
				'type' => 'Text',
				'options' => array (
						'label' => 'alias' 
				) 
		) );
		
		$this->add ( array (
				'name' => 'password',
				'type' => 'Text',
				'options' => array (
						'label' => 'password' 
				) 
		) );
		
		$this->add ( array (
				'name' => 'last_login',
				'type' => 'Hidden' 
		) );
		
		$this->add ( array (
				'name' => 'status',
				'type' => 'Hidden' 
		) );
		
		$this->add ( array (
				'name' => 'avatar',
				'type' => 'Text',
				'options' => array (
						'label' => 'avatar' 
				) 
		) );
		
		$this->add ( array (
				'name' => 'profile',
				'type' => 'Text',
				'options' => array (
						'label' => 'profile' 
				) 
		) );
		$this->add ( array (
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array (
						'value' => 'Go',
						'id' => 'submitbutton' 
				) 
		) );
	}
}

