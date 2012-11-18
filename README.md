# Sudzy
**Currently an evolving work-in-progress.**

Sudzy is a collection of validator classes, currently intended for use with Paris/Idiorm (an active record ORM used with Slim), although it could be adapted easily.

The core validation engine contains only validation functions, which can be extended or overwritten. Validation functions return only booleans.

When wrapping the ORM by extending its `Model` class, Sudzy's `ValidModel` does validation checks whenever properties are set. A failed validation throws a `ValidationError`.

Validation failures are stored, and available through `::getValidationErrors()`, a method of both the `ValidModel` object and the thrown `ValidationException`.

Future development will better separate `ValidModel` and the methods that invoke the engine, to potentially enable use cases with other ORMs or independent of an ORM.

## Example
Validations are set up in the model's constructor. The `addValidation()` method is passed the model field to watch, a space-separated list of validations, and an error message. Multiple calls on the same field adds additional validation checks with potentially different error messages.

```php
    // Multiple validations on the same field.
    $this->addValidation('email', 'required', 'An email address is required.');
    $this->addValidation('email', 'email',    'The provided email address is not valid.');

    // Alternative method of using mulitple validations on the same field.
    $this->addValidation('email', 'required email', 'A valid emali address is required.');
```

If validations require additional parameters (e.g., minLength), these are passed with a vertical bar:

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
    }
}
```
## Validator Methods
+ required
+ email
+ minLength accepts a length parameter; Implies required.
