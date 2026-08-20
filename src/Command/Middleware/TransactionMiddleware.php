<?php

declare(strict_types=1);

namespace Componenta\CQRS\Command\Middleware;

use Componenta\CQRS\Command\Exception\TransactionBoundaryException;
use Componenta\CQRS\Command\Exception\TransactionRollbackException;
use Componenta\CQRS\Command\OperationInterface;
use Cycle\Database\DatabaseInterface;
use Throwable;

/**
 * Wraps downstream command execution in a database transaction.
 *
 * Middleware ordering is application configuration. With retry outside this
 * middleware each retry attempt gets its own transaction; with retry inside
 * this middleware all attempts share the surrounding transaction.
 */
final readonly class TransactionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private DatabaseInterface $database,
    ) {
    }

    /** @throws Throwable */
    public function execute(OperationInterface $operation, OperationHandlerInterface $handler): OperationInterface
    {
        if (!$this->database->begin()) {
            throw TransactionBoundaryException::forPhase('begin');
        }

        try {
            $operation = $handler->handle($operation);

            if (!$this->database->commit()) {
                throw TransactionBoundaryException::forPhase('commit');
            }

            return $operation;
        } catch (Throwable $e) {
            try {
                if (!$this->database->rollback()) {
                    throw TransactionBoundaryException::forPhase('rollback');
                }
            } catch (Throwable $rollbackFailure) {
                throw new TransactionRollbackException($e, $rollbackFailure);
            }

            throw $e;
        }
    }
}
