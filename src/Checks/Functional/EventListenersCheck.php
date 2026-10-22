<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class EventListenersCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Event classes have listeners wired up'; }

    public function run()
    {
        $eventPath = app_path('Events');
        if (! is_dir($eventPath)) {
            return $this->pass('No app/Events directory.');
        }

        $fs = new Filesystem();
        $events = [];

        foreach ($fs->allFiles($eventPath) as $file) {
            $events[] = 'App\\Events\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
        }

        $listener = app('events');

        $missing = [];
        foreach ($events as $eventClass) {
            if (! class_exists($eventClass)) continue;
            try {
                $listeners = $listener->getListeners($eventClass);
            } catch (\Throwable $e) {
                $listeners = [];
            }
            if (empty($listeners)) {
                $missing[] = $eventClass;
            }
        }

        if ($missing) {
            return $this->warning(
                'Events without listeners: ' . implode(', ', array_slice($missing, 0, 5)),
                ['missing' => $missing]
            );
        }

        return $this->pass('All events have at least one listener.');
    }
}
