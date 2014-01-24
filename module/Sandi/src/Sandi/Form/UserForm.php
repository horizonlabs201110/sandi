<?php

namespace Sandi\Form;

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
				'attributes' => array (
						'class' => 'form-control',
						'placeholder' => 'user_account' 
				) 
		) );
		
		$this->add ( array (
				'name' => 'alias',
				'type' => 'Text',
				'attributes' => array (
						'class' => 'form-control',
						'placeholder' => 'alias' 
				) 
		) );
		
		$this->add ( array (
				'name' => 'password',
				'type' => 'Text',
				'attributes' => array (
						'class' => 'form-control',
						'placeholder' => 'password' 
				),
				
				'options' => array(
				'label' => 'password',
				 ),
				
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
				'name' => 'profile',
				'type' => 'Text',
				'attributes' => array (
						'class' => 'form-control',
						'placeholder' => 'profile'
				)
		) );		
		
		
		$this->add ( array (
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array (
						'value' => 'Go',
						'id' => 'submitbutton',
						'class' => 'btn btn-lg btn-primary btn-block' 
				) 
		) );
	}
}

