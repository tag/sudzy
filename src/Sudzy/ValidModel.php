<?php
namespace Sudzy;

use Respect\Validation\Exceptions\NestedValidationException;

abstract class ValidModel extends \Model
{
    protected $_validators = [];
    protected $_validatorsLoaded = false;
    protected $_validationErrors = [];
    protected $_validationExceptions = [];

    protected $_validationOptions = [
        'indexedErrors' => false,   // If True getValidationErrors will return an array with the index
                                    // being the field name and the value an *array* of errors.

        'throw' => self::VALIDATE_ON_SAVE // One of self::ON_SET|ON_SAVE|NEVER.
                                  //  + ON_SET throws immediately when field is set()
                                  //  + ON_SAVE throws on save()
                                  //  + NEVER means an exception is never thrown; check for ->getValidationErrors()
    ];

    const VALIDATE_ON_SET   = 'set';
    const VALIDATE_ON_SAVE  = 'save';
    const VALIDATE_NEVER    = null;

    public function setValidationOptions($options)
    {
        $this->_validationOptions = array_merge($this->_validationOptions, $options);
    }

    public function getValidationOptions()
    {
        return $this->_validationOptions;
    }

    abstract public function prepareValidations();

    /**
     * @param string $prop Property name to be validated
     * @param object $validator An instance of Respect\Validation\Validator
     */
    public function setValidation($prop, $validator)
    {
        $this->lazyLoadValidations();
        if ($validator === null) {
            unset($this->_validators[$prop]);
        } else {
            $this->_validators[$prop] = $validator;
        }
    }

    /**
     * @param string $prop Property name to be validated
     * @return object $validator An instance of Respect\Validation\Validator
     */
    public function getValidation($prop)
    {
        $this->lazyLoadValidations();
        return isset($this->_validators[$prop]) ? $this->_validators[$prop] : null;
    }

    /**
     * @return array Arrany of Respect\Validation\Validator instances
     */
    public function getAllValidations()
    {
        $this->lazyLoadValidations();
        return $this->_validators;
    }

    /**
    * Manually trigger validation checking
    *
    * @return bool `true` if passes validation, otherwise,
    *         throws `Respect\Validation\Exceptions\NestedValidationException`
    */
    public function validate()
    {
        $this->lazyLoadValidations();
        foreach ($this->_validators as $key => $val) {
            $this->validateProperty($key, $this->$key);
        }
    }

    /**
    * @param string $prop Property name to be validated
    * @param mixed $value Property value to be validated
    * @param bool $throw Whether (true) to throw or a consequent validation exception or (false) return false
    * @return bool Will set a message if returning false
    * @throws Respect\Validation\Exceptions\NestedValidationException If validations fail and options permit throwing
    **/
    public function validateProperty($prop, $value, $throw = false)
    {
        $this->lazyLoadValidations();
        
        // If prop is actaully an Array by using Model->set(Array), return true to prevent warnings from unset()
        if (is_array($prop))
        {
            return true;
        }
        
        unset($this->_validationErrors[$prop]);
        unset($this->_validationExceptions[$prop]);

        if (!isset($this->_validators[$prop])) {
            return true; // No validations, return true by default
        }

        try {
            $this->_validators[$prop]->assert($value);
        } catch (NestedValidationException $validationException) {
            $this->_validationErrors[$prop] = $validationException->getMessages();
            $this->_validationExceptions[]  = $validationException;

            if (!$throw) {
                return false;
            }

            throw $validationException;
        }

        return true;
    }

    public function getValidationErrors()
    {
        return $this->_validationErrors;
    }

    public function getValidationExceptions()
    {
        return $this->_validationExceptions;
    }

    public function resetValidationErrors()
    {
        $this->_validationErrors = [];
        $this->_validationExceptions = [];
    }

    ///////////////////
    // Overloaded methods

    /**
    * Overload __set to call validateAndSet
    */
    public function __set($name, $value)
    {
        return $this->validateAndSet($name, $value);
    }

    /**
    * Overload save; checks if errors exist before saving
    */
    public function save()
    {
        if ($this->isNew()) { //Properties populated by create() or hydrate() don't pass through set()
            $this->validate(); // Check the valide of $this->getValidationErrors rather than the return value here
        }

        if (!empty($this->getValidationErrors())) {
            $this->doValidationError(self::VALIDATE_ON_SAVE);
        }

        return parent::save();
    }

    /**
    * Overload set; to call validateAndSet
    * // TODO: handle multiple sets if $name is a property=>val array
    */
    public function set($name, $value = null)
    {
        return $this->validateAndSet($name, $value);
    }

    ////////////////////
    // Protected methods

    protected function lazyLoadValidations()
    {
        if (!$this->_validatorsLoaded) {
            $this->_validatorsLoaded = true;
            $this->prepareValidations();
        }
    }

    protected function doValidationError($context)
    {
        if ($context === $this->_validationOptions['throw']) {
            throw new \Sudzy\ValidationException($this->_validationErrors, $this->_validationExceptions);
        }
    }

    /**
    * Overload set; to call validateAndSet
    * @param string $name Property name
    * @param mixed $value Property value
    */
    protected function validateAndSet($name, $value)
    {
        if (!$this->validateProperty($name, $value)) {
            $this->doValidationError(self::VALIDATE_ON_SET);
        }

        return parent::set($name, $value);
    }
}
