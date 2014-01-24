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
		$file->setLabel ( "model project file" )->setAttribute ( 'id', 'model-project-file' );
		$this->add ( $file );
		
		$this->add ( array (
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array (
						'value' => 'Go',
						'id' => 'submitbutton',
						'class' => 'form-control' 
				) 
		) );
	}
}

?>