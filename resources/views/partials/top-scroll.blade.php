{{-- Top horizontal scrollbar for wide tables (Cyd 2026-11-01).
     Long tables put their horizontal scrollbar at the very bottom, so you had to
     scroll past every row just to slide sideways. This mirrors a slim scrollbar
     above each table wrapper that actually overflows, kept in sync both ways. --}}
<style>
    .top-scroll {
        overflow-x: auto;
        overflow-y: hidden;
        height: 14px;
        margin-bottom: .25rem;
    }
    .top-scroll[hidden] { display: none; }
    .top-scroll-inner { height: 1px; }
    .top-scroll::-webkit-scrollbar { height: 10px; }
    .top-scroll::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, .55);
        border-radius: 9999px;
    }
    .top-scroll::-webkit-scrollbar-track { background: rgba(148, 163, 184, .18); border-radius: 9999px; }
</style>
<script>
(function () {
    'use strict';

    var SELECTOR = '.overflow-x-auto, .overflow-auto';

    function eligible(el) {
        if (el.dataset.topScroll === 'off') return false;
        if (el.closest('.top-scroll')) return false;
        if (el.getAttribute('role') === 'tablist') return false;
        return !!el.querySelector('table');
    }

    function attach(el) {
        if (el.dataset.topScroll === 'on') { sync(el); return; }
        el.dataset.topScroll = 'on';

        var bar = document.createElement('div');
        bar.className = 'top-scroll';
        bar.setAttribute('aria-hidden', 'true');
        var inner = document.createElement('div');
        inner.className = 'top-scroll-inner';
        bar.appendChild(inner);
        el.parentNode.insertBefore(bar, el);

        el._topScrollBar = bar;
        el._topScrollInner = inner;

        var lock = false;
        bar.addEventListener('scroll', function () {
            if (lock) return;
            lock = true;
            el.scrollLeft = bar.scrollLeft;
            lock = false;
        });
        el.addEventListener('scroll', function () {
            if (lock) return;
            lock = true;
            bar.scrollLeft = el.scrollLeft;
            lock = false;
        });

        sync(el);
    }

    function sync(el) {
        var bar = el._topScrollBar;
        if (!bar) return;
        var overflows = el.scrollWidth > el.clientWidth + 2;
        bar.hidden = !overflows;
        if (!overflows) return;
        el._topScrollInner.style.width = el.scrollWidth + 'px';
        if (bar.scrollLeft !== el.scrollLeft) bar.scrollLeft = el.scrollLeft;
    }

    function refresh() {
        document.querySelectorAll(SELECTOR).forEach(function (el) {
            if (eligible(el)) attach(el);
            else sync(el);
        });
    }

    var pending = null;
    function schedule() {
        if (pending) clearTimeout(pending);
        pending = setTimeout(function () { pending = null; refresh(); }, 150);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', schedule);
    } else {
        schedule();
    }
    window.addEventListener('resize', schedule);
    // Tabs, accordions and modals reveal tables by toggling classes only, which
    // fires no childList mutation — re-measure after any click, debounced.
    document.addEventListener('click', schedule, true);

    if (window.MutationObserver) {
        new MutationObserver(schedule).observe(document.body, { childList: true, subtree: true });
    }
})();
</script>
