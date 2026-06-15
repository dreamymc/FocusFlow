<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Actions\MoveTaskAction;
use App\Models\Task;
use App\Enums\TaskStatus;
use Illuminate\Support\Facades\Event;
use App\Events\TaskMoved;
use App\Events\TaskCompleted;

it('moves a task', function () {
    Event::fake();
    
    $task = Task::factory()->create(['status' => TaskStatus::Backlog->value]);
    
    $action = app(MoveTaskAction::class);
    $updatedTask = $action->execute($task, TaskStatus::InProgress);
    
    expect($updatedTask->status->value)->toBe(TaskStatus::InProgress->value);
    
    Event::assertDispatched(TaskMoved::class, function ($event) use ($task) {
        return $event->task->id === $task->id;
    });
    Event::assertNotDispatched(TaskCompleted::class);
});

it('dispatches TaskCompleted when moved to Done', function () {
    Event::fake();
    
    $task = Task::factory()->create(['status' => TaskStatus::InProgress->value]);
    
    $action = app(MoveTaskAction::class);
    $updatedTask = $action->execute($task, TaskStatus::Done);
    
    expect($updatedTask->status->value)->toBe(TaskStatus::Done->value);
    
    Event::assertDispatched(TaskCompleted::class, function ($event) use ($task) {
        return $event->task->id === $task->id;
    });
});

use Illuminate\Support\Facades\Exceptions;

it('gracefully handles event broadcasting failures', function () {
    Exceptions::fake();

    Event::shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(TaskMoved::class))
        ->andThrow(new \Illuminate\Broadcasting\BroadcastException('Pusher error: cURL error 7'));

    Event::shouldReceive('dispatch')
        ->zeroOrMoreTimes()
        ->with(Mockery::type(TaskCompleted::class));

    Event::shouldReceive('dispatch')
        ->zeroOrMoreTimes()
        ->with(Mockery::type(\Illuminate\Log\Events\MessageLogged::class));

    $task = Task::factory()->create(['status' => TaskStatus::Backlog->value]);
    
    $action = app(MoveTaskAction::class);
    $updatedTask = $action->execute($task, TaskStatus::InProgress);
    
    expect($updatedTask->status->value)->toBe(TaskStatus::InProgress->value);
    Exceptions::assertReported(\Illuminate\Broadcasting\BroadcastException::class);
});

it('does not swallow unrelated critical exceptions during task move', function () {
    Event::shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(TaskMoved::class))
        ->andThrow(new \RuntimeException('Database connection lost'));

    Event::shouldReceive('dispatch')
        ->zeroOrMoreTimes()
        ->with(Mockery::type(TaskCompleted::class));

    Event::shouldReceive('dispatch')
        ->zeroOrMoreTimes()
        ->with(Mockery::type(\Illuminate\Log\Events\MessageLogged::class));

    $task = Task::factory()->create(['status' => TaskStatus::Backlog->value]);
    
    $action = app(MoveTaskAction::class);
    
    expect(fn() => $action->execute($task, TaskStatus::InProgress))
        ->toThrow(\RuntimeException::class, 'Database connection lost');
});
