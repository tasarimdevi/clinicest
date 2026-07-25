<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The only way a document's file is ever served — never a direct public
 * URL. See DocumentPolicy::view() for the clinic-membership scoping this
 * enforces on top of the documents.view permission.
 */
class DocumentDownloadController extends Controller
{
    public function __invoke(Document $document): StreamedResponse
    {
        Gate::authorize('view', $document);

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }
}
