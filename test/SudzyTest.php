<?php
use Respect\Validation\Validator as v;

class SudzyTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        // Set up the dummy database connection
        ORM::set_db(new MockPDO('sqlite::memory:'));

        // Enable logging
        ORM::configure('logging', true);
    }

    public function tearDown() {
        ORM::configure('logging', false);
        ORM::set_db(null);
    }

    public function testValidationRequired() {
        $simple = Model::factory('Simple')->find_one(1);
        $this->assertNotEmpty($simple->email);
        $simple->setValidation('email', v::notEmpty() );
        $simple->validateProperty('email', $simple->email); //Initially populated with email address
        $this->assertEmpty($simple->getValidationErrors());

        $simple->email = '';
        $this->assertNotEmpty($simple->getValidationErrors());

        try {
            $simple->save();
        } catch (\Sudzy\ValidationException $e) {
            return;
        }
        $this->fail('ValidationException expected, but not raised.');
    }

    public function testValidationIsInt() {
        $simple = Model::factory('Simple')->find_one(1);
        
        // This validation set in the Simple model
        //$simple->setValidation('age', v::intVal());

        $simple->age = 23;
        $this->assertEmpty($simple->getValidationErrors());

        $simple->age = '24';
        $this->assertEmpty($simple->getValidationErrors());

        $simple->age = '3.14159';
        $this->assertNotEmpty($simple->getValidationErrors());

        $simple->resetValidationErrors();
        $this->assertEmpty($simple->getValidationErrors());

        $simple->age = false;
        $this->assertNotEmpty($simple->getValidationErrors());

        $simple->resetValidationErrors();
        $simple->age = 'orange';
        $this->assertNotEmpty($simple->getValidationErrors());
    }

    public function testValidationIsPositiveNumeric() {
        $simple = Model::factory('Simple')->find_one(1);
        $simple->setValidation('age', v::numeric()->positive());

        $simple->age = 23;
        $this->assertEmpty($simple->getValidationErrors());

        $simple->age = '3.14159';
        $this->assertEmpty($simple->getValidationErrors());

        $simple->age = '.0314e2';
        $this->assertEmpty($simple->getValidationErrors());
        
        $simple->age = '-1';
        $this->assertNotEmpty($simple->getValidationErrors());

        // Reset errors
        $simple->resetValidationErrors();
        $this->assertEmpty($simple->getValidationErrors());

        $simple->age = false;
        $this->assertNotEmpty($simple->getValidationErrors());
    }

    public function testValidationEmail() {
        $simple = Model::factory('Simple')->find_one(1);
        $simple->setValidation('email', v::notEmpty()->email() );

        $simple->email = 'valid@example.com';
        $this->assertEmpty($simple->getValidationErrors());
        
        $simple->email = 'invalid@@example.com';  // Incorrect email
        $this->assertNotEmpty($simple->getValidationErrors());
    }

    // TODO: permit adding a null validator to overwrite existing one
    // public function testIncorrectValidator() {
    //     $simple = Model::factory('Simple')->create();
    //
    //     $simple->addValidation('age', 'notAValidation', 'Error message.');
    //     try{
    //         $simple->age = 23;
    //     } catch(InvalidArgumentException $e) {
    //         return;
    //     }
    //     $this->fail('InvalidArgumentException expected, but not raised.');
    // }

    public function testValidationOfNewModel() {
        $simple = Model::factory('Simple')->create(
            array('name'=>'Steve', 'age'=>'unknown')
        );
        
        // Default validation on age 
        
        try {
            $simple->save();
        } catch (\Sudzy\ValidationException $e) {
            return;
        }

        $this->fail('ValidationException expected, but not raised.');
    }

    public function testValidationMessageResetOnSet() {
        $simple = Model::factory('Simple')->create(
            array('name'=>'Steve', 'age'=>'0')
        );
        $simple->setValidation('age', v::intVal()->positive());

        $this->assertEmpty($simple->getValidationErrors());

        $simple->age = null;
        $this->assertNotEmpty($simple->getValidationErrors());

        $simple->age = 25;
        $this->assertEmpty($simple->getValidationErrors());
    }
}