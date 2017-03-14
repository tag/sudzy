# Sudzy [![Build Status](https://travis-ci.org/tag/sudzy.png?branch=master)](https://travis-ci.org/tag/sudzy)
** Documentaiton is out of date **
** Breaking change: Use `respect/validation` validation library. See code for details. **

Sudzy is a collection of validator classes, currently intended for use with [Paris][paris]/[Idiorm][idiorm] (an active record ORM, often used with Slim), although it could be adapted easily.

Sudzy's `ValidModel` class decorates Paris' `Model` class. By extending `ValidModel`, your model classes gain immediate access to validations.

By default the `ValidModel` will store validation errors when model properties are set (for an exising model) or a new model is saved, and throw a `ValidationException` on save if errors were encountered.

[paris]: https://github.com/j4mie/paris
[idiorm]: https://github.com/j4mie/idiorm

Sudzy's `ValidModel` class uses `Respect/Validation` as its validation engine. See that project for details.

### Installation
The easiest way to install Sudzy is via [Composer][composer]. Start by creating or adding to your project's `composer.json` file:

```js
    {
        "require": {
            "tag/sudzy" : "dev-master" // Grab the most recent version from github
        }
    }
```

[composer]: http://getcomposer.org

## ValidModel Example
The `ValidModel` class requires you to implement the abstract method 
`::prepareValidations()`, in order to lazily load the validations. Thus, 
constructors will not have the overhead of creating unused validation objects. 

Validations can also be added at any time with the `::addValidation()` method.

The `::setValidation()` method is passed the model property to watch, and a 
Respect validation object to be checked against. Multiple calls on the same 
property overwrite previous validations.

`Respect\Validation` is namespaced, but you can make your life easier by importing a single class into your context:

```php
    use Respect\Validation\Validator as v;
```

```php
    // Within a `ValidModel` class declaration:
    
    public function prepareValidation()
    {
        $this->setValidation('username', v::alnum()->noWhitespace()->length(1, 15) );
        $this->setValidation('email', v::email() );
        $this->setValidation('password', v::stringType()->length(6, null)->length(1, 15) );
        $this->setValidation('birthdate', v::date()->age(18));

    }
```
When using `Respect\Validation`, create different validations for each field, instead of a single validator for the entire object.

### Full Example
```php
class User extends \Sudzy\ValidModel
{
    public function __construct()
    {
        // Add new validation methods
        $this->validator->addValidator('customIsTrimmed', function ($val, $params) {return trim($val) === $val;});

        // Add validation tests for model fields
        $this->addValidation('email', 'required email', 'A valid email address is required.');
        $this->addValidation('password', 'minLen|6', 'Password must be at least 6 characters.');
        $this->addValidation('email', 'customIsTrimmed', 'Password must be at least 6 characters.');
    }
}
```

### Validation Exceptions and Errors
By default, Sudzy's `ValidModel` does validation checks whenever objects are
committed to the database via `::save()`, but can be configured to throw an 
exception when properties are set, or not at all.

Because an object can have multiple fields fail, it is necessary to catch and wrap Respect's exceptions.

:TODO:

Validation failures are stored, and available through `getValidationErrors()`, a method of both the `ValidModel` object and the thrown `ValidationException`. An object that fails validation throws a `ValidationException` when `save()` is attempted (default behavior). This can be changed to `::ON_SET` or `::NEVER` by setting the `throw` option:

```php
$model->setValidationOptions(
    array('throw' => self::ON_SET)
);
```

Be careful of using `::ON_SET`, as Paris' internal `set()` method is not called
when a model is built via Paris' `hydrate()` or `create()` methods. Also, 
`::ON_SET` tiggers the validation exception immediately, whereas `::ON_SAVE` 
permits validating all fields before throwing an exception.

Regardless of the value of the `throw` option, validations are checked when 
properties are set. In the case of new models (such as one built with Paris 
methods `create()` or `hydrate()`), validations are also checked on save. 
Regardless of when exceptions are thrown (or not), errors are immediately 
available through `getValidationErrors()`.

