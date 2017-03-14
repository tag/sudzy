<?php
namespace Sudzy;

class ValidationException extends \Exception
{
    protected $_validationErrors;
    protected $_validationExceptions;

    public function __construct($errs, $exceptions = [])
    {
        $this->_validationExceptions = $exceptions;
        $this->_validationErrors = $errs;

        $errs = array_map (
            function($val) {
                return implode("\n", $val);
            },
            $errs
        );
        $errStr = implode("\n", $errs);
        parent::__construct($errStr);
    }

    public function getValidationErrors()
    {
        return $this->_validationErrors;
    }
    
    public function getValidationExceptions()
    {
        return $this->_validationExceptions;
    }
}
