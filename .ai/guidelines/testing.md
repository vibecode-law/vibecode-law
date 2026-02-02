# Testing

These guidelines apply in addition to the boost guidelines.

## guidelines
- When writing Pest tests, avoid using `$this` for clarity. Instead, import the relevant pest function.
- Where a controller has multiple methods, use a separate test file per method. For instance `ExampleControllerIndexTest.php`.
- Where an endpoint requires authentication or permission, group related tests in an "auth" describe function.
- Do not alias AssertableInertia.
- Controller tests should include a test to validate the endpoint is returning the correct data (and no additional data to what is expected). Such tests as a general rule should therefore not use the `->etc()` method. You should test the values, not just the presence of properties. When testing the values, don't manually create the data. Use a factory with any necessary overrides, and test against that.
- When writing validation tests, where possible write a single test that accepts $data and $invalid using the pest `with()` method.
- When making assertions against a response, chain the method calls to the http function used, rather than creating a response variable with separate assertions.
- When running all tests, use ``php artisan test --compact --parallel` for speed.
- You shouldn't run migrations. The test-suite will handle this for you. If you run migrations, you may destroy data the developer is currently working with. If you need them to be run (e.g. for ide-helpers), ask whether they'd like to skip that step.
- Tests should cover both the "happy path" and "unhappy path".