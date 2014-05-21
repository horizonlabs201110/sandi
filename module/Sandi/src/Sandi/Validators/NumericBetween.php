<?php


namespace Sandi\Validators;

use \Zend\Validator\AbstractValidator;

class NumericBetween extends AbstractValidator
{
	const MSG_NUMERIC = 'msgNumeric';
	const MSG_MINIMUM = 'msgMinimum';
	const MSG_MAXIMUM = 'msgMaximum';

	public $minimum = 0;
	public $maximum = 100;

	protected $messageVariables = array(
			'min' => 'minimum',
			'max' => 'maximum'
	);

	protected $messageTemplates = array(
			self::MSG_NUMERIC => "'%value%' is not numeric",
			self::MSG_MINIMUM => "'%value%' must be at least '%min%'",
			self::MSG_MAXIMUM => "'%value%' must be no more than '%max%'"
	);

	public function isValid($value)
	{
		$this->setValue($value);

		if (!is_numeric($value)) {
			$this->error(self::MSG_NUMERIC);
			return false;
		}

		if ($value < $this->minimum) {
			$this->error(self::MSG_MINIMUM);
			return false;
		}

		if ($value > $this->maximum) {
			$this->error(self::MSG_MAXIMUM);
			return false;
		}

		return true;
	}
}
