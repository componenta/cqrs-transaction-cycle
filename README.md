# Componenta CQRS Transaction Cycle

Cycle Database transaction middleware for `componenta/cqrs` commands.

```bash
composer require componenta/cqrs-transaction-cycle
```

Register the provider and add `Componenta\CQRS\Command\Middleware\TransactionMiddleware` to the command middleware chain where database transactions are required.

```php
return [
    new Componenta\CQRS\ConfigProvider(),
    new Componenta\CQRS\Transaction\Cycle\ConfigProvider(),
];
```