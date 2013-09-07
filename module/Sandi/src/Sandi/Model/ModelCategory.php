<?php
namespace Sandi\Model;

// Add these import statements
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ModelCategory implements InputFilterAwareInterface
{
    public $category_id;
    public $category_name;
	public $parent_id;
	public $visibility;
	public $descript;
	public $title;
	
	protected $inputFilter;
	
	public function exchangeArray($data)
    {
        $this->category_id = (!empty($data['category_id'])) ? $data['category_id'] : 0;
		$this->category_name  = (!empty($data['category_name'])) ? $data['category_name'] : NULL;
		$this->parent_id = (!empty($data['parent_id'])) ? $data['parent_id'] : 0;
		$this->visibility = (!empty($data['visibility'])) ? $data['visibility'] : 1;
		$this->descript  = (!empty($data['descript'])) ? $data['descript'] : NULL;
		$this->title  = (!empty($data['title'])) ? $data['title'] : NULL;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
		return $this->inputFilter;
	}
}
