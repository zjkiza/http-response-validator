# Changelog

All notable changes to this project will be documented in this file.

## [0.13.0]
Added
- New exceptions thrown by `HttpResponseLoggerHandler` for an unexpected status code: `HttpBadRequestException` (400), `HttpUnauthorizedException` (401), `HttpForbiddenException` (403) and `HttpNotFoundException` (404). They extend the matching Symfony `*HttpException` classes (so existing `catch` blocks keep working) and implement the new `HttpResponseExceptionInterface`, which extends the bundle marker `ExceptionInterface`.
- `HttpResponseExceptionInterface::getMessageId()` (`Message ID=<hex>`) and `getRequestFailed()` (the data logged under the `http_request_failed` key).
- `HttpResponseLoggerHandler::addSensitiveValues()` masks literal values (e.g. a token placed in the URL path) in the logged and exception URL and body. The values are not stored between calls.

Changed
- The exception message now contains what is written to the log: `Message ID=<hex> : [HttpRequestLogger ERROR CODE] Unexpected status code 400 expected 200 | GET <url> | Response body: <body>`, so the cause is visible in an error tracker (e.g. Sentry) without searching the logs. The body is shortened to 2000 characters. Statuses without a dedicated exception still throw `RuntimeException`, with the same message format.
- Previously the 4xx exceptions did not implement `ExceptionInterface`, so code that wraps everything that is not a bundle exception generated a second, different `Message ID`. Now there is a single ID in the log and in the exception.
- Query parameters named in the sensitive keys (`password`, `token`, `apiKey` and the ones added with `addSensitiveKeys()`) are masked in the logged URL.

Fixed
- `AbstractHandler::fail()` no longer substitutes the "run place" inside the response body part of the message (e.g. `"errors": ["x"]`).

Security
- The exception message now includes the request URL and the response body. Do not show `$exception->getMessage()` to end users; show only `getMessageId()`.

## [0.12.3]
Added
- Added Symfony 8 support: `symfony/config`, `symfony/dependency-injection` and `symfony/http-kernel` now allow `^8.0`.
- Added GitHub Actions workflow (`.github/workflows/ci.yml`) that runs PHPUnit, PHPStan and Psalm (the same checks as `bin/ci.py`) on a matrix of PHP 8.2 - 8.5 and Symfony 5.4, 6.4, 7.4 and 8.1, plus one job with the lowest allowed dependencies (`--prefer-lowest`).
- Added a CI job that validates `composer.json`, runs `composer audit` and checks the code style with PHP CS Fixer.
- Added `symfony/property-info` to `require-dev`, because the test kernel enables the `property_info` framework option.

Changed
- Widened development dependency constraints so the test suite can be installed on every supported platform:
  - `symfony/framework-bundle`, `symfony/yaml` and `symfony/http-client` accept `^5.4 || ^6.4 || ^7.0 || ^8.0` (`framework-bundle` `^7.3`),
  - `phpunit/phpunit` accepts `^11.5 || ^12.4` (PHPUnit 12 requires PHP 8.3, the bundle supports PHP 8.2),
  - `phpstan/phpstan` requires at least `^2.1.5` (older versions report a false positive in `Result::success()` on the lowest dependencies).
- Psalm: `DeprecatedInterface` is reported only as info, because Symfony 8.1 marks `BundleInterface` as deprecated.
- `bin/run.py --verbose` (and `bin/shutdown.py --verbose`) now stream the `docker-compose` output live; without `--verbose` the output is hidden and printed only when the command fails.
- `bin/utility/dockerManager.py` now runs `docker-compose` without a shell and detects running containers by exact name.

Fixed
- Tests on Symfony 5.4: `logger` service is now replaced by the test logger too, because Symfony 5.4 `LoggerPass` always points `Psr\Log\LoggerInterface` to `logger`.
- `bin/ci.py` now tears down the docker containers when a check fails, and it no longer uses `docker exec -it` without a TTY (e.g. in a CI environment).
- Docker image: `/composer` (`COMPOSER_HOME`) is now writable by the `app` user, which fixes "Cannot create cache directory /composer/cache/..." when running `composer` inside the container.

