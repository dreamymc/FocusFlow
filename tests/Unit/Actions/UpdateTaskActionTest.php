<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Actions\UpdateTaskAction;
use App\Models\Task;
use App\Models\User;
use App\Events\TaskUpdated;
use App\Events\TaskAssigned;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;

it('updates a task and dispatches events', function () {
    Event::fake();

    $task = Task::factory()->create([
        'title' => 'Old Title',
    ]);
    $user = User::factory()->create();
    
    $action = app(UpdateTaskAction::class);
    $updatedTask = $action->execute($task, [
        'title' => 'New Title',
        'assignee_ids' => [$user->id],
    ]);
    
    expect($updatedTask->title)->toBe('New Title');
    $this->assertCount(1, $updatedTask->assignees);

    Event::assertDispatched(TaskUpdated::class, function ($event) use ($task) {
        return $event->task->id === $task->id;
    });

    Event::assertDispatched(TaskAssigned::class, function ($event) use ($task, $user) {
        return $event->task->id === $task->id && $event->user->id === $user->id;
    });
});

it('gracefully handles broadcasting failure in UpdateTaskAction', function () {
    Exceptions::fake();

    Event::shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(TaskUpdated::class))
        ->andThrow(new \Illuminate\Broadcasting\BroadcastException('Pusher error: cURL error 7'));

    Event::shouldReceive('dispatch')
        ->zeroOrMoreTimes()
        ->with(Mockery::type(TaskAssigned::class));

    Event::shouldReceive('dispatch')
        ->zeroOrMoreTimes()
        ->with(Mockery::type(\Illuminate\Log\Events\MessageLogged::class));

    $task = Task::factory()->create(['title' => 'Old Title']);
    
    $action = app(UpdateTaskAction::class);
    $updatedTask = $action->execute($task, [
        'title' => 'New Title',
    ]);
    
    expect($updatedTask->title)->toBe('New Title');
    Exceptions::assertReported(\Illuminate\Broadcasting\BroadcastException::class);
});
