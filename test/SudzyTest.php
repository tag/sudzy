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
        } catch (ValidationException $e) {
            return;
        }
        $this->fail('ValidationException expected, but not raised.');
    }

    public function testValidationEmail() {
        $simple = Model::factory('Simple')->find_one(1);
        $simple->addValidation('email', 'isEmail', 'Must be a valid email.');

        $this->email = 'valid@example.com';
        $this->assertEmpty($simple->getValidationErrors());
        $this->email = 'invalid@^&example.com';
        echo 'EMAIL:' . (filter_var($this->email, FILTER_VALIDATE_EMAIL)?:'false');
        var_dump($simple->getValidationErrors());
        $this->assertNotEmpty($simple->getValidationErrors());
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