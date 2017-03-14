<?php

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
        $simple->addValidation('email', 'required', 'An email address is required.');
        $simple->validateField('email', $simple->email); //Initially populated with email address
        $this->assertEmpty($simple->getValidationErrors());

        $simple->email = '';
        $this->assertNotEmpty($simple->getValidationErrors());

        try {
            $simple->save();
        } catch (\Validationexception $e) {
            return;
        }
        $this->fail('ValidationException expected, but not raised.');
    }

    public function testValidationIsInt() {
        $simple = Model::factory('Simple')->find_one(1);
        $simple->addValidation('age', 'isInteger', 'Must be a valid integer.');

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

    public function testValidationEmail() {
        $simple = Model::factory('Simple')->find_one(1);
        $simple->addValidation('email', 'isEmail', 'Must be a valid email.');

        $simple->email = 'valid@example.com';
        $this->assertEmpty($simple->getValidationErrors());
        $simple->email = 'invalid@@example.com';  // Incorrect email
        $this->assertNotEmpty($simple->getValidationErrors());
    }

    public function testValidationMinLenth() {
        $simple = Model::factory('Simple')->find_one(1);
        $simple->addValidation('password', 'minLength|6', 'Must be at least 6 characters.');

        $simple->password = 'password';
        $this->assertEmpty($simple->getValidationErrors());
        $simple->password = 'hello';  // Too short
        $this->assertNotEmpty($simple->getValidationErrors());
    }

    public function testIncorrectValidator() {
        $simple = Model::factory('Simple')->create();

        $simple->addValidation('age', 'notAValidation', 'Error message.');
        try{
            $simple->age = 23;
        } catch(InvalidArgumentException $e) {
            return;
        }
        $this->fail('InvalidArgumentException expected, but not raised.');
    }

    public function testValidationOfNewModel() {
        $simple = Model::factory('Simple')->create(
            array('name'=>'Steve', 'age'=>'unknown')
        );
        $simple->addValidation('age', 'isInteger', 'Age must be an integer.');

        try {
            $simple->save();
        } catch (\Validationexception $e) {
            return;
        }
        $this->fail('ValidationException expected, but not raised.');
    }

    // public function testSimpleAutoTableName() {
    //     Model::factory('Simple')->find_many();
    //     $expected = 'SELECT * FROM `simple`';
    //     $this->assertEquals($expected, ORM::get_last_query());
    // }
    // 
    // public function testFindResultSet() {
    //     $result_set = Model::factory('BookFive')->find_result_set();
    //     $this->assertInstanceOf('IdiormResultSet', $result_set);
    //     $this->assertSame(count($result_set), 5);
    // }

}
