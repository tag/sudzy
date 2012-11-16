<?php
namespace Sudzy;

abstract class ValidModel extends \Slim\Model
{
    protected $_validator        = null;    // Reference to Sudzy validator object
    protected $_validations      = array(); // Array of validations
    protected $_validationErrors = array(); // Array of error messages

    abstract public function prepareValidations($arr); //MUST call $this->prepareValidationEngine();

    public function addValidation($field, $validations, $message) {
        if (!isset($this->_validations[$field])) {
            $this->_validations[$field] = array();
        }
        $this->_validations[$field][] = array(
            'validations' => $validations,
            'message'     => $message
        );
    }

    /**
    * @return bool If false, running $this->doValidationError() will respond appropriately
    */
    public function validate()
    {
        $fields = array_keys($this->validations);
        $success = true;
        foreach ($fields as $f) {
            $success = $success && $this->validateField($f);
        }
        reurn $success;
    }

    /**
    * @return bool Will set a message if returning false
    **/
    public function validateField($field)
    {
        if (null == $this->_validator) $this->_validator = new \Sudzy\Engine(); // Is lazy setup worth it?

        if (!isset($this->_validations[$field])) {
            if (!isset($this->$field)) {
                throw new BadArgumentException("{$field} is not a valid property of object.");
            }
            return true; // No validations, return true
        }

        $success = true;
        foreach ($this->_validations[$field] as $v) {
                $checks = explode(' ', $v['validation']);
                $localSuccess = true;
                foreach ($checks as $check) {
                    $params = explode('|', $check);
                    $check  = array_shift($params);
                    $localSuccess = $localSuccess
                        && $this->_validator->executeOne($check, $this->$field, $params) {
                }
                if (!$localSuccess) {
                    $this->addValidationError($v['message']);
                }
                $success = $success && $localSuccess;
        }
        return $success;
    }

    public function getValidationErrors()
    {
        return $this->_validationErrors;
        //return $this->_validator->getErrors();
    }

    /**
    * Overrides parent::save
    */
    public function save()
    {
        if (!empty($this->_validations)) {
            if (!$this->validate()) {
                //$this->doValidationError();
                throw new ValidationException();
                return false;
        }
        parent::save();
    }

    ////////////////////
    // Protected methods

    protected function addValidationError($msg) {
        $this->_validationErrors[] = $msg;
    }
}




$this->validator->addValidator('custom1', function ($f) {return trim($f) === trim($field2);});

$this->addValidation('email', 'required email', 'A valid email address is required');
$this->addValidation('password', 'minLen|6', 'Password must be at least 6 characters');

// $this->validator->prepare(array(
//  'field1' => 'required minChars(5)',
//  'field2' => array(checks=>'required email', 'name'=>'Email Address'),
//  'field2' => 'custom1'
// ));

