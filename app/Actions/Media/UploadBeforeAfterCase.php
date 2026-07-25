<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\BeforeAfterCase;
use App\Models\Clinic;
use App\Models\User;
use App\Notifications\BeforeAfterPendingModeration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * A clinic submits a real before/after case (they hold the patient
 * relationship and the consent). It is created is_published=false and
 * only appears publicly after a moderator approves it — see
 * BeforeAfterModeration and the trust rule in the before_after_cases
 * migration ("never substitute a placeholder for a genuine result").
 * consent_confirmed is required at the form layer, so it's always true
 * here; it's still recorded so the moderator sees an explicit attestation.
 * Photos go on the 'public' disk (like the clinic gallery) — visible only
 * once published, but there's no per-file access control beyond that.
 */
class UploadBeforeAfterCase
{
    /**
     * @param  array{treatment_id: int, doctor_id: int|null, title: string, description: string|null, patient_country_id: int|null}  $data
     */
    public function handle(Clinic $clinic, UploadedFile $before, UploadedFile $after, array $data, User $submitter): BeforeAfterCase
    {
        return DB::transaction(function () use ($clinic, $before, $after, $data) {
            $case = $clinic->beforeAfterCases()->create([
                'treatment_id' => $data['treatment_id'],
                'doctor_id' => $data['doctor_id'] ?? null,
                'title' => ['en' => $data['title']],
                'description' => $data['description'] ? ['en' => $data['description']] : null,
                'patient_country_id' => $data['patient_country_id'] ?? null,
                'before_media_path' => $before->store("before-after/{$clinic->id}", 'public'),
                'after_media_path' => $after->store("before-after/{$clinic->id}", 'public'),
                'consent_confirmed' => true,
                'is_published' => false,
            ]);

            Notification::send(
                User::permission('reviews.moderate')->get(),
                new BeforeAfterPendingModeration($case)
            );

            return $case;
        });
    }
}
