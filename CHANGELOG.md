# Changelog

All notable changes to this project will be documented in this file.

## [0.11.0]
- Improved PhpUnitTool to support more flexible testing of handlers, including better assertion capabilities and enhanced error reporting.
- Added new methods to PhpUnitTool for easier setup and execution of handler tests, allowing developers to write more comprehensive test cases with less boilerplate code. `ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait::assertArrayStructureAndValues`, `ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait::assertArrayStrictStructureAndValues`
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