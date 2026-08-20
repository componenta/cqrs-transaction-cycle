# Componenta CQRS Transaction Cycle

Cycle Database transaction middleware for `componenta/cqrs` v4 commands. `main` is the transaction v3 line.

```bash
composer require componenta/cqrs-transaction-cycle
```

The package has no ConfigProvider. `TransactionMiddleware` is constructor-autowireable; register `Cycle\Database\DatabaseInterface` in your container and add `Componenta\CQRS\Command\Middleware\TransactionMiddleware` to `ConfigKey::COMMAND_MIDDLEWARES` where command transactions are required.

With the official policy and retry middleware the execution order is:

```text
PolicyMiddleware
  RetryMiddleware
    TransactionMiddleware
      handler
```

`TransactionMiddleware` owns the hard invariant that policy runs before opening a database transaction. `RetryMiddleware` owns the complementary cross-package invariant that retry wraps transaction, so every retry attempt gets its own `begin -> rollback/commit` boundary. The same relation is intentionally declared in one place only.

Configuring `TransactionMiddleware -> RetryMiddleware` fails through retry's `MiddlewareOrder` constraint instead of allowing writes from a failed attempt to remain in a transaction later committed by a successful attempt.

Example without retry:

```php
use Componenta\CQRS\Command\Middleware\TransactionMiddleware;
use Componenta\CQRS\ConfigKey;

return [
    ConfigKey::COMMAND_MIDDLEWARES => [
        TransactionMiddleware::class,
    ],
];
```
