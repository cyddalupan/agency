<?php

namespace App\Http\Controllers;

use App\Models\ApplicantDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PortalDocumentController extends Controller
{
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:50'],
            'document'      => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        $applicant = Auth::guard('applicant')->user();
        $file = $request->file('document');

        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('applicant-documents', $fileName, 'public');

        ApplicantDocument::create([
            'agency_id'     => $applicant->agency_id,
            'applicant_id'  => $applicant->id,
            'document_type' => $validated['document_type'],
            'file_name'     => $file->getClientOriginalName(),
            'file_path'     => $filePath,
            'mime_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'notes'         => $validated['notes'] ?? null,
        ]);

        return redirect()->route('portal.profile')
            ->with('success', 'Document uploaded successfully.');
    }

    public function download(ApplicantDocument $document)
    {
        $applicant = Auth::guard('applicant')->user();

        if ($document->applicant_id !== $applicant->id) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
