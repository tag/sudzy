<?php

// namespace Sudzy;
// 
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