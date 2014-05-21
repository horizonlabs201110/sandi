<?php

namespace Sandi\Model;

// Add these import statements
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Model implements InputFilterAwareInterface {
	public $model_id;
	public $designer_id;
	public $owner_id;
	public $profile;
	// public $create_time;
	
	protected $inputFilter;
	
	
	public function exchangeArray($data) 
	{
		$this->model_id = (! empty ( $data ['model_id'] )) ? $data ['model_id'] : null;
		$this->designer_id = (! empty ( $data ['user_id'] )) ? $data ['user_id'] : null;
		$this->owner_id = (! empty ( $data ['owner_id'] )) ? $data ['owner_id'] : null;
		$this->profile = (! empty ( $data ['profile'] )) ? $data ['profile'] : null;
	}
	
	// Add the following method:
	public function getArrayCopy()
	{
		return get_object_vars ( $this );
	}
	
	// Add content to these methods:
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception ( "Not used" );
	}
	
	
	public function getInputFilter() 
	{
		if (! $this->inputFilter) 
		{
			$inputFilter = new InputFilter ();
			$factory = new InputFactory ();

			$inputFilter->add ( $factory->createInput ( array (
					'name' => 'profile',
					'required' => true,
					'filters' => array (
							array (	'name' => 'StripTags' ),
							array (	'name' => 'StringTrim' ) 
					),
					
					'validators' => array (
							array (
									'name' => 'StringLength',
									'options' => array (
											'encoding' => 'UTF-8',
											'min' => 1,
											'max' => 1000 
									) 
							) 
					) 
			) ) );
			

			$inputFilter->add($factory->createInput(array(	
			
					'name'     => 'price',
					'required' => true,
					'validators' => array (
							array (
									'name' => 'Sandi\Validators\NumericBetween',
							 )
							)
			)));
				
			
			
			$this->inputFilter = $inputFilter;
		}
		
		return $this->inputFilter;
	}
}


class ModelFlag implements InputFilterAwareInterface {
	public $flag_name;
	protected $inputFilter;
	
	public function exchangeArray($data) {
		$this->flag_name = (! empty ( $data ['flag_name'] )) ? $data ['flag_name'] : 0;
	}

	// Add content to these methods:
	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new \Exception ( "Not used" );
	}
	public function getInputFilter() {
		return $this->inputFilter;
	}
}

class ModelFlagMapping implements InputFilterAwareInterface {
	public $model_id;
	public $flag_id;
	protected $inputFilter;
	public function exchangeArray($data) {
		$this->model_id = (! empty ( $data ['model_id'] )) ? $data ['model_id'] : 0;
		$this->flag_id = (! empty ( $data ['flag_id'] )) ? $data ['flag_id'] : 0;
	}
	
	// Add content to these methods:
	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new \Exception ( "Not used" );
	}
	public function getInputFilter() {
		return $this->inputFilter;
	}
}