## [0.12.2]
Fixed
- Resolved all PHPStan (max level) and Psalm static analysis errors across the bundle, enabling clean type-checking for both production and test code.

## [0.12.1]
Change/fix 
- function name from ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait::arrayStructureExact to assertArrayStructureExact

## [0.12.0]
Added
- Added typed expected-type API usage across validators and handlers using:
  - `ZJKiza\HttpResponseValidator\Enum\TypeCheck`
  - `ZJKiza\HttpResponseValidator\Validator\Type\ArrayOfExpectedType`
  - `ZJKiza\HttpResponseValidator\Validator\Type\UnionExpectedType`
- Added/expanded PHPUnit helper usage for:
  - `ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait::assertArrayStructure`
  - `ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait::assertArrayStructureExact`
- Added and expanded unit tests for validator/type handling and logging components.

Optimization
- Refactored and optimized validation/type-checking flow (strategy-based handling and helper decomposition).
- Refactored HTTP logger internals into focused components while preserving public behavior.

Documentation
- Updated README with typed expected-type guidance and null/type interaction details.
- Documented legacy compatibility rules and PHPUnit helper usage.

Compatibility
- Legacy string-based type declarations remain supported for the full `0.12.x` line and are planned for removal in a future major release.

## [0.11.3]
Added
- Added non-empty string type support.
- Unit tests.
Fixed
- Error is logged after adding "Message ID".

## [0.11.2]
Added
- Throwing exception `InvalidArgumentException` if check type is not supported or entered incorrectly.
- Unit tests.
Fixed
- documentation

## [0.11.1]
Fixed
- Docker for development.
Added
- Added support for checking whether all elements of the array belong to a certain type (ex. int[], string[], float[]...).

## [0.11.0]
- Improved PhpUnitTool to support more flexible testing of handlers, including better assertion capabilities and enhanced error reporting.
- Added new methods to PhpUnitTool for easier setup and execution of handler tests, allowing developers to write more comprehensive test cases with less boilerplate code. `ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait::assertArrayStructureAndValues`, `ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait::assertArrayStrictStructureAndValues`.
- Added support for checking multiple types (ex : 'int|string') for a single parameter in the validation handlers, allowing for more flexible validation rules and better handling of cases where a parameter can be of different types (e.g., string or array). This enhancement enables developers to define more complex validation logic while still maintaining clear and informative error messages when validation fails.

## [0.10.2]
- Improved PhpUnitTool 

## [0.10.1]
Optimization
- Docker optimization for development

## [0.10.0]
Fixed:
- error when adding exception message

## [0.9.0]
Fixed:
- Relaxed `psr/log` dependency constraint to support Symfony 5.4 (`^1.1 || ^2.0 || ^3.0`)

## [0.8.0]
Fixed:
- generating only one message ID,
- creating an exception fixed problem with order (message, code, previous).

## [0.7.1]
Fixed:
- static analysis issues
- optimized code

## [0.7.0]
Added: 
- `ZJKiza\HttpResponseValidator\Handler\ArrayStructureValidateExactHandler` – validates the structure of the response using strict/exact key and type checking
- `ZJKiza\HttpResponseValidator\Handler\ArrayStructureValidateInternalHandler` – validates the structure using internal/relaxed rules for key existence and potential type checking

**!BREAKING CHANGE!**: removed 
- `ZJKiza\HttpResponseValidator\Handler\ValidateArrayKeysExistHandler`

## [0.6.0]
Added
- When ValidateArrayKeysExistHandler encounters the first error in the keys, it does not immediately throw an exception, but collects all the missing keys and only then throws the exception.

Fixed:
- remove getRequestOptions  

## [0.5.1]
Fixed:
- tag name
- documentation

## [0.5.0]
Initial version
