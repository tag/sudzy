Although intended for use with Paris/Idiorm, an active record ORM used with Slim.

In general, exceptions are avoided. The core validation engine contains only validation functions, which can be extended or overwritten. Validation functions return only booleans.

Validation failures are logged, and available through ::getMessages(), via the object that wraps the engine. The ORM model is wrapped, so validation occurs naurally when a save() is attempted.

Future development will better separate ValidModel and the validation methods.