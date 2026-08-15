<?php

declare(strict_types=1);

namespace Componenta\CQRS\Command\Exception;

use RuntimeException;
use Throwable;

final class TransactionRollbackException extends RuntimeException
{
    public function __construct(
        public readonly Throwable $primaryFailure,
        public readonly Throwable $rollbackFailure,
    ) {
        parent::__construct(
            sprintf(
                'Transaction failed with "%s" and rollback also failed with "%s".',
                $primaryFailure->getMessage(),
                $rollbackFailure->getMessage(),
            ),
            previous: $primaryFailure,
        );
    }
}
