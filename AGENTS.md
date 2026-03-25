# AGENTS.md - Http Response Validator Bundle

## Architecture Overview
This Symfony bundle validates HTTP responses using a Result monad and handler pipelines. Core flow: `Result::success($response)->bind($handler)->bind(...)->getOrThrow()`. Handlers process sequentially, failing fast on errors. Key components in `src/`:
- `Monad/`: Result monad (Success/Failure) for error handling
- `Handler/`: Pipeline steps (e.g., status check, JSON extraction, structure validation)
- `Validator/`: Standalone array validators
- `HttpLogger/`: Response logging with sensitive data masking

## Handler Patterns
Handlers implement `Contract/HandlerInterface` and extend `AbstractHandler` for logging. Use fluent config (e.g., `->setExpectedStatus(200)->addSensitiveKeys(['token'])`). Register via DI tag `zjkiza.http_response_validate.handler_factory` in `src/Resources/config/services.yaml`. Example custom handler in README.md.

## Error Handling
Failures logged with unique `Message ID=<hex>` via PSR-3 logger. `AbstractHandler::fail()` adds backtrace and overrides exception message. Result monad binds only on Success; Failure short-circuits.

## Testing & CI
Run `composer phpunit` for tests with coverage in `build/phpunit/`. CI via `bin/ci.py` in Docker: installs deps, runs phpunit-ci, phpstan, psalm, phpmd. Use `bin/run.py` to start dev container. Config in `docker-compose.yaml`, commands in `bin/config.py`.

## Code Style
Strict PHP 8.2+, PSR-4 autoload. Use `composer phpstan`, `composer psalm`, `composer rector`, `composer php-cs-fixer`. Validators in `Validator/` use `ErrorCollector` for multiple errors. Functions in `src/functions.php` (e.g., `addIdInMessage`).

## Dependencies
Symfony 5-7, PSR/log. Bundle auto-wires in `DependencyInjection/Extension.php`. Handlers injected via `HandlerFactory` (tagged services).
