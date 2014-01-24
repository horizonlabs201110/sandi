<?php

namespace Sandi\Form;

use Zend\InputFilter;
// use Zend\Form\Element;
use Zend\Form\Form;

class UploadModelForm extends Form {
	public function __construct($name = null, $options = array()) {
		parent::__construct ( $name, $options );
		$this->setAttribute ( 'method', 'post' );
		$this->addElements ();
		// $this->addInputFilter();
	}
	public $categoryData;
	public function addCategory() {
		$value_options = array ();
		
		foreach ( $this->categoryData as $column => $value ) {
			// array_push($value_options, $value->category_id , $value->title);
			// array_push($value_options, $value->title);
			// $value_options[ $value->category_id] = mb_convert_encoding($value->title, "UTF-8", "GBK");
			$value_options [$value->category_id] = $value->title;
		}
		
		$this->add ( array (
				'name' => 'category',
				'type' => 'select',
				'options' => array (
						'label' => 'category',
						'value_options' => $value_options 
				)
				 
		) );
	}
	public function addElements() {
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
						'placeholder' => 'profile',
						'size' => '20' 
				)
				 
		) );
		
		$this->add ( array (
				'name' => 'price',
				'type' => 'Text',
				'attributes' => array (
						'class' => 'form-control',
						'placeholder' => 'price' 
				) 
		) );
		
		$this->add ( array (
				'name' => 'offer',
				'type' => 'select',
				'options' => array (
						'label' => 'offer type:',
						'value_options' => array (
								8 => '可下载',
								4 => '可打印' 
						) 
				),
				'attributes' => array (
						'value' => 2  // set selected to "download"
								) 
		)
		 );
		
		// File Input
		// $file = new Element\File('image-file');
		// $file->setLabel('3d-model file Upload')
		// ->setAttribute('id', 'image-file');
		// $this->add($file);
		
		// $this->add(array(
		// 'name' => 'submit',
		// 'type' => 'Submit',
		// 'attributes' => array(
		// 'value' => 'Go',
		// 'id' => 'submitbutton',
		// 'class' => 'form-control'
		// ),
		// ));
	}
	public function addInputFilter() {
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
