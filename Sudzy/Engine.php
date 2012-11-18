<?php

namespace Sudzy;

class Engine
{
    /**
    * Validation methods are stored here so they can easily be overwritten
    */
    protected $_checks;

    public function __construct()
    {
        $this->_checks = array(
            'required'  => array($this, '_required'),
            'minLength' => array($this, '_minLength')
        );
    }

    public function __call($name, $args) 
    {
        if (!isset($this->_checks[$name])) 
            throw new \InvalidArgumentException("{$name} is not a valid validation function.");
        //return; // TODO: Throw error if missing?
        $val = array_shift($args);
        call_user_func($this->_checks[$name], $val, $args); //__NAMESPACE__.'\Engine::'.
    }

    public function executeOne($check, $val, $params=array())
    {
        return $this->$check($val, $params);
    }

    /**
    * @param string label used to call function
    * @param Callable function with params (value, additional params as array)
    */
    public function addValidator($label, $function)
    {
        if (isset($this->_checks[$label])) throw Exception();
        $this->setValidator($label, $function);
    }

    public function setValidator($label, $function)
    {
        $this->_checks[$label] = $function;
    }

    public function removeValidator($label)
    {
        unset($this->_checks[$label]);
    }

    /**
    * @return string The list of usable validator methods
    */
    public function getValidators()
    {
        return array_keys($this->checks);
    }

    ///// Validator methods
    protected function _required($val, $params=array())
    {
        return (($val !== null) && ('' !== trim($val)));
    }

    protected function _minLength($val, $params)
    {
        $len = array_shift($params);
        return strlen($val)>=$len;
    }
}
