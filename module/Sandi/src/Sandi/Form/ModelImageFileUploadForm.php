<?php

namespace Sandi\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class ModelImageFileUploadForm extends Form 
{
	public function __construct($name = null, $options = array()) 
	{
		parent::__construct ( $name, $options );
		
		$this->setAttribute ( 'method', 'post' );
		
		$this->addElements ();
	}
	
	
	public function addElements() {
		// File Input
		$file = new Element\File ( 'image-file' );
		
		$file->setLabel ( "模型图片文件：" )->setAttribute ( 'id', 'image-file' )
										->setAttribute( 'class', 'form-control' );
		$this->add ( $file );
		
		$this->add ( array (
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array (
						'value' => '上传模型图片文件',
						'id' => 'submitbutton',
						'class' => 'btn btn-lg btn-primary btn-block' 
				) 
		) );
	}
}

?>