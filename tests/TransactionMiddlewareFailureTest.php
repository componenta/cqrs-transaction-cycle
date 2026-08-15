<?php

declare(strict_types=1);

use Componenta\CQRS\Command\Exception\TransactionRollbackException;
use Componenta\CQRS\Command\Middleware\OperationHandlerInterface;
use Componenta\CQRS\Command\Middleware\TransactionMiddleware;
use Componenta\CQRS\Command\Operation;
use Componenta\CQRS\Command\OperationInterface;
use Cycle\Database\DatabaseInterface;

it('preserves the primary transaction failure when rollback also fails', function (): void {
    $primary = new RuntimeException('handler failed');
    $rollback = new RuntimeException('rollback failed');
    $database = $this->createMock(DatabaseInterface::class);
    $database->expects($this->once())->method('begin')->willReturn(true);
    $database->expects($this->never())->method('commit');
    $database->expects($this->once())->method('rollback')->willThrowException($rollback);
    $handler = new class ($primary) implements OperationHandlerInterface {
        public function __construct(private readonly Throwable $failure)
        {
        }

        public function handle(OperationInterface $operation): OperationInterface
        {
            throw $this->failure;
        }
    };
    $middleware = new TransactionMiddleware($database);

    try {
        $middleware->execute(Operation::create(new stdClass()), $handler);
        test()->fail('Expected rollback failure.');
    } catch (TransactionRollbackException $exception) {
        expect($exception->primaryFailure)->toBe($primary)
            ->and($exception->rollbackFailure)->toBe($rollback)
            ->and($exception->getPrevious())->toBe($primary);
    }
});

it('does not execute the handler when beginning the transaction returns false', function (): void {
    $database = $this->createMock(DatabaseInterface::class);
    $database->expects($this->once())->method('begin')->willReturn(false);
    $database->expects($this->never())->method('commit');
    $database->expects($this->never())->method('rollback');
    $handler = new class implements OperationHandlerInterface {
        public bool $called = false;

        public function handle(OperationInterface $operation): OperationInterface
        {
            $this->called = true;

            return $operation;
        }
    };

    expect(fn() => (new TransactionMiddleware($database))->execute(
        Operation::create(new stdClass()),
        $handler,
    ))->toThrow(
        Componenta\CQRS\Command\Exception\TransactionBoundaryException::class,
        'begin returned false',
    )->and($handler->called)->toBeFalse();
});

it('rolls back when committing the transaction returns false', function (): void {
    $database = $this->createMock(DatabaseInterface::class);
    $database->expects($this->once())->method('begin')->willReturn(true);
    $database->expects($this->once())->method('commit')->willReturn(false);
    $database->expects($this->once())->method('rollback')->willReturn(true);
    $handler = new class implements OperationHandlerInterface {
        public function handle(OperationInterface $operation): OperationInterface
        {
            return $operation;
        }
    };

    expect(fn() => (new TransactionMiddleware($database))->execute(
        Operation::create(new stdClass()),
        $handler,
    ))->toThrow(
        Componenta\CQRS\Command\Exception\TransactionBoundaryException::class,
        'commit returned false',
    );
});

it('reports rollback returning false together with the primary failure', function (): void {
    $primary = new RuntimeException('handler failed');
    $database = $this->createMock(DatabaseInterface::class);
    $database->expects($this->once())->method('begin')->willReturn(true);
    $database->expects($this->never())->method('commit');
    $database->expects($this->once())->method('rollback')->willReturn(false);
    $handler = new class ($primary) implements OperationHandlerInterface {
        public function __construct(private readonly Throwable $failure)
        {
        }

        public function handle(OperationInterface $operation): OperationInterface
        {
            throw $this->failure;
        }
    };

    try {
        (new TransactionMiddleware($database))->execute(
            Operation::create(new stdClass()),
            $handler,
        );
        test()->fail('Expected rollback failure.');
    } catch (TransactionRollbackException $exception) {
        expect($exception->primaryFailure)->toBe($primary)
            ->and($exception->rollbackFailure)
            ->toBeInstanceOf(Componenta\CQRS\Command\Exception\TransactionBoundaryException::class)
            ->and($exception->getPrevious())->toBe($primary);
    }
});
