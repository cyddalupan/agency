@extends('layouts.app')

@section('title', 'New Controlled Document')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📄</span>
                <div>
                    <h1 class="text-2xl font-extrabold">New Controlled Document</h1>
                    <p class="opacity-70 text-sm mt-0.5">
                        Registers a document at v1.0 when a file is attached. It starts as a draft.
                    </p>
                </div>
            </div>
            <a href="{{ route('controlled_documents.index') }}" class="btn btn-sm btn-ghost text-primary-content" id="document-cancel-top">
                ← Back to register
            </a>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('controlled_documents.store') }}" enctype="multipart/form-data"
          class="card bg-base-100 shadow" id="document-form">
        @csrf
        <div class="card-body space-y-4">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label" for="code"><span class="label-text font-semibold">Document code <span class="text-error">*</span></span></label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required maxlength="64"
                           placeholder="e.g. QMS-001"
                           class="input input-bordered w-full @error('code') input-error @enderror">
                    @error('code')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                </div>

                <div class="form-control">
                    <label class="label" for="title"><span class="label-text font-semibold">Title <span class="text-error">*</span></span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255"
                           placeholder="e.g. Leave Policy"
                           class="input input-bordered w-full @error('title') input-error @enderror">
                    @error('title')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                </div>

                <div class="form-control">
                    <label class="label" for="document_type"><span class="label-text font-semibold">Type</span></label>
                    <select name="document_type" id="document_type" class="select select-bordered w-full">
                        <option value="">— none —</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('document_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label" for="department"><span class="label-text font-semibold">Department</span></label>
                    <input type="text" name="department" id="department" value="{{ old('department') }}" maxlength="255"
                           class="input input-bordered w-full">
                </div>

                <div class="form-control">
                    <label class="label" for="process_owner_id"><span class="label-text font-semibold">Process owner</span></label>
                    <select name="process_owner_id" id="process_owner_id" class="select select-bordered w-full">
                        <option value="">— none —</option>
                        @foreach ($owners as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('process_owner_id') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label" for="confidentiality"><span class="label-text font-semibold">Confidentiality</span></label>
                    <select name="confidentiality" id="confidentiality" class="select select-bordered w-full">
                        @foreach ($levels as $value => $label)
                            <option value="{{ $value }}" @selected(old('confidentiality', 'internal') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label" for="review_frequency"><span class="label-text font-semibold">Review frequency</span></label>
                    <select name="review_frequency" id="review_frequency" class="select select-bordered w-full">
                        <option value="">— none —</option>
                        @foreach ($frequencies as $value => $label)
                            <option value="{{ $value }}" @selected(old('review_frequency') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label" for="next_review_date"><span class="label-text font-semibold">Next review date</span></label>
                    <input type="date" name="next_review_date" id="next_review_date" value="{{ old('next_review_date') }}"
                           class="input input-bordered w-full">
                </div>
            </div>

            <div class="form-control">
                <label class="label" for="file"><span class="label-text font-semibold">First version file (optional)</span></label>
                <input type="file" name="file" id="file" class="file-input file-input-bordered w-full">
                <label class="label"><span class="label-text-alt opacity-60">Max 20 MB. Attaching a file creates version 1.0.</span></label>
                @error('file')<span class="text-error text-xs">{{ $message }}</span>@enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('controlled_documents.index') }}" class="btn btn-ghost" id="document-cancel">Cancel</a>
                <button type="submit" class="btn btn-primary" id="document-submit">Create document</button>
            </div>
        </div>
    </form>
</div>
@endsection
