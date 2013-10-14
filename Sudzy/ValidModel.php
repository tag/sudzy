<?php
namespace Sudzy;

abstract class ValidModel extends \Model
{
    protected $_validator        = null;    // Reference to Sudzy validator object
    protected $_validations      = array(); // Array of validations
    protected $_validationErrors = array(); // Array of error messages
    protected $_validationOptions = array(
        'throw' => self::ON_SAVE // One of self::ON_SET|ON_SAVE|NEVER. 
                                  //  + ON_SET throws immediately when field is set()
                                  //  + ON_SAVE throws on save()
                                  //  + NEVER means an exception is never thrown; check for ->getValidaionErrors()
    );

    const ON_SET   = 'set';
    const ON_SAVE  = 'save';
    const NEVER    = null;

    public function setValidationOptions($options)
    {
        $this->$_validationOptions = array_merge($this->_validationOptions, $options);
    }

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
    // * Checks, without throwing exceptions, model fields with validations
    // *
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
    * @return bool Will set a message if returning false
    **/
    public function validateField($field, $value)
    {
        $this->setupValidationEngine();

        if (!isset($this->_validations[$field])) {
            return true; // No validations, return true by default
        }

        $success = true;
        foreach ($this->_validations[$field] as $v) {
                $checks = explode(' ', $v['validations']);
                foreach ($checks as $check) {
                    $params = explode('|', $check);
                    $check  = array_shift($params);

                    if ($this->_validator->executeOne($check, $value, $params)) {
                        $success = $success && true;
                    } else {
                        $this->addValidationError($v['message']);
                        $success = false;
                    }
                }
        }
        return $success;
    }

    public function getValidationErrors()
    {
        return $this->_validationErrors;
    }

    ///////////////////
    // Overloaded methods

    /**
    * Overload __set to call validateAndSet
    */
    public function __set($name, $value)
    {
        $this->validateAndSet($name, $value);
    }

    /**
    * Overload save; checks if errors exist before saving
    */
    public function save()
    {
        if ($this->isNew()) { //Fields populated by create() or hydrate() don't pass through set()
            foreach( array_keys($this->_validations) as $field) {
                $this->validateField($field, $this->$field);
            }
        }

        $errs = $this->getValidationErrors();
        if (!empty($errs))
            $this->doValidationError(self::ON_SAVE);

        parent::save();
    }

    /**
    * Overload set; to call validateAndSet
    * // TODO: handle multiple sets if $name is a field=>val array
    */
    public function set($name, $value = null)
    {
        $this->validateAndSet($name, $value);
    }


    ////////////////////
    // Protected methods
    protected function doValidationError($context)
    {
        if ($context == $this->_validationOptions['throw'])
                throw new \ValidationException($this->_validationErrors);
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
        if (!$this->validateField($name, $value)) $this->doValidationError(self::ON_SET);
        parent::set($name, $value);
    }

    protected function setupValidationEngine()
    {
        if (null == $this->_validator) $this->_validator = new \Sudzy\Engine(); // Is lazy setup worth it?
    }
}
