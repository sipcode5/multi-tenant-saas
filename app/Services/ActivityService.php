<?php

namespace App\Services;

use App\Models\Organization;

class ActivityService
{
    public function log(string $description, ?object $subject = null, array $properties = []): void
    {
        $org = Organization::current();

        $activity = activity()
            ->causedBy(auth()->user())
            ->withProperties(array_merge(
                ['organization_id' => $org?->id],
                $properties,
            ))
            ->log($description);

        if ($subject) {
            $activity->performedOn($subject);
        }
    }
}
