<?php

namespace Sandi\Form;

use Zend\InputFilter;
use Zend\Form\Form;

class UploadModelForm extends Form {

	public $categoryData;
	
	public function __construct($name = null, $options = array()) 
	{
		parent::__construct ( $name, $options );
		$this->setAttribute ( 'method', 'post' );
		$this->addElements ();
		// $this->addInputFilter();
	}
	
	public function addCategory() 
	{
		$value_options = array ();
		
		foreach ( $this->categoryData as $column => $value ) 
		{
			// array_push($value_options, $value->category_id , $value->title);
			// array_push($value_options, $value->title);
			// $value_options[ $value->category_id] = mb_convert_encoding($value->title, "UTF-8", "GBK");
			$value_options [$value->category_id] = $value->title;
		}
		
		$this->add ( array (
				'name' => 'category',
				'type' => 'select',
				'options' => array (
						'label' => '模型分类：',
						'value_options' => $value_options 
				),
				
				'attributes' => array (
						'class' => 'form-control'
				)				
				 
		) );
	}
	
	
	public function addElements() 
	{
		$this->add ( array (
				'name' => 'model_id',
				'type' => 'Hidden' 
		) );
		
		$this->add ( array (
				'name' => 'user_id',
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
						'label' => '简介:',
				),				
				 
		) );
		
		$this->add ( array (
				'name' => 'tag',
				'type' => 'Text',
				'attributes' => array (
						'class' => 'form-control',
						'placeholder' => '自定义标签'
				),
		
				'options' => array(
						'label' => '自定义标签:',
				),
					
		) );		
		
		$this->add ( array (
				'name' => 'price',
				'type' => 'Text',
				'attributes' => array (
						'class' => 'form-control',
						'placeholder' => '价格' 
				) ,
				
				'options' => array(
						'label' => '价格:',
				),
		) );
		
		$this->add ( array (
				'name' => 'offer',
				'type' => 'select',
				'options' => array (
						'label' => '授权类型:',
						'value_options' => array (
								8 => '下载',   //downlaod	
								4 => '打印' 	//print
						) 
				),
				'attributes' => array (
						'value' => 2 , // set selected to "download"
						'class' => 'form-control'
								) 
		)
		 );
		
// 		// File Input
// 		$file = new Element\File('image-file');
// 		$file->setLabel('3d-model file Upload')
// 		->setAttribute('id', 'image-file');
// 		$this->add($file);
		
		$this->add(array(
		'name' => 'submit',
		'type' => 'Submit',
		'attributes' => array(
		'value' => '提交模型基本信息',
		'id' => 'submitbutton',
		'class' => 'btn btn-lg btn-primary btn-block'
		),
		));
	}
	
	public function addInputFilter() 
	{
		$inputFilter = new InputFilter\InputFilter ();
		
		// File Input
		$fileInput = new InputFilter\FileInput ( 'image-file' );
		$fileInput->setRequired ( true );
		$fileInput->getFilterChain ()->attachByName ( 'filerenameupload', array (
				'target' => './data/tmpuploads/3d_model.png',
				'randomize' => true 
		) );
		
		$inputFilter->add ( $fileInput );
		$this->setInputFilter ( $inputFilter );
	}
}
