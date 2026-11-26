{{--
    Card 6b056f91 (Cyd 2026-11-24): per-tab total on the Agents Report.
    READ THE STICKY TEST BEFORE CHANGING THIS FILE.

    One line per data table, under the card title and above the column headers,
    so the number is on screen the moment the tab opens and stays there while
    you scroll the rows.

    Sticky status — TESTED, NOT ASSUMED.
    `layouts/app.blade.php:194` is `<main class="... overflow-x-auto">`. That
    ancestor's overflow-y computes to `auto`, and `auto` + `visible` = `auto`,
    so `<main>` IS a scroll container. Every `position: sticky` descendant is
    therefore positioned inside `<main>`'s scroll box, not the viewport, and
    `<main>` never scrolls itself (it grows with the content). Measured on
    /agents-report?tab=deductions and cash-advance, headed Chromium 1280x800,
    page scrolled to the middle of a 35-40 row table: the bar sits ~600px above
    the viewport. It does nothing. `headless` Playwright cannot see this
    (page scrolling is a no-op there), so this was only visible in a headed
    browser — keep that in mind before you "verify" it with a headless run.

    Therefore, per Bitoy's fallback, this is a STATIC bar: first content of the
    card, so it is visible on open, and the footer `<tfoot>` carries the same
    number at the end of the table. Two visible numbers, one computation
    ($tabTotals in AgentReportController::index()), never a disagreement.

    Do NOT re-add `sticky` here without first removing `overflow-x-auto` from
    `<main>` (that is a layout wide change: five layouts do it, and on mobile
    the page overflowed 375 -> 471px in a headed test, so it is its own card).
    Keep `top-14`/`z-20` only if you do that; the navbar is `sticky top-0 z-30`
    so a re-enabled bar needs a z-index below 30.

    Never nest this inside `.overflow-x-auto` (the table's scroll box).

    Props: $amount (float, PHP) and optional $label.
--}}
@props(['amount' => 0, 'label' => 'Total (all rows in this tab)'])

<div class="-mx-1 px-1 mb-2" data-tab-total>
    <div class="flex flex-wrap items-center justify-between gap-2 rounded-box bg-base-200 border border-base-300 px-3 py-2">
        <span class="text-sm font-semibold">{{ $label }}</span>
        <span class="text-sm font-bold tabular-nums whitespace-nowrap">{{ peso((float) $amount) }}</span>
    </div>
</div>
