<?php

declare(strict_types=1);

namespace App\Actions\Documents;

use App\Models\Clinic;
use App\Models\Document;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Stores the file on the 'local' disk (storage/app/private — see the
 * documents migration docblock for why not 'public') under a per-clinic
 * directory, then records it. Logs a LeadActivity only when the document
 * is tied to a specific lead (clinic-level verification docs aren't part
 * of any lead's history).
 */
class UploadDocument
{
    /**
     * @param  array{type: string, title: string}  $data
     */
    public function handle(Clinic $clinic, ?Lead $lead, UploadedFile $file, array $data, User $uploader): Document
    {
        return DB::transaction(function () use ($clinic, $lead, $file, $data, $uploader) {
            $path = $file->store("documents/{$clinic->id}", 'local');

            $document = Document::create([
                'clinic_id' => $clinic->id,
                'lead_id' => $lead?->id,
                'uploaded_by' => $uploader->id,
                'type' => $data['type'],
                'title' => $data['title'],
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
            ]);

            if ($lead) {
                $lead->activities()->create([
                    'actor_type' => $uploader::class,
                    'actor_id' => $uploader->id,
                    'type' => 'system',
                    'payload_json' => ['event' => 'document_uploaded', 'document_id' => $document->id, 'clinic_id' => $clinic->id],
                    'created_at' => now(),
                ]);
            }

            return $document;
        });
    }
}
