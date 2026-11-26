{{--
    Infinite scroll for the applicant lists (card 6b07e35d8d2e571a7ac9ca6b).

    First batch is server-rendered, so the page works without JS and lands on
    the same rows a no-JS user sees. Later batches come from applicants/feed
    and are appended verbatim — the server renders them with the same partial,
    so appended and rendered rows cannot drift.

    The component is declared inline rather than via Alpine.data(): this
    partial is emitted in the body, while the Alpine bundle loads in <head>,
    so alpine:init has already fired by the time this markup exists.
--}}
<div x-data="{
        hasMore: {{ $applicants->hasMorePages() ? 'true' : 'false' }},
        total: {{ $applicants->total() }},
        loading: false,
        error: '',
        page: {{ $applicants->currentPage() }},
        observer: null,

        init() {
            this.$nextTick(() => {
                if (!('IntersectionObserver' in window)) return;
                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach((e) => {
                        if (e.isIntersecting && !this.loading) this.loadMore();
                    });
                }, { rootMargin: '300px' });
                if (this.$refs.sentinel) this.observer.observe(this.$refs.sentinel);
            });
        },

        async loadMore() {
            if (this.loading) return;

            const params = new URLSearchParams(window.location.search);
            params.set('context', '{{ $context }}');
            params.set('page', this.page + 1);

            this.loading = true;
            this.error = '';
            try {
                const res = await fetch('{{ route('applicants.feed') }}?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('Server returned ' + res.status);
                const data = await res.json();

                this.$refs.body.insertAdjacentHTML('beforeend', data.html);
                this.page = data.page;
                this.hasMore = data.has_more;
                this.total = data.total;

                if (!this.hasMore && this.observer) {
                    this.observer.disconnect();
                    this.observer = null;
                }
            } catch (e) {
                this.error = 'Could not load more applicants. ' + e.message;
            } finally {
                this.loading = false;
            }
        },

        retry() { this.loadMore(); }
     }"
     x-init="init()">

    <table class="table table-zebra">
        <thead>
            <tr class="bg-base-200/80">
                @if(isset($customHeaders))
                    {!! $customHeaders !!}
                @else
                    @foreach($tableColumns as $col)
                        <th @if($col === 'name') class="w-64" @endif>
                            {{ app_applicant_table_column_labels()[$col] ?? ucfirst($col) }}
                        </th>
                    @endforeach
                    <th class="text-right">Action</th>
                @endif
            </tr>
        </thead>
        <tbody x-ref="body">
            @include($rowPartial, ['applicants' => $applicants, 'tableColumns' => $tableColumns ?? null])
        </tbody>
    </table>

    {{-- Sentinel: fires when it scrolls into view. Dropped once the list is
         exhausted, so there is no endless spinner. --}}
    <div x-ref="sentinel" class="py-6 text-center" x-show="hasMore || loading">
        <span x-show="loading" class="loading loading-spinner loading-md"></span>
        <span x-show="!loading && hasMore" class="text-sm opacity-50">Scroll for more…</span>
    </div>

    {{-- End-of-list state: always explicit, never a silent stop. --}}
    <div class="py-6 text-center" x-show="!hasMore && total > 0" x-cloak>
        <span class="badge badge-ghost">End of list — <span x-text="total"></span> applicants</span>
    </div>

    <div class="py-4 text-center" x-show="error" x-cloak>
        <span class="text-sm text-error" x-text="error"></span>
        <button type="button" class="btn btn-xs btn-outline ml-2" @click="retry()">Retry</button>
    </div>
</div>
