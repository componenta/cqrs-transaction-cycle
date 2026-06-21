<?php

declare(strict_types=1);

namespace Componenta\CQRS\Command\Middleware;

use Componenta\CQRS\Command\OperationInterface;
use Cycle\Database\DatabaseInterface;
use Throwable;

/**
 * Wraps command execution in a database transaction.
 *
 * Automatically commits on success and rolls back on any exception.
 *
 * Flow:
 * ```
 * begin()
 *   -> handler executes command
 *   -> success: commit()
 *   -> failure: rollback() -> rethrow exception
 * ```
 *
 * @example
 * ```php
 * $bus = new CommandBus(
 *     new HandleCommandHandler($locator),
 *     new SequentialMiddleware(),
 *     new TransactionMiddleware($database),
 *     new EventMiddleware($listeners),
 * );
 * ```
 */
final readonly class TransactionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private DatabaseInterface $database,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(OperationInterface $operation, OperationHandlerInterface $handler): OperationInterface
    {
        $this->database->begin();

        try {
            $operation = $handler->handle($operation);
            $this->database->commit();

            return $operation;
        } catch (Throwable $e) {
            $this->database->rollback();

            throw $e;
        }
    }
}