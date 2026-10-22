<?php

/**
 * Pest test stub — outgoing email + queued job assertions.
 * Copy to tests/Feature/MailAndJobsTest.php.
 */

use App\Mail\WelcomeMail;
use App\Models\User;
use App\Notifications\PasswordChanged;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

it('sends a welcome email to new users', function () {
    Mail::fake();

    $user = User::factory()->create();

    Mail::to($user)->send(new WelcomeMail($user));

    Mail::assertSent(WelcomeMail::class, fn ($m) => $m->hasTo($user->email));
});

it('queues the welcome email instead of sending it inline', function () {
    Mail::fake();
    Queue::fake();

    $user = User::factory()->create();
    Mail::to($user)->queue(new WelcomeMail($user));

    Queue::assertPushed(\Illuminate\Mail\SendQueuedMailable::class);
});

it('notifies the user when their password changes', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->notify(new PasswordChanged($user));

    Notification::assertSentTo($user, PasswordChanged::class);
});

it('dispatches a ProcessPodcast job when feed is parsed', function () {
    Queue::fake();

    // trigger your app's flow that should dispatch this job
    // e.g. FeedParserService::parse($url);

    Queue::assertPushed(\App\Jobs\ProcessPodcast::class);
});

it('releases a job back to the queue when the connection times out', function () {
    Queue::fake();

    // Assert any job is pushed and then released:
    // Queue::assertPushed(...);
});
