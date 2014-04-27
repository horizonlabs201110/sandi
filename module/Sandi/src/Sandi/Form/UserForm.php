<?php

namespace Sandi\Form;

use Zend\Form\Form;

class UserForm extends Form {
	public function __construct($name = null) {
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
						'placeholder' => '帐号' 
				) ,
				
				'options' => array(
						'label' => '帐号：',
				),	
		) );
		
		$this->add ( array (
				'name' => 'alias',
				'type' => 'Text',
				'attributes' => array (
						'class' => 'form-control',
						'placeholder' => '昵称' 
				),
				
				'options' => array(
						'label' => '昵称：',
				),				
		) );
		
		$this->add ( array (
				'name' => 'password',
				'type' => 'password',
				'attributes' => array (
						'class' => 'form-control',
						'placeholder' => '密码' 
				),
				
				'options' => array(
				'label' => '密码：',
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
						'placeholder' => '简介'
				),
				
				'options' => array(
						'label' => '简介：',
				),	
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

