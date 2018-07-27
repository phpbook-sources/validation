    
+ [About Validation](#about-validation)
+ [Composer Install](#composer-install)
+ [Declare Configurations](#declare-configurations)
+ [Declare Validation](#declare-validation)
+ [Use Validation](#use-validation)

### About Validation

- A PHP library for data validations

### Composer Install

	composer require phpbook/validation

### Declare Configurations

```php
<?php

/********************************************
 * 
 *  Declare Configurations
 * 
 * ******************************************/

/* the example messages below are the default messages */

\PHPBook\Validation\Configuration\Message::setLabel('values:attrs', 'The count of values and attributes must match');
\PHPBook\Validation\Configuration\Message::setLabel('attr:exists', 'The attribute alias {attribute} does not exist');
\PHPBook\Validation\Configuration\Message::setLabel('type:string', '{label} must be a string');
\PHPBook\Validation\Configuration\Message::setLabel('type:integer', '{label} must be a integer');
\PHPBook\Validation\Configuration\Message::setLabel('type:boolean', '{label} must be a boolean');
\PHPBook\Validation\Configuration\Message::setLabel('type:date', '{label} must be a date');
\PHPBook\Validation\Configuration\Message::setLabel('type:datetime', '{label} must be a datetime');
\PHPBook\Validation\Configuration\Message::setLabel('type:time', '{label} must be a time');
\PHPBook\Validation\Configuration\Message::setLabel('type:array', '{label} must be a lista of data');
\PHPBook\Validation\Configuration\Message::setLabel('type:class', '{label} is a invalid data');
\PHPBook\Validation\Configuration\Message::setLabel('required', '{label} is required.');
\PHPBook\Validation\Configuration\Message::setLabel('min', '{label} must have min value {min}');
\PHPBook\Validation\Configuration\Message::setLabel('max', '{label} must have max value {max}');
\PHPBook\Validation\Configuration\Message::setLabel('minlength', '{label} must have min {minlength} characters');
\PHPBook\Validation\Configuration\Message::setLabel('maxlength', '{label} must have max {maxlength} characters');
\PHPBook\Validation\Configuration\Message::setLabel('options', '{label} must be one of the following options, {options}');

?>
```

### Declare Validation

```php
<?php

/********************************************
 * 
 *  Declare Validation
 * 
 * ******************************************/

class CustomerValidation {

	private $layout;

	public static $STATUS_ACTIVE = 'active';

	public static $STATUS_INACTIVE = 'inactive';

	public static $STATUS_PROCESS = 'process';

	function __construct() {

		$this->layout = new \PHPBook\Validation\Layout;

		$this->layout->setAttribute('id', [
			'label' => 'Id',
			'type' => '@string',
			'uuid' => true,
			'required' => true,
		])
		
		->setAttribute('age', [
			'label' => 'Age',
			'type' => '@integer',
			'required' => true,
			'min' => 18,
			'max' => 100
		])

		->setAttribute('name', [
			'label' => 'Name',
			'type' => '@string',
			'required' => true,
			'minlength' => 5,
			'maxlength' => 120
		])
		
		->setAttribute('status', [
			'label' => 'Status',
			'type' => '@string',
			'required' => false,
			'options' => [static::$STATUS_ACTIVE => 'Active', static::$STATUS_INACTIVE => 'Inactive', static::$STATUS_PROCESS => 'Process']
		])
		
		->setAttribute('friend',  [
			'label' => 'Friend',
			'type' => '\App\Module\Friend\Entity',
			'required' => false,
		])

		->setAttribute('friends', [
			'label' => 'Friends',
			'type' => '@array:\App\Module\Friend\Entity',
		])

		->setAttribute('numbers', [
			'label' => 'Numbers',
			'type' => '@array:@integer',
		])

		->setAttribute('active', [
			'label' => 'Ativo',
			'type' => '@boolean',
			'required' => true
		])

		->setAttribute('date', [
			'label' => 'Date',
			'type' => '@date',
			'required' => true,
		])

		->setAttribute('datetime', [
			'label' => 'Date Time',
			'type' => '@datetime',
			'required' => true
		])

		->setAttribute('time', [
			'label' => 'Time',
			'type' => '@time',
			'required' => true
		])

		->setRule('ageOfJhon', ['name', 'age'], function($name, $age) {
			
			/* rules validations are called after the attributes validation */
			/* rules are always called in validation, even if you dont call any of rules parameters */
			if (($name) and ($name == 'jhon')) {
				if (($age) and ($age < 18)) {
					/* you should throw exception like the basic validation does */
					throw new Exception('Jhon must be 18 years old or more');
				};
			};

		})

		->setRule('anaCantBeHere', ['name'], function($name) {

			/* rules validations are called after the attributes validation */
			/* rules are always called in validation, even if you dont call any of rules parameters */
			if (($name) and ($name == 'ana')) {
				/* you should throw exception like the basic validation does */
				throw new Exception('What are you doing here Ana?');
			};

		})

		->setRule('statuRequiredForJhon', ['name', 'status'], function($name, $status) {

			/* rules validations are called after the attributes validation */
			/* rules are always called in validation, even if you dont call any of rules parameters */
			if (($name) and ($name == 'jhon')) {
				if (!$status) {
					/* you should throw exception like the basic validation does */
					throw new Exception('Status is required for Jhon');
				};
			};

		});

	}

	public function getLayout(): \PHPBook\Validation\Layout {

		return $this->layout;

	}

}

?>
```

### Use Validation

```php
<?php

/********************************************
 * 
 *  Use Validation
 * 
 * ******************************************/

class Customer {
	
	function __construct($name, $age) {		
	
		/* the validator throws exception when there is an error and variables list will not be returned */

		/* validator uses only the defined parameters to validate, the others will be ignored, unless you have a rule validation for completing the information */

		/* Validator provides a uuid attribute option, but the variable value must be null to generate the uuid */

		$customerValidation = new CustomerValidation;

		try {

			list($this->id, $this->name, $this->age) = $customerValidation->getLayout()->validate([null, $name, $age], ['id', 'name', 'age']);

		} catch(\Exception $e) {

			echo $e->getMessage();

		};

	}

	public function getName() {
		return $this->name;
	}

	public function getAge() {
		return $this->age;
	}

}

/* You can iterate the attributes */

$customerValidation = new CustomerValidation;

$attributes = $customerValidation->getLayout()->getAttributes();

foreach($attributes as $name => $attribute) {

	$name; //age

	$attribute; //['type' => '@integer', 'required' => true]
	
};


/* You can iterate the rules */

$customerValidation = new CustomerValidation;

$rules = $customerValidation->getLayout()->getRules();

foreach($rules as $key => $rule) {

	list($name, $attributes, $closure) = $rule;
	
};

?>
```
- Validation of date, datetime and time follows the international format YYYY-MM-DD H:i:s | YYYY-MM-DD | H:i:s.