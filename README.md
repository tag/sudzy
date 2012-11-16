**Currently an untested work-in-progress.**

Sudzy is a collection of validator classes, currently intended for use with Paris/Idiorm (an active record ORM used with Slim), although is could be adapted easily.

In general, exceptions are avoided. The core validation engine contains only validation functions, which can be extended or overwritten. Validation functions return only booleans.

Validation failures are stored, and available through ::getValidationErrors(), via the object that connects to the engine. The ORM model is wrapped, so validation occurs naurally when a save() is attempted.

Future development will better separate ValidModel and the validation methods, to potentially enable use cases with other ORMs or independent of an ORM.

# Example

```php
    _construct()
    {
        // Add new validation methods
        $this->validator->addValidator('custom_trimmed', function ($val, $params) {return trim($val) === $val;});

        // Add validation tests for model fields
        $this->addValidation('email', 'required email', 'A valid email address is required');
        $this->addValidation('password', 'minLen|6', 'Password must be at least 6 characters');
    }
```
