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

    /**
    * @return bool If false, running $this->doValidationError() will respond appropriately
    */
    public function validate()
    {
        $fields = array_keys($this->_validations);
        $success = true;
        foreach ($fields as $f) {
            $success = $success && $this->validateField($f);
        }
        return $success;
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
                        && $this->_validator->executeOne($check, $this->$field, $params);
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
        if (!$this->validate()) {
            //throw new ValidationException(); // TODO: Add custome exception
            throw new Exception();
            return false;
        }
        parent::save();
    }

    ////////////////////
    // Protected methods

    protected function addValidationError($msg) 
    {
        $this->_validationErrors[] = $msg;
    }
}
