<?php namespace PHPBook\Validation\Configuration;

abstract class Message {

	private static $labels = [
        'values:bind'   => 'You must set all parameters to validate',
        'values:attrs'  => 'The count of values and attributes must match to validate',
        'attr:exists'   => 'The attribute alias {attribute} does not exist',
        'type:string'   => '{label} must be a string',
        'type:integer'  => '{label} must be a integer',
	    'type:digits'   => '{label} must be only digits',
        'type:float'    => '{label} must be a float',
        'type:boolean'  => '{label} must be a boolean',
        'type:date'     => '{label} must be a date',
        'type:datetime' => '{label} must be a date time',
        'type:time'     => '{label} must be a time',
        'type:array'    => '{label} is contains a invalid data',
        'type:class'    => '{label} is a invalid data',
        'required'      => '{label} is required.',
        'min'           => '{label} needs min value {min}',
        'max'           => '{label} needs max value {max}',
        'minlength'     => '{label} needs min {minlength} characteres',
        'maxlength'     => '{label} needs max {maxlength} characteres',
        'options'       => '{label} must be one of the following options, {options}',
        'mimes'         => '{label} must be a file type {mimes}',
        'maxkbs'        => '{label} max kb size is {maxkbs}'
    ];

	public static function setLabel(String $name, String $label) {
        if (array_key_exists($name, static::$labels)) {
            static::$labels[$name] = $label;
        };
	}

    public static function getLabel($name): String {
		return (array_key_exists($name, static::$labels)) ? static::$labels[$name] : '';
    }
    
    public function getLabels(): Array {
		return static::$labels;
	}

}
