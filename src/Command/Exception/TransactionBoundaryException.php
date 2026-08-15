<?php

declare(strict_types=1);

namespace Componenta\CQRS\Command\Exception;

use RuntimeException;

final class TransactionBoundaryException extends RuntimeException
{
    /**
     * @param 'begin'|'commit'|'rollback' $phase
     */
    public static function forPhase(string $phase): self
    {
        return new self(sprintf(
            'Database transaction %s returned false.',
            $phase,
        ));
    }
}
