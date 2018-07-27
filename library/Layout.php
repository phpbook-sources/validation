<?php namespace PHPBook\Validation;

class Layout {

	private $attributes = [];

	private $rules = [];
	
	public function setAttribute(String $name, Array $attribute): Layout {
		$this->attributes[$name] = $attribute;
		return $this;
	}

    public function getAttributes(): Array {
		return $this->attributes;
	}

	public function setRule(String $name, Array $attributes, \Closure $closure): Layout {
		$this->rules[] = [$name, $attributes, $closure];
		return $this;
	}

    public function getRules(): Array {
		return $this->rules;
	}

	private function generateUUID(): String {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	private function validateType($type, $label, $value) {

		$typeSchema = explode(':', $type);

		$error = null;

		switch($typeSchema[0]) {
			case '@string':
					if ((is_object($value)) or (is_array($value))) {
						$error = 'string';
					};
				break;
			case '@integer':
					if (!ctype_digit((string) $value)) {
						$error = 'integer';
					};
				break;
			case '@boolean':
					if (!is_bool($value)) {
						$error = 'boolean';
					};
				break;
			case '@date':
					$date = \DateTime::createFromFormat('Y-m-d', $value);
					if ((!$date) or ($date->format('Y-m-d') !== $value)) {
						$error = 'date';
					};
					unset($date);
				break;
			case '@datetime':
					$datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
					if ((!$datetime) or ($datetime->format('Y-m-d H:i:s') !== $value)) {
						$error = 'date';
					};
					unset($datetime);
				break;
			case '@time':
					$time = \DateTime::createFromFormat('H:i:s', $value);
					if ((!$time) or ($time->format('H:i:s') !== $value)) {
						$error = 'date';
					};
					unset($time);
				break;
			case '@array':
					if (!is_array($value)) {
						$error = 'array';
					} else {
						if (array_key_exists(1, $typeSchema)) {
							foreach($value as $item) {
								$this->validateType($typeSchema[1], $label, $item);
							};
						};
					}
				break;
			default:
					if (!($value instanceof $typeSchema[0])) {
						$error = 'class';
					}
				break;
		};

		if ($error) {
			throw new \Exception(str_replace('{label}', $label, Configuration\Message::getLabel('type:' . $error)));
		};
		
	}

	private function validateRequired($required, $label, $value) {
		if ($required) {
			if ((!$value) or (strlen($value) == 0)) {
				throw new \Exception(str_replace('{label}', $label, Configuration\Message::getLabel('required')));
			};
		};
	}

	private function validateMin($min, $label, $value) {
		if ($min) {
			if ($value < $min) {
				throw new \Exception(str_replace(['{label}', '{min}'], [$label, $min], Configuration\Message::getLabel('min')));
			};
		};
	}

	private function validateMax($max, $label, $value) {
		if ($max) {
			if ($value > $max) {
				throw new \Exception(str_replace(['{label}', '{max}'], [$label, $max], Configuration\Message::getLabel('max')));
			};
		};
	}

	private function validateMinLength($minLength, $label, $value) {
		if ($minLength) {
			if (strlen($value) < $minLength) {
				throw new \Exception(str_replace(['{label}', '{minlength}'], [$label, $minLength], Configuration\Message::getLabel('minlength')));
			};
		};
	}

	private function validateMaxLength($maxLength, $label, $value) {
		if ($maxLength) {
			if (strlen($value) > $maxLength) {
				throw new \Exception(str_replace(['{label}', '{maxlength}'], [$label, $maxLength], Configuration\Message::getLabel('maxlength')));
			};
		};
	}

	private function validateOptions($options, $label, $value) {
		if ($options) {
			if (!(array_key_exists($value, $options))) {
				throw new \Exception(str_replace(['{label}', '{options}'], [$label, implode(', ', $options)], Configuration\Message::getLabel('options')));
			};
		};
	}

	public function validate(Array $values, Array $attributes) {
		
		if (count($values) != count($attributes)) {
			throw new \Exception(Configuration\Message::getLabel('values:attrs'));
		};

		foreach($values as $position => $value) {

			if (!array_key_exists($attributes[$position], $this->getAttributes())) {
				throw new \Exception(str_replace('{attribute}', $attributes[$position], Configuration\Message::getLabel('attr:exists')));
			};

			$attribute =  $this->getAttributes()[$attributes[$position]];

			$label = array_key_exists('label', $attribute) ? $attribute['label'] : $attributes[$position];

			if (array_key_exists('uuid', $attribute)) {

				if (($attribute['uuid']) and ((!$value) or (strlen($value) == 0))) {
					$values[$position] = $this->generateUUID();
					$value = $values[$position];
				};

			};

			if (array_key_exists('required', $attribute)) {

				$this->validateRequired($attribute['required'], $label, $value);

			};

			if ($value) {

				if (array_key_exists('type', $attribute)) {

					$this->validateType($attribute['type'], $label, $value);
	
				};
	
				if (array_key_exists('min', $attribute)) {
					
					$this->validateMin($attribute['min'], $label, $value);
	
				};
	
				if (array_key_exists('max', $attribute)) {
	
					$this->validateMax($attribute['max'], $label, $value);
	
				};
	
				if (array_key_exists('minlength', $attribute)) {
	
					$this->validateMinLength($attribute['minlength'], $label, $value);
				};
	
				if (array_key_exists('maxlength', $attribute)) {
	
					$this->validateMaxLength($attribute['maxlength'], $label, $value);
	
				};
	
				if (array_key_exists('options', $attribute)) {
	
					$this->validateOptions($attribute['options'], $label, $value);
					
				};
				
			};

		};

		foreach($this->getRules() as $position => $rule) {
			
			list($ruleName, $ruleAttributes, $ruleClosure) = $rule;

			$ruleValues = [];

			foreach($ruleAttributes as $ruleAttribute) {

				if (!array_key_exists($ruleAttribute, $this->getAttributes())) {
					throw new \Exception(str_replace('{attribute}', $ruleAttribute, Configuration\Message::getLabel('attr:exists')));
				};

				if (array_key_exists($ruleAttribute, $values)) {

					$ruleValues[] = $values[$ruleAttribute];

				} else {

					$ruleValues[] = null;

				};

			};

			if ($ruleClosure instanceof \Closure) {
				call_user_func_array($ruleClosure, $ruleValues);
			};

		};

		return $values;

	}

}