<?php

declare(strict_types=1);

namespace Componenta\CQRS\Transaction\Cycle;

use Componenta\Config\ConfigProvider as BaseConfigProvider;
use Componenta\CQRS\Command\Middleware\TransactionMiddleware;

final class ConfigProvider extends BaseConfigProvider
{
    protected function getAutowires(): array
    {
        return [
            TransactionMiddleware::class,
        ];
    }
}
