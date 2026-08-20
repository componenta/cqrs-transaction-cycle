# Componenta CQRS Transaction Cycle

Cycle Database transaction middleware for `componenta/cqrs` commands.

```bash
composer require componenta/cqrs-transaction-cycle
```

The package has no ConfigProvider. `TransactionMiddleware` is constructor-autowireable; register `Cycle\Database\DatabaseInterface` in your container and add `Componenta\CQRS\Command\Middleware\TransactionMiddleware` to `ConfigKey::COMMAND_MIDDLEWARES` where command transactions are required.

When retry middleware is also enabled, the safe order is:

```text
RetryMiddleware
  TransactionMiddleware
    handler
```

`RetryMiddleware` must wrap `TransactionMiddleware`, so every retry attempt gets its own `begin -> rollback/commit` boundary. The reverse order (`TransactionMiddleware -> RetryMiddleware`) is unsafe: a retryable failure can be caught by RetryMiddleware before it reaches the transaction boundary, leaving writes from the failed attempt inside the transaction that is later committed by a successful attempt.

Example configuration:

```php
use Componenta\CQRS\Command\Middleware\TransactionMiddleware;
use Componenta\CQRS\ConfigKey;

return [
    ConfigKey::COMMAND_MIDDLEWARES => [
        TransactionMiddleware::class,
    ],
];
```

If retry is used, place its service ID before `TransactionMiddleware::class` in the command middleware list.
