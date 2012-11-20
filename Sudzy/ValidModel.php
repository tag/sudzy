<?php
namespace Sudzy;

class ValidModel extends \Model
{
    protected $_validator        = null;    // Reference to Sudzy validator object
    protected $_validations      = array(); // Array of validations
    protected $_validationErrors = array(); // Array of error messages

    public function addValidation($field, $validations, $message) {
        if (!isset($this->_validations[$field])) {
            $this->_validations[$field] = array();
        }
        $this->_validations[$field][] = array(
            'validations' => $validations,
            'message'     => $message
        );
    }

    // /**
    // * @return bool If false, running $this->doValidationError() will respond appropriately
    // */
    // public function validate()
    // {
    //     $fields = array_keys($this->_validations);
    //     $success = true;
    //     foreach ($fields as $f) {
    //         $success = $success && $this->validateField($f, $this->$f);
    //     }
    //     return $success;
    // }

    /**
    * @throws BadArumentException if field is not a valid property of the object
    * @return bool Will set a message if returning false
    **/
    public function validateField($field, $value)
    {
        if (null == $this->_validator) $this->_validator = new \Sudzy\Engine(); // Is lazy setup worth it?

        if (!isset($this->_validations[$field])) {
            if (!isset($this->$field)) {
                throw new \InvalidArgumentException("{$field} is not a valid property of object.");
            }

            return true; // No validations, return true by default
        }

        $success = true;
        foreach ($this->_validations[$field] as $v) {
                $checks = explode(' ', $v['validations']);
                $localSuccess = true;
                foreach ($checks as $check) {
                    $params = explode('|', $check);
                    $check  = array_shift($params);

                    $localSuccess = $localSuccess
                        && $this->_validator->executeOne($check, $value, $params);
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
    }

    /**
    * Overload __set to call validateAndSet
    */
    public function __set($name, $value) {
        $this->validateAndSet($name, $value);
    }

    /**
    * Overload set; to call validateAndSet
    */
    public function set($name, $value) {
        $this->validateAndSet($name, $value);
    }


    ////////////////////
    // Protected methods
    protected function doValidationError() {
        throw new ValidationException($this->_validationErrors); // TODO: Update to give option of silent failure
    }

    protected function addValidationError($msg) 
    {
        $this->_validationErrors[] = $msg;
    }

    /**
    * Overload set; to call validateAndSet
    */
    protected function validateAndSet($name, $value)
    {
        if (!$this->validateField($name, $value)) $this->doValidationError();
        parent::set($name, $value);
    }
}
