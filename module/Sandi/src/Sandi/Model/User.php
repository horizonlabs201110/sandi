<?php
namespace Sandi\Model;

// Add these import statements
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class User implements InputFilterAwareInterface
{
    public $user_id;
    public $alias;
    public $password;
	public $user_account;
	//public $last_login;
	public $status;
	//public $avatar;
	public $profile;
    
	protected $inputFilter;                       // <-- Add this variable
	
    public function exchangeArray($data)
    {
        $this->user_id     = (!empty($data['user_id'])) ? $data['user_id'] : null;
        $this->alias = (!empty($data['alias'])) ? $data['alias'] : null;
        $this->password  = (!empty($data['password'])) ? $data['password'] : null;
		$this->user_account  = (!empty($data['user_account'])) ? $data['user_account'] : null;
		//$this->last_login  = (!empty($data['last_login'])) ? $data['last_login'] : null;
		$this->status  = (!empty($data['status'])) ? $data['status'] : null;
		//$this->avatar  = (!empty($data['avatar'])) ? $data['avatar'] : null;
		$this->profile  = (!empty($data['profile'])) ? $data['profile'] : null;
    }

 
    // Add the following method:
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	
	
    // Add content to these methods:
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            // $inputFilter->add($factory->createInput(array(
                // 'name'     => 'user_id',
                // 'required' => true,
                // 'filters'  => array(
                    // array('name' => 'Int'),
                // ),
            // )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'alias',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'user_account',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    } 
}

