<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicantDocumentController extends Controller
{
    public function store(Request $request, Applicant $applicant)
    {
        $request->validate([
            'document_type' => 'required|string|max:255',
            'file'          => 'required|file|mimes:jpg,jpeg,png,webp,gif,pdf|max:2048',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $path = $file->store('applicant-documents', 'public');

        $applicant->documents()->create([
            'agency_id'     => $applicant->agency_id,
            'user_id'       => auth()->id(),
            'document_type' => $request->document_type,
            'file_name'     => $file->getClientOriginalName(),
            'file_path'     => $path,
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'notes'         => $request->notes,
        ]);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Applicant $applicant, ApplicantDocument $document)
    {
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Document deleted successfully.');
    }
}
