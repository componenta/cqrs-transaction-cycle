<?php

declare(strict_types=1);

use Componenta\Config\ConfigKey as DependencyConfigKey;
use Componenta\CQRS\Command\Middleware\TransactionMiddleware;
use Componenta\CQRS\Transaction\Cycle\ConfigProvider;

it('registers cycle transaction middleware autowire', function (): void {
    $config = (new ConfigProvider())();
    $autowires = $config[DependencyConfigKey::DEPENDENCIES][DependencyConfigKey::AUTOWIRES];

    expect($autowires)->toContain(TransactionMiddleware::class);
});
