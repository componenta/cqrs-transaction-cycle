# Componenta CQRS Transaction Cycle

Cycle Database transaction middleware for `componenta/cqrs` commands. The current package supports CQRS 2.x, 3.x, and the current CQRS v4 line.

```bash
composer require componenta/cqrs-transaction-cycle
```

The package has no ConfigProvider. `TransactionMiddleware` is constructor-autowireable; register `Cycle\Database\DatabaseInterface` in your container and add `Componenta\CQRS\Command\Middleware\TransactionMiddleware` to `ConfigKey::COMMAND_MIDDLEWARES` where command transactions are required.

Middleware ordering is application configuration. With retry outside transaction:

```text
RetryMiddleware
  TransactionMiddleware
    handler
```

each retry attempt gets its own `begin -> rollback/commit` boundary. With transaction outside retry:

```text
TransactionMiddleware
  RetryMiddleware
    handler
```

all retry attempts run inside one surrounding transaction. The package does not reject either topology; applications choose the transaction scope they need and should account for how failed attempts affect that scope.

Policy placement is likewise application-defined. Putting policy outside transaction avoids opening a transaction for commands that authorization rejects; putting policy inside transaction deliberately includes authorization in the transaction boundary.

Example:

```php
use Componenta\CQRS\Command\Middleware\TransactionMiddleware;
use Componenta\CQRS\ConfigKey;

return [
    ConfigKey::COMMAND_MIDDLEWARES => [
        TransactionMiddleware::class,
    ],
];
```
