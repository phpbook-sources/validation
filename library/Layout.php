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

	public function getAttributeVerbose($name): ?string {

		if (array_key_exists($name, $this->attributes)) {

            $attributes = $this->attributes[$name];

            $verbose = [];

            foreach($attributes as $attribute => $value) {
                switch ($attribute) {
                    case 'required':
                        if ($value) {
                            $verbose[] = str_replace('{label}', $attributes['label'], Configuration\Message::getLabel('required'));
                        };
                        break;
                    case 'type':
                        $verbose[] = str_replace('{label}', $attributes['label'], \PHPBook\Validation\Configuration\Message::getLabel('type:' . str_replace('@', '', $value)));
                        break;
                    case 'min':
                        $verbose[] = str_replace(['{label}', '{min}'], [$attributes['label'], $value], Configuration\Message::getLabel('min'));
                        break;
                    case 'max':
                        $verbose[] = str_replace(['{label}', '{max}'], [$attributes['label'], $value], Configuration\Message::getLabel('max'));
                        break;
                    case 'minlength':
                        $verbose[] = str_replace(['{label}', '{minlength}'], [$attributes['label'], $value], Configuration\Message::getLabel('minlength'));
                        break;
                    case 'maxlength':
                        $verbose[] = str_replace(['{label}', '{maxlength}'], [$attributes['label'], $value], \PHPBook\Validation\Configuration\Message::getLabel('maxlength'));
                        break;
                    case 'options':
                        $verbose[] = str_replace(['{label}', '{options}'], [$attributes['label'], implode(', ', $value)], \PHPBook\Validation\Configuration\Message::getLabel('options'));
                        break;
                    case 'mimes':
                        $verbose[] = str_replace(['{label}', '{mimes}'], [$attributes['label'], implode(', ', $value)], \PHPBook\Validation\Configuration\Message::getLabel('mimes'));
                        break;
                    case 'maxkbs':
                        $verbose[] = str_replace(['{label}', '{maxkbs}'], [$attributes['label'], $value], \PHPBook\Validation\Configuration\Message::getLabel('maxkbs'));
                        break;
                }

            };

            return implode(', ', $verbose);

        }

        return '';

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
			case '@float':
					if (!is_numeric((string) $value)) {
						$error = 'float';
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
						$error = 'date time';
					};
					unset($datetime);
				break;
			case '@time':
					$time = \DateTime::createFromFormat('H:i:s', $value);
					if ((!$time) or ($time->format('H:i:s') !== $value)) {
						$error = 'time';
					};
					unset($time);
				break;
			case '@file-buffer':
				if ((is_object($value)) or (is_array($value))) {
					$error = 'file buffer';
				};
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
			if (($value == null) or ((is_string($value)) and (strlen($value) == 0))) {
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

	private function validateFileSizeKB($sizeKb, $label, $value) {
		
		if (strlen($value) / 1024 > $sizeKb) {
			throw new \Exception(str_replace(['{label}', '{maxkbs}'], [$label, $sizeKb], Configuration\Message::getLabel('maxkbs')));
		};

	}

	private function validateFileMime($mimes, $label, $value) {

		$finfo = new \finfo(FILEINFO_MIME);

        $fileMimeType = explode(';', $finfo->buffer($value))[0];

        $fileType = explode('/', $fileMimeType)[0];

		$allows = false;

        foreach($mimes as $validationMimeType => $validationMimeTypeDescription) {
            
            if (($fileType == $validationMimeType) or ($fileMimeType == $validationMimeType)) {

                $allows = true;

            };

        };

		if (!$allows) {
			
			throw new \Exception(str_replace(['{label}', '{mimes}'], [$label, implode(', ', $mimes)], Configuration\Message::getLabel('mimes')));

		};
		
	}

	public function validate(Array $values, Array $attributes) {
		
		foreach ($this->getAttributes() as $name => $attribute) {

			if (!in_array($name, $attributes)) {

				throw new \Exception(Configuration\Message::getLabel('values:bind'));

			};

		};

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

				if (array_key_exists('mimes', $attribute)) {
	
					$this->validateFileMime($attribute['mimes'], $label, $value);
					
				};

				if (array_key_exists('maxkbs', $attribute)) {
	
					$this->validateFileSizeKB($attribute['maxkbs'], $label, $value);
					
				};
				
			};

		};

		foreach($this->getRules() as $position => $rule) {
			
			list($ruleName, $ruleAttributes, $ruleClosure) = $rule;

			$ruleValues = [];

			$valuesMapped = [];
			foreach($values as $valueParsePosition => $valueParse) {
                $valuesMapped[$attributes[$valueParsePosition]] = $valueParse;
            }

			foreach($ruleAttributes as $ruleAttribute) {


				if (!array_key_exists($ruleAttribute, $this->getAttributes())) {
					throw new \Exception(str_replace('{attribute}', $ruleAttribute, Configuration\Message::getLabel('attr:exists')));
				};

				if (array_key_exists($ruleAttribute, $valuesMapped)) {

					$ruleValues[] = $valuesMapped[$ruleAttribute];

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
