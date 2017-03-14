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
            $this->assertNotEmpty($e->getValidationErrors());
            $this->assertNotEmpty($e->getValidationExceptions());
            $this->assertInstanceOf(
                Respect\Validation\Exceptions\NestedValidationException::class,
                $e->getValidationExceptions()[0]
            );
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

        // Test #set(), not just #__set()
        $simple->set('age', '3.14159');
        $this->assertNotEmpty($simple->getValidationErrors());
        $this->assertNotEmpty($simple->getValidationExceptions());

        $simple->resetValidationErrors();
        $this->assertEmpty($simple->getValidationErrors());

        $simple->age = false;
        $this->assertNotEmpty($simple->getValidationErrors());

        $simple->resetValidationErrors();
        $this->assertEmpty($simple->getValidationErrors());

        $simple->age = 'orange';
        $this->assertNotEmpty($simple->getValidationErrors());
    }

    //superfluous test, but useful to see an example of email validation
    public function testValidationEmail() {
        $simple = Model::factory('Simple')->find_one(1);
        $simple->setValidation('email', v::notEmpty()->email() );

        $simple->email = 'valid@example.com';
        $this->assertEmpty($simple->getValidationErrors());

        $simple->email = 'invalid@@example.com';  // Incorrect email
        $this->assertNotEmpty($simple->getValidationErrors());
    }

    public function testSuccessfulValidation() {
        $simple = Model::factory('Simple')->create(
            array('name'=>'Steve', 'age'=>'16')
        );

        $this->assertTrue(
            $simple->validateProperty('name', $simple->name)
        ); // Success, because no validation assigned to 'name'

        // Using the default validation on age

        try {
            $simple->save();
        } catch (\Sudzy\ValidationException $e) {
            $this->fail('ValidationException raised.');
        }
        // Success!
    }

    public function testValidationOfNewModel() {
        $simple = Model::factory('Simple')->create(
            ['name'=>'Steve', 'age'=>'unknown']
        );

        $options = $simple->getValidationOptions();
        $this->assertEquals($options['throw'], Sudzy\ValidModel::VALIDATE_ON_SAVE);

        // Use the default validation on age

        $this->expectException(Sudzy\ValidationException::class);
        $simple->save();

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

    public function testRemoveValidation() {
        $simple = Model::factory('Simple')->create(
            array('name'=>'Steve', 'age'=>'16')
        );
        $this->assertEmpty($simple->getValidationErrors());

        $this->assertNotEmpty($simple->getAllValidations());

        //remove the default validation on age
        $simple->setValidation('age', null);

        $this->assertNull(
            $simple->getValidation('age')
        );

        $this->assertEmpty($simple->getAllValidations());

        $simple->age = 'not a valid age';

        $this->assertEmpty($simple->getValidationErrors());

    }

    public function testGetAndOverwriteValidations() {
        $simple = Model::factory('Simple')->find_one(1);

        $validation = $simple->getValidation('email');
        $this->assertNull($validation);

        $simple->setValidation('email', v::notEmpty()->email() );

        $this->assertNotNull($simple->getValidation('email'));
    }

    public function testThrowNativeRespectException() {
        $simple = Model::factory('Simple')->create(
            array('name'=>'Steve', 'age'=>'16')
        );

        $this->expectException(Respect\Validation\Exceptions\NestedValidationException::class);

        $simple->validateProperty('age', 'not a valid age', true);
    }

    public function testNoExceptions() {
        $simple = Model::factory('Simple')->create(
            array('name'=>'Steve', 'age'=>'16')
        );

        $simple->setValidationOptions([
            'throw' => $simple::VALIDATE_NEVER
        ]);

        $simple->age = 'not a valid age';
        try {
            $simple->save();
        } catch (\Sudzy\ValidationException $e) {
            $this->fail('ValidationException raised incorrectly.');
        }
        $this->assertNotEmpty($simple->getValidationErrors());
        $this->assertNotEmpty($simple->getValidationExceptions());
    }
}