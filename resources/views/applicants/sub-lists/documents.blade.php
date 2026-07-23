<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
    @forelse ($records as $doc)
    <div class="card bg-base-200 shadow-sm">
        @php $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION); @endphp
        @if(in_array($ext, ['jpg','jpeg','png','webp','gif']))
        <figure class="px-2 pt-2">
            <img src="{{ Storage::url($doc->file_path) }}" class="w-full h-24 object-cover rounded" alt="{{ $doc->file_name }}">
        </figure>
        @else
        <figure class="px-2 pt-2 flex items-center justify-center h-24">
            <span class="text-4xl opacity-40">📎</span>
        </figure>
        @endif
        <div class="card-body p-2 text-xs">
            <p class="font-semibold truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</p>
            <p class="opacity-60 truncate">{{ str_replace('_', ' ', $doc->document_type) }}</p>
            <div class="flex justify-between items-center mt-1">
                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="link link-primary text-xs">View</a>
                <form action="{{ route('applicants.documents.destroy', [$doc->applicant_id, $doc]) }}" method="POST"
                      onsubmit="return confirm('Delete this document?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-ghost btn-xs text-error">Delete</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <p class="col-span-full text-sm opacity-40 py-4 text-center">No documents uploaded yet.</p>
    @endforelse
</div>
