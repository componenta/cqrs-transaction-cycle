# Componenta CQRS Transaction Cycle

Cycle Database transaction middleware for `componenta/cqrs` v4 commands. `main` is the transaction v3 line.

```bash
composer require componenta/cqrs-transaction-cycle
```

The package has no ConfigProvider. `TransactionMiddleware` is constructor-autowireable; register `Cycle\Database\DatabaseInterface` in your container and add `Componenta\CQRS\Command\Middleware\TransactionMiddleware` to `ConfigKey::COMMAND_MIDDLEWARES` where command transactions are required.

CQRS v4 validates the transaction ordering contract before compiling the command pipeline. When the corresponding middleware are present, the order is:

```text
PolicyMiddleware
  RetryMiddleware
    TransactionMiddleware
      handler
```

Policy must run before a database transaction is opened. Retry must wrap transaction so every retry attempt gets its own `begin -> rollback/commit` boundary. Configuring `TransactionMiddleware -> RetryMiddleware` fails immediately instead of allowing writes from a failed attempt to remain in a transaction later committed by a successful attempt.

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

If policy or retry middleware are installed, the hard `MiddlewareOrder` contract ensures they appear before `TransactionMiddleware`.
