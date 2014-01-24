<?php

namespace Sandi\Model;

use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ModelFile implements InputFilterAwareInterface {
	public $id;
	public $model_id;
	public $file_name;
	public $file_type;
	protected $inputFilter;
	public function exchangeArray($data) {
		$this->id = (! empty ( $data ['id'] )) ? $data ['id'] : null;
		$this->model_id = (! empty ( $data ['model_id'] )) ? $data ['model_id'] : null;
		$this->file_name = (! empty ( $data ['file_name'] )) ? $data ['file_name'] : null;
		$this->file_type = (! empty ( $data ['file_type'] )) ? $data ['file_type'] : null;
	}
	
	// Add the following method:
	public function getArrayCopy() {
		return get_object_vars ( $this );
	}
	
	// Add content to these methods:
	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new \Exception ( "Not used" );
	}
	public function getInputFilter() {
		return $this->inputFilter;
	}
}
