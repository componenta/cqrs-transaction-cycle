<?php

declare(strict_types=1);

use Componenta\CQRS\Command\Middleware\TransactionMiddleware;
use Componenta\DI\ContainerBuilder;
use Cycle\Database\DatabaseInterface;

it('uses DI v2 constructor autowiring without an explicit autowire section', function (): void {
    $database = $this->createStub(DatabaseInterface::class);
    $container = (new ContainerBuilder())
        ->addService(DatabaseInterface::class, $database)
        ->build();

    expect($container->make(TransactionMiddleware::class))
        ->toBeInstanceOf(TransactionMiddleware::class);
});
