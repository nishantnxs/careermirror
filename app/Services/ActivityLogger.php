<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $properties
     */
    public function record(
        ActivityAction $action,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?array $properties = null,
    ): ActivityLog {
        [$actor, $guard, $actorName] = $this->resolveActor();

        $oldValues = $this->normalize($oldValues);
        $newValues = $this->normalize($newValues);

        return ActivityLog::create([
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'actor_name' => $actorName,
            'actor_guard' => $guard,
            'action' => $action,
            'description' => $description ?: $action->label(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subject ? $this->subjectLabel($subject) : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 500, ''),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public function diff(array $before, array $after): array
    {
        $old = [];
        $new = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $key) {
            $left = $before[$key] ?? null;
            $right = $after[$key] ?? null;

            if ($this->valuesAreEqual($left, $right)) {
                continue;
            }

            $old[$key] = $left;
            $new[$key] = $right;
        }

        return [$old, $new];
    }

    /**
     * @return array{0: ?Model, 1: ?string, 2: string}
     */
    protected function resolveActor(): array
    {
        foreach (['admin', 'employer', 'candidate'] as $guard) {
            $user = Auth::guard($guard)->user();

            if ($user instanceof Model) {
                return [$user, $guard, $this->actorName($user, $guard)];
            }
        }

        return [null, 'system', 'System'];
    }

    protected function actorName(Model $actor, string $guard): string
    {
        return match ($guard) {
            'admin' => (string) ($actor->getAttribute('name') ?? 'Admin'),
            'employer' => (string) ($actor->getAttribute('company_name') ?: $actor->getAttribute('name') ?: 'Employer'),
            'candidate' => (string) ($actor->getAttribute('name') ?? 'Candidate'),
            default => class_basename($actor),
        };
    }

    protected function subjectLabel(Model $subject): string
    {
        $type = class_basename($subject);

        $name = $subject->getAttribute('title')
            ?? $subject->getAttribute('name')
            ?? $subject->getAttribute('company_name')
            ?? $subject->getAttribute('order_number')
            ?? $subject->getAttribute('email')
            ?? '#'.$subject->getKey();

        return trim($type.': '.$name);
    }

    /**
     * @param  array<string, mixed>|null  $values
     * @return array<string, mixed>|null
     */
    protected function normalize(?array $values): ?array
    {
        if ($values === null || $values === []) {
            return null;
        }

        return collect($values)
            ->map(function ($value) {
                if ($value instanceof \BackedEnum) {
                    return $value->value;
                }

                if ($value instanceof \UnitEnum) {
                    return $value->name;
                }

                if ($value instanceof \DateTimeInterface) {
                    return $value->format('Y-m-d H:i:s');
                }

                if (is_bool($value)) {
                    return $value ? 'true' : 'false';
                }

                return $value;
            })
            ->all();
    }

    protected function valuesAreEqual(mixed $left, mixed $right): bool
    {
        return json_encode($this->normalize(['v' => $left])['v'])
            === json_encode($this->normalize(['v' => $right])['v']);
    }
}
