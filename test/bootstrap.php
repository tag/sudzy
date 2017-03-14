<?php

date_default_timezone_set('UTC'); // Avoid ugly warnings w/ phpunit

require     __DIR__ . '/../vendor/autoload.php';

/**
 * Mock version of the PDOStatement class.
 */
class MockPDOStatement extends PDOStatement {

   private $current_row = 0;

   public function __construct() {}
   public function execute($params = null) {}

   /**
    * Return some dummy data
    */
   //TODO: Needed?
   public function fetch($fetch_style=PDO::FETCH_BOTH, $cursor_orientation=PDO::FETCH_ORI_NEXT, $cursor_offset=0) {
       if ($this->current_row == 5) {
           return false;
       } else {
           return array('name' => 'Fred', 'email' => 'jim@example.com', 'age'=>34, 'id' => ++$this->current_row);
       }
   }
}

/**
 * Mock database class implementing a subset
 * of the PDO API.
 */
class MockPDO extends PDO {

   /**
    * Return a dummy PDO statement
    */
   public function prepare($statement, $driver_options=array()) {
       return new MockPDOStatement($statement);
   }
}

/**
 * Models for use during testing
 */
class Simple extends \Sudzy\ValidModel {
    public function prepareValidations()
    {
        $this->setValidation('age', \Respect\Validation\Validator::intVal());

    }
}