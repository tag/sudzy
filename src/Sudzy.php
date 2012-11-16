<?php

namespace Sudzy;

// class Sudzy
// {
//     protected $_options = array (
//         'throw'     => false,
// //      'checkAll'  => 'true',  // false: fail on first
//         'engine'    => __NAMESPACE__.'\\Engine'
//     );
// 
//     protected $_errors = array();
// 
//     protected $engine;
// 
//     __construct($options = array()) {
//         $this->setOptions($options);
//     }
// 
//     public function setOptions($options) {
//         $this->_options = array_merge($this->_options,  $options);
//     }
// 
//     /**
//     *
//     * @param array of validations, where each validation is [check, value, array of additional parameters, optional error message]
//     */
//     public function execute($arr) {
//         $this->_errors = array();
//         foreach ($arr as $validation) {
//             $check = array_shift($validation);
//             $value = array_shift($validation);
// 
//             if (!$this->engine->executeOne($check, $value, $params=array())) {
//                 $this->_errors[] = $this->prepareMessage($check, $value, $params) ;
//                 if (!$this->_options['checkAll']) {
//                     if ($this->_options['throw']) throw new ValidationException($msg);
//                     return false;
//                 }
//             }
//         }
//         return empty($this->_errors);
//     }
// }

class Engine
{
    protected $_checks = array( //Validation methods are stored here so they can easily be overwritten
        'required' => array($this, '_required')
    );

    public __call($name, $args) {
        if (!isset($this->_checks[$name])) return; // TODO: Throw error if missing?
        $val = array_shift($args);
        call_user_func(__NAMESPACE__.'\Engine::'.$this->_checks[$name], $val, $args);
    }

    public function executeOne($check, $val, $params=array()) {
        return $this->$check($val, $params);
    }

    public function addValidator($label, $function) {
        if (isset($this->_checks[$label])) throw Exception();
        $this->setValidator($label, $function);
    }

    public function setValidator($label, $function) {
        $this->_checks[$label] = $function;
    }

    public function removeValidator($label) {
        unset($this->_checks[$label]);
    }

    /**
    * @return string The list of usable validator methods
    */
    public function getValidators() {
        return array_keys($this->checks);
    }

    ///// Validator methods
    protected function _required($val, $params=array()) {
        return $val !=== null && trim($val)!==="";
    }
}

