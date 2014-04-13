<?php

namespace Sandi\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class ModelProjectFileUploadForm extends Form {
	public function __construct($name = null, $options = array()) {
		parent::__construct ( $name, $options );
		
		$this->setAttribute ( 'method', 'post' );
		
		$this->addElements ();
	}
	public function addElements() {
		// File Input
		$file = new Element\File ( 'model-project-file' );
		$file->setLabel ( "模型工程文件" )->setAttribute ( 'id', 'model-project-file' )
										->setAttribute( 'class', 'form-control' );
		$this->add ( $file );
		
		$this->add ( array (
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array (
						'value' => '上传模型工程文件',
						'id' => 'submitbutton',
						'class' => 'form-control',
						'class' => 'btn btn-lg btn-primary btn-block'
				) 
		) );
	}
}

?>