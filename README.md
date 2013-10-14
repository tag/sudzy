# Sudzy [![Build Status](https://travis-ci.org/tag/sudzy.png?branch=master)](https://travis-ci.org/tag/sudzy)
**v0.1 released.**

Sudzy is a collection of validator classes, currently intended for use with [Paris][paris]/[Idiorm][idiorm] (an active record ORM, often used with Slim), although it could be adapted easily.

Sudzy's `ValidModel` class decorates Paris' `Model` class. By extending `ValidModel`, your model classes gain immediate access to validations.

By default the `ValidModel` will store validation errors when model properties are set (for an exising model) or a new model is saved, and throw a `ValidationException` on save if errors were encountered.

[paris]: https://github.com/j4mie/paris
[idiorm]: https://github.com/j4mie/idiorm

The core validation engine contains only validation functions, which can be extended or overwritten. Validation functions return only booleans.

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
Validations are most easily set up in a model's constructor. The `addValidation()` method is passed the model field to watch, a space-separated list of validations, and an error message. Multiple calls on the same field adds additional validation checks with potentially different error messages.

```php
    // Multiple validations on the same field.
    $this->addValidation('email', 'required', 'An email address is required.');
    $this->addValidation('email', 'email',    'The provided email address is not valid.');

    // Alternative method of using mulitple validations on the same field.
    $this->addValidation('email', 'required email', 'A valid emali address is required.');
```

If validations require additional parameters (e.g., `minLength`), these are passed with a vertical bar:

```php
    $this->addValidation('password', 'minLen|6', 'Password must be at least 6 characters');
```

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
By default, Sudzy's `ValidModel` does validation checks whenever properties are set or new models are created. It can be configured to throw a `ValidationException` on `save()` (default), when properties are set, or not at all.

Validation failures are stored, and available through `getValidationErrors()`, a method of both the `ValidModel` object and the thrown `ValidationException`. An object that fails validation throws a `ValidationException` when `save()` is attempted (default behavior). This can be changed to `::ON_SET` or `::NEVER` by setting the `throw` option:

```php
$model->setValidationOptions(
    array('throw' => self::ON_SET)
);
```

Be careful of using `::ON_SET`, as Paris' internal `set()` method is not called when a model is built via Paris' `hydrate()` or `create()` methods. Also, `::ON_SET` tiggers the validation exception immediately, whereas `::ON_SAVE` permits validating all fields before throwing an exception.

Regardless of the value of `throw`, validations are checked when properties are set. In the case of new models (such as one built with Paris methods `create()` or `hydrate()`), validations are also checked on save. Regardless of when exceptions are thrown (or not), errors are immediately available through `getValidationErrors()`.

## Engine
### Validator Methods
+ `required`: Is not null or an empty string
+ `isEmail`: Results of [PHP's filter](http://php.net/manual/en/filter.filters.validate.php) using `FILTER_VALIDATE_EMAIL`; by default, permits local and UTF hostnames, so be careful.
+ `minLength`, accepts a length parameter: Implies required
+ `isInteger`: also valid for integer as a string

Validation methods may be overwritten or removed from the validation engine by using `setValidator()` and `removeValidator()` respecively.

For example, the current `isInteger` validation accepts integer strings. To enforce the `int` type as well, overwrite the `isInteger` validation:
```php
    $engine->setValidator(
        'isInteger',
        function ($val, $params) {
            return $val != 'password';
        }
    );
```

The primary difference between `addValidator()` and `setValidator()` is that the add method throws an exception if a validator of that name already exists, while set overwrites without warning.

### Custom validations
New validations may be added to the engine with `addValidator()`.

```php
    // Add new validation method
    $engine = $model->validator;
    $engine->addValidator(
        'passwordIsNotPassword',
        function ($val, $params) {
            return $val != 'password';
        }
    );

    // Use the new validation test for model fields
    $this->addValidation('password', 'passwordIsNotPassword', 'Don\'t use lame passwords.');
```
