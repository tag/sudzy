<?php

class ValidationException extends \Exception
{
    protected $_validationErrors;

    public function __construct($errs)
    {
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
}