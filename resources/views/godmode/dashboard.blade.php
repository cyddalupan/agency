@extends('godmode.layouts.app')

@section('title', 'Village')

@php
    // ── The village ────────────────────────────────────────────────
    //
    // One building per agency. Position is deterministic: a hash of the agency
    // id picks a slot on a jittered grid, so the town looks organic but an
    // agency never moves between page loads — a village that reshuffles every
    // refresh is unreadable, you can never learn where anything is.
    //
    // Two independent axes, both already in `village()`:
    //   shape/opacity ← `status`  (active / inactive / pending) — can they log in
    //   colour + plate ← `state`  (settled / pending / overdue / none) — money
    //
    // Shapes are picked by the same hash so a given agency keeps its building.

    $shapePool = ['tower', 'castle', 'house', 'keep', 'mill'];

    $render = [];

    foreach ($village as $i => $b) {
        $seed = crc32((string) $b['id']);

        // Position. NOT a grid: a village laid out in rows reads as a table
        // with pictures. Each building gets its own offset on both axes, big
        // enough to see (up to ~34px of vertical drift, 26px horizontal) but
        // never enough to collide with its neighbour — the grid cell is the
        // spacing budget, the offsets are the variation inside it.
        $col = $i % 5;
        $row = intdiv($i, 5);

        // Deterministic per-building offsets, decorrelated so two agencies
        // with neighbouring ids do not drift together in a diagonal streak.
        // Horizontal drift is smaller than the vertical one on purpose: a
        // nameplate is ~186px wide inside a ~290px cell, so ±26px of sideways
        // travel is what pushed the edge columns off-screen. ±10px still
        // breaks the grid line without spilling.
        $jitterX = (int) ($seed % 21) - 10;            // -10..+10 px
        $lift    = (int) (((($seed >> 7) & 0xFFFF) % 69)) - 34;  // -34..+34 px
        $jitterY = 0;

        // Shape + a per-building scale so no two neighbours are identical.
        $shape = $shapePool[$seed % count($shapePool)];
        $scale = 0.9 + (($seed >> 9) % 22) / 100;   // 0.90 .. 1.11

        // Which sprite file. A building with no money is always the
        // under-construction/incomplete art; money states pick 'ok' unless
        // the money is bad.
        $sprite = match ($b['state']) {
            'overdue' => 'tower_delay',
            'pending' => 'tower_initial',
            // 'none' is an agency with real activity but no invoice yet —
            // that is a town that has not been billed, not a building site.
            // It gets the lively town sprite (lit windows), never the ruin.
            'none'    => 'town_ok',
            default   => $shape.'_ok',
        };

        // A shape may not have an `_ok` variant on disk; fall back.
        if (! in_array($sprite, ['tower_delay', 'tower_initial', 'tower_notstarted'], true)
            && ! file_exists(public_path('images/village/'.$sprite.'.svg'))) {
            $sprite = 'tower_ok';
        }

        $render[] = $b + [
            'col' => $col,
            'row' => $row,
            'jx'  => $jitterX,
            'jy'  => $jitterY,
            'lift' => $lift,
            'shape' => $shape,
            'scale' => $scale,
            'sprite' => $sprite,
        ];
    }

    $stateMeta = [
        'settled' => ['label' => 'Paid up',  'cls' => 'v-paid',    'dot' => 'var(--v-mint)'],
        'pending' => ['label' => 'Paying in', 'cls' => 'v-warn',    'dot' => 'var(--v-gold)'],
        'overdue' => ['label' => 'Delayed',   'cls' => 'v-bad',     'dot' => 'var(--v-rose)'],
        'none'    => ['label' => 'No billing', 'cls' => 'v-muted',  'dot' => 'var(--v-muted)'],
    ];

    $service = app(\App\Services\GodMode\GodReceivableService::class);
@endphp

@section('fullbleed')

{{-- ── Village shell ───────────────────────────────────────────────
     The whole page is the village. Every other console screen is reached
     through a modal opened from here, so the operator never leaves the town.
     Data attributes drive the JS; no framework, matching the tenant app. --}}

<style>
    /* Scoped to this page. Variables prefixed --v- so nothing here can
       collide with the daisyUI theme tokens the rest of the console uses. */
    #village-page {
        --v-ink:#eef3fb; --v-muted:#8b96ab; --v-gold:#ffd166;
        --v-mint:#6ee7b7; --v-rose:#fb7185; --v-line:rgba(125,211,252,.16);
        --v-panel:rgba(12,20,36,.92);
        margin:0; padding:0; min-height:100vh;
        display:flex; flex-direction:column;
    }
    /* Nothing below the ground should ever be visible as bare page background. */
    #village-page .v-ground { flex:1 1 auto; }
    body:has(#village-page) { background:#33815d; overflow-x:hidden; }
    #village-page .v-hud {
        display:flex; align-items:center; justify-content:space-between; gap:16px;
        flex-wrap:wrap; padding:12px 20px;
        background:linear-gradient(180deg,rgba(8,14,26,.98),rgba(8,14,26,.82));
        border-bottom:1px solid var(--v-line);
        position:sticky; top:0; z-index:40; backdrop-filter:blur(10px);
    }
    #village-page .v-brand { display:flex; align-items:center; gap:11px; }
    #village-page .v-crest {
        width:38px; height:38px; border-radius:10px; display:grid; place-items:center;
        font-size:19px; background:linear-gradient(145deg,#2a4a7a,#16243c);
        border:1px solid rgba(125,211,252,.3);
        box-shadow:0 0 20px -6px rgba(125,211,252,.55);
    }
    #village-page .v-brand h1 { font-size:15px; font-weight:800; color:var(--v-ink); line-height:1.15; margin:0; }
    #village-page .v-brand .v-sub { font-size:10.5px; color:var(--v-muted); }
    #village-page .v-res { display:flex; gap:8px; flex-wrap:wrap; }
    #village-page .v-chip {
        display:flex; align-items:center; gap:6px; background:var(--v-panel);
        border:1px solid var(--v-line); border-radius:8px; padding:5px 10px;
        font-size:12px; font-weight:700; color:var(--v-ink);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.05); cursor:pointer;
        transition:border-color .15s, background .15s;
    }
    #village-page .v-chip:hover { border-color:rgba(125,211,252,.45); background:rgba(20,32,54,.95); }
    #village-page .v-chip i { font-style:normal; font-size:13px; }

    /* ── ground ── */
    #village-page .v-ground {
        position:relative; min-height:100vh;
        /* Village sits in the lower half of the field, not pinned to the top:
           with only a handful of buildings a top-aligned row left two thirds
           of the screen empty and the town looked abandoned. */
        display:flex; flex-direction:column; justify-content:flex-end;
        padding:120px 22px 64px; overflow:hidden;
        /* Daytime meadow under a soft sky, not a night pit. The village was
           reading as an abandoned graveyard because everything was dark green
           on near-black; grass reads alive in mid-tones. */
        background:
            radial-gradient(ellipse 900px 420px at 16% 2%,rgba(255,224,140,.30),transparent 60%),
            radial-gradient(ellipse 700px 340px at 84% 6%,rgba(147,222,255,.22),transparent 62%),
            radial-gradient(ellipse 1200px 620px at 50% 122%,rgba(24,120,84,.40),transparent 72%),
            linear-gradient(178deg,#62b892 0%,#52ab84 20%,#46a078 50%,#3b9169 76%,#33815d 100%);
    }
    /* Sun + clouds: cheap, static, but they read as "daytime/sky" instantly. */
    #village-page .v-sky {
        position:absolute; inset:0; pointer-events:none; z-index:0; overflow:hidden;
    }
    #village-page .v-sun {
        position:absolute; top:26px; right:8%; width:74px; height:74px; border-radius:50%;
        background:radial-gradient(circle,#fff4c9,#ffd166 55%,rgba(255,209,102,0) 72%);
        filter:blur(1px); opacity:.9;
    }
    #village-page .v-cloud {
        position:absolute; height:26px; border-radius:999px;
        background:rgba(233,242,255,.14); filter:blur(6px);
    }
    #village-page .v-cloud.c1 { top:60px; left:6%; width:170px; }
    #village-page .v-cloud.c2 { top:118px; left:38%; width:130px; height:20px; }
    #village-page .v-cloud.c3 { top:40px; left:64%; width:150px; height:22px; }
    /* Rolling hills at the horizon, drawn behind the buildings. */
    /* A winding path through the middle of the field: gives the eye something
       to travel on instead of a flat void between horizon and village. */
    #village-page .v-path {
        position:absolute; left:-10%; right:-10%; top:56%; height:120px; z-index:0;
        background:linear-gradient(180deg,rgba(214,190,140,.34),rgba(196,170,120,.18));
        border-radius:50%;
        filter:blur(14px); transform:rotate(-2.2deg);
    }
    /* Scattered trees. Purely decorative, placed with inline styles so they
       are deterministic rather than randomly hopping on each render. */
    #village-page .v-tree {
        position:absolute; z-index:1; border-radius:50% 50% 46% 46%;
        background:radial-gradient(circle at 35% 30%,#4fbf90,#2f8a63 70%);
        box-shadow:0 8px 14px rgba(8,48,34,.30);
    }
    #village-page .v-tree::after {
        content:""; position:absolute; left:50%; bottom:-9px; translate:-50% 0;
        width:4px; height:11px; background:#5a4630; border-radius:2px;
    }
    #village-page .v-hills {
        position:absolute; left:-5%; right:-5%; bottom:34%; height:190px; z-index:0;
        background:
            radial-gradient(ellipse 46% 100% at 24% 100%,#5cbf96 60%,transparent 61%),
            radial-gradient(ellipse 40% 92% at 62% 100%,#4fb488 60%,transparent 61%),
            radial-gradient(ellipse 34% 80% at 88% 100%,#57bb92 60%,transparent 61%);
        opacity:.55; filter:blur(.4px);
    }
    #village-page .v-ground::before {
        content:""; position:absolute; inset:0; opacity:.55; pointer-events:none;
        background-image:
            radial-gradient(circle at 12% 22%,rgba(255,255,255,.16) 0 60px,transparent 61px),
            radial-gradient(circle at 88% 32%,rgba(20,90,64,.16) 0 76px,transparent 77px),
            radial-gradient(circle at 42% 84%,rgba(255,255,255,.12) 0 66px,transparent 67px),
            radial-gradient(circle at 66% 10%,rgba(20,90,64,.12) 0 52px,transparent 53px),
            radial-gradient(circle at 32% 52%,rgba(255,255,255,.10) 0 48px,transparent 49px),
            radial-gradient(circle at 76% 64%,rgba(20,90,64,.10) 0 58px,transparent 59px),
            radial-gradient(circle at 8% 78%,rgba(255,255,255,.09) 0 44px,transparent 45px);
    }
    #village-page .v-grid {
        position:relative; z-index:2;
        display:grid; grid-template-columns:repeat(5,minmax(0,1fr));
        gap:56px 6px; align-items:end;
    }
    @media (max-width:1200px){ #village-page .v-grid { grid-template-columns:repeat(4,minmax(0,1fr)); } }
    @media (max-width:900px) { #village-page .v-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:620px) { #village-page .v-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }

    /* ── one building ── */
    #village-page .v-plot {
        position:relative; display:flex; flex-direction:column; align-items:center;
        cursor:pointer; background:none; border:0; padding:0 2px;
        font:inherit; color:inherit; text-align:center;
        transition:transform .18s ease, filter .18s ease;
    }
    #village-page .v-plot:hover { filter:brightness(1.15); }
    #village-page .v-plot:hover .v-sprite { transform:translateY(-7px) scale(1.06); }
    #village-page .v-plot:focus-visible { outline:2px solid var(--v-gold); outline-offset:6px; border-radius:12px; }
    #village-page .v-plate {
        position:absolute; bottom:30px; left:50%; translate:-50% 0;
        width:88%; max-width:204px; height:56px; border-radius:50%; z-index:0;
        background:radial-gradient(ellipse,rgba(30,96,70,.42),rgba(30,96,70,.12));
        border:1px solid rgba(255,255,255,.20);
        box-shadow:inset 0 4px 10px rgba(8,48,34,.25);
        transition:box-shadow .2s, border-color .2s;
    }
    #village-page .v-plot[data-state="pending"] .v-plate {
        border-color:rgba(255,209,102,.34); box-shadow:inset 0 5px 12px rgba(0,0,0,.6),0 0 24px -6px rgba(255,209,102,.55);
    }
    #village-page .v-plot[data-state="overdue"] .v-plate {
        border-color:rgba(251,113,133,.4); box-shadow:inset 0 5px 12px rgba(0,0,0,.6),0 0 26px -6px rgba(251,113,133,.6);
    }
    #village-page .v-sprite {
        position:relative; z-index:1; width:158px; height:190px;
        transition:transform .18s ease;
        filter:drop-shadow(0 12px 14px rgba(0,0,0,.65));
    }
    #village-page .v-sprite img { width:100%; height:100%; display:block; }
    #village-page .v-plot[data-status="inactive"] .v-sprite,
    #village-page .v-plot[data-status="pending"]  .v-sprite { filter:drop-shadow(0 12px 14px rgba(0,0,0,.65)) grayscale(.45); }
    #village-page .v-plot[data-status="inactive"] { opacity:.62; }

    /* ── nameplate ── */
    #village-page .v-lbl {
        position:relative; z-index:3; margin-top:-10px; width:100%; max-width:200px;
        background:linear-gradient(180deg,rgba(255,255,255,.97),rgba(240,247,255,.97));
        border:1px solid rgba(20,60,45,.22); border-radius:8px; padding:4px 7px;
        box-shadow:0 4px 12px rgba(10,40,30,.28);
    }
    /* Agency names are long ("Gulf Horizon International Services Inc.").
       Truncating to one line hid WHO the building is, which defeats the whole
       screen. Two lines with the full name in the modal and the tooltip. */
    #village-page .v-lbl .v-n {
        font-size:10px; font-weight:700; color:#12283f; line-height:1.25;
        display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;
        overflow:hidden; word-break:break-word;
    }
    #village-page .v-lbl .v-st {
        font-size:8.5px; font-weight:800; letter-spacing:.07em; text-transform:uppercase;
        margin-top:2px; display:flex; align-items:center; justify-content:center; gap:4px;
    }
    #village-page .v-dot { width:5px; height:5px; border-radius:50%; display:inline-block; }
    #village-page .v-paid  { color:var(--v-mint); }
    #village-page .v-warn  { color:var(--v-gold); }
    #village-page .v-bad   { color:var(--v-rose); }
    #village-page .v-muted { color:var(--v-muted); }

    /* ── legend ── */
    #village-page .v-legend {
        display:flex; gap:15px; flex-wrap:wrap; justify-content:center;
        margin-top:26px; font-size:11px; color:#a9b6c9; position:relative; z-index:2;
    }
    #village-page .v-legend span { display:flex; align-items:center; gap:5px; }
    #village-page .v-legend i { width:9px; height:9px; border-radius:3px; display:inline-block; }

    /* ── modal ── */
    #v-modal {
        position:fixed; inset:0; z-index:100; display:none;
        align-items:center; justify-content:center; padding:20px;
        background:rgba(4,8,16,.78); backdrop-filter:blur(6px);
    }
    #v-modal[data-open="1"] { display:flex; }
    #v-modal .v-card {
        width:100%; max-width:560px; max-height:82vh; overflow:auto;
        background:linear-gradient(180deg,#101c30,#0b1424);
        border:1px solid rgba(125,211,252,.24); border-radius:16px;
        box-shadow:0 40px 90px -30px rgba(0,0,0,.95);
        color:var(--v-ink); padding:20px 22px;
    }
    #v-modal .v-card h2 { font-size:16px; font-weight:800; margin:0 0 3px; }
    #v-modal .v-card .v-kicker { font-size:10px; text-transform:uppercase; letter-spacing:.09em; color:var(--v-muted); }
    #v-modal .v-row {
        display:flex; justify-content:space-between; gap:12px; padding:7px 0;
        border-top:1px solid rgba(125,211,252,.1); font-size:12.5px;
    }
    #v-modal .v-row span { color:var(--v-muted); }
    #v-modal .v-row b { font-variant-numeric:tabular-nums; }
    #v-modal .v-actions { display:flex; flex-wrap:wrap; gap:8px; margin-top:16px; }
    #v-modal .v-a {
        display:inline-block; padding:7px 13px; border-radius:9px; font-size:12px; font-weight:700;
        text-decoration:none; background:rgba(125,211,252,.12); color:#bfe6fb;
        border:1px solid rgba(125,211,252,.28); transition:background .15s;
    }
    #v-modal .v-a:hover { background:rgba(125,211,252,.22); }
    #v-modal .v-close {
        float:right; background:none; border:0; color:var(--v-muted); font-size:20px;
        cursor:pointer; line-height:1; padding:2px 6px;
    }
    #v-modal .v-close:hover { color:var(--v-ink); }
</style>

<div id="village-page">
    <div class="v-hud">
        <div class="v-brand">
            <div class="v-crest">🏘️</div>
            <div>
                <h1>Landas Village</h1>
                <div class="v-sub">
                    {{ $villageSummary['total'] }} {{ Str::plural('agency', $villageSummary['total']) }}
                    · {{ $villageSummary['attention'] }} {{ Str::plural('needs', $villageSummary['attention']) }} attention
                </div>
            </div>
        </div>
        <div class="v-res">
            <div class="v-chip" data-open="modal-payments" title="Collected this month"><i>💰</i>{{ $service->formatMoney($summaryThis['collected']) }}</div>
            <div class="v-chip" data-open="modal-payments" title="Collected, last 12 months"><i>🏦</i>{{ $service->formatMoney($summaryRolling['collected']) }}</div>
            <div class="v-chip" data-open="modal-agencies" title="Billed agencies"><i>🏗️</i>{{ $villageSummary['billed'] }}</div>
            <div class="v-chip" data-open="modal-payments" title="Needs attention" style="color:var(--v-rose)"><i>⚠️</i>{{ $villageSummary['attention'] }}</div>
            {{-- The layout header is gone on this page, so sign-out lives here
                 or the operator has no way to leave a god session. --}}
            <form method="POST" action="{{ route('godmode.logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="v-chip" style="font-size:11px">Sign out</button>
            </form>
        </div>
    </div>

    <div class="v-ground">
        <div class="v-sky">
            <div class="v-sun"></div>
            <div class="v-cloud c1"></div>
            <div class="v-cloud c2"></div>
            <div class="v-cloud c3"></div>
        </div>
        <div class="v-hills"></div>
        <div class="v-path"></div>
        {{-- Scenery with depth: small far trees up top, larger near trees lower
             down. Fixed positions, so the landscape never reshuffles. --}}
        @foreach ([
            ['left:3%','top:24%',34,0.55],['left:14%','top:30%',28,0.6],['left:26%','top:22%',30,0.55],
            ['left:39%','top:29%',26,0.6],['left:52%','top:20%',32,0.55],['left:65%','top:28%',28,0.6],
            ['left:77%','top:23%',34,0.55],['left:89%','top:31%',30,0.6],
            ['left:6%','top:46%',52,1],['left:19%','top:56%',44,1],['left:31%','top:44%',48,1],
            ['left:45%','top:58%',40,1],['left:57%','top:45%',54,1],['left:71%','top:57%',46,1],
            ['left:84%','top:43%',50,1],['left:94%','top:58%',42,1],
            ['left:11%','top:70%',62,1],['left:37%','top:73%',56,1],['left:60%','top:69%',60,1],
            ['left:88%','top:72%',58,1],
        ] as $tree)
            <div class="v-tree" style="{{ $tree[0] }};{{ $tree[1] }};width:{{ $tree[2] }}px;height:{{ $tree[2] }}px;opacity:{{ $tree[3] }}"></div>
        @endforeach
        <div class="v-grid">
            @foreach ($render as $b)
                @php $meta = $stateMeta[$b['state']]; @endphp
                <button type="button" class="v-plot"
                        data-state="{{ $b['state'] }}"
                        data-status="{{ $b['status'] }}"
                        data-agency="{{ $b['id'] }}"
                        data-name="{{ $b['name'] }}"
                        data-subdomain="{{ $b['subdomain'] }}"
                        data-collected="{{ $b['collected'] }}"
                        data-outstanding="{{ $b['outstanding'] }}"
                        data-pending="{{ $b['pending_count'] }}"
                        data-overdue="{{ $b['overdue_count'] }}"
                        data-label="{{ $meta['label'] }}"
                        style="transform:translate({{ $b['jx'] }}px, {{ $b['lift'] }}px)"
                        title="{{ $b['name'] }} — {{ $meta['label'] }}"
                        aria-label="{{ $b['name'] }} — {{ $meta['label'] }}">
                    <div class="v-plate"></div>
                    <div class="v-sprite" style="transform:scale({{ $b['scale'] }})">
                        <img src="{{ asset('images/village/'.$b['sprite'].'.svg') }}" alt="">
                    </div>
                    <div class="v-lbl">
                        <div class="v-n">{{ $b['name'] }}</div>
                        <div class="v-st {{ $meta['cls'] }}">
                            <span class="v-dot" style="background:{{ $meta['dot'] }}"></span>{{ $meta['label'] }}
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

        <div class="v-legend">
            <span><i style="background:var(--v-mint)"></i>Paid up</span>
            <span><i style="background:var(--v-gold)"></i>Paying in</span>
            <span><i style="background:var(--v-rose)"></i>Delayed</span>
            <span><i style="background:var(--v-muted)"></i>No billing yet</span>
            <span style="opacity:.65">· Click a building</span>
        </div>
    </div>
</div>

{{-- ── Modal: agency detail ── --}}
<div id="v-modal" data-open="0" role="dialog" aria-modal="true" aria-labelledby="v-modal-title">
    <div class="v-card">
        <button type="button" class="v-close" data-close aria-label="Close">×</button>
        <div class="v-kicker" id="v-modal-kicker">Agency</div>
        <h2 id="v-modal-title">—</h2>
        <div id="v-modal-body" style="margin-top:12px"></div>
        <div class="v-actions" id="v-modal-actions"></div>
    </div>
</div>

{{-- ── Modal: payments (the ledger, in a modal) ── --}}
<div id="modal-payments" class="v-modal-src" hidden>
    <div class="v-kicker">Cross-tenant receivables</div>
    <h2>Payments</h2>
    <div class="v-row"><span>Collected this month</span><b>{{ $service->formatMoney($summaryThis['collected']) }}</b></div>
    <div class="v-row"><span>Collected, last 12 months</span><b>{{ $service->formatMoney($summaryRolling['collected']) }}</b></div>
    <div class="v-row"><span>Outstanding, 12 months</span><b>{{ $service->formatMoney($summaryRolling['outstanding']) }}</b></div>
    <div class="v-row"><span>Agencies with collections</span><b>{{ number_format((int) $summaryRolling['agency_count']) }}</b></div>
</div>

{{-- ── Modal: agencies (the client list, in a modal) ── --}}
<div id="modal-agencies" class="v-modal-src" hidden>
    <div class="v-kicker">Every client on the platform</div>
    <h2>Agencies</h2>
    <div class="v-row"><span>Total buildings</span><b>{{ $villageSummary['total'] }}</b></div>
    <div class="v-row"><span>Billed at least once</span><b>{{ $villageSummary['billed'] }}</b></div>
    <div class="v-row"><span>Paid up</span><b>{{ $villageSummary['thriving'] }}</b></div>
    <div class="v-row"><span>Paying in</span><b>{{ $villageSummary['settling'] }}</b></div>
    <div class="v-row"><span>Delayed</span><b>{{ $villageSummary['attention'] }}</b></div>
</div>

<script>
(function () {
    var modal   = document.getElementById('v-modal');
    var kicker  = document.getElementById('v-modal-kicker');
    var title   = document.getElementById('v-modal-title');
    var body    = document.getElementById('v-modal-body');
    var actions = document.getElementById('v-modal-actions');
    var lastFocus = null;

    var PESO = function (v) {
        var n = parseFloat(v || 0);
        var s = Math.abs(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return (n < 0 ? '-₱' : '₱') + s;
    };

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function row(label, value) {
        return '<div class="v-row"><span>' + esc(label) + '</span><b>' + value + '</b></div>';
    }

    function openModal() {
        lastFocus = document.activeElement;
        modal.setAttribute('data-open', '1');
        var c = modal.querySelector('.v-close');
        if (c) { c.focus(); }
    }

    function closeModal() {
        modal.setAttribute('data-open', '0');
        body.innerHTML = '';
        actions.innerHTML = '';
        if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
    }

    function agencyModal(el) {
        var d = el.dataset;
        kicker.textContent = d.status ? ('Agency · ' + d.status) : 'Agency';
        title.textContent = d.name;

        var html = '';
        html += row('Payment state', '<span class="' + stateCls(d.state) + '">' + esc(d.label) + '</span>');
        html += row('Collected', PESO(d.collected));
        html += row('Outstanding', PESO(d.outstanding));
        html += row('Pending bills', esc(d.pending));
        if (parseInt(d.overdue, 10) > 0) {
            html += row('Overdue bills', '<span class="v-bad">' + esc(d.overdue) + '</span>');
        }
        if (d.subdomain) { html += row('Subdomain', esc(d.subdomain)); }
        body.innerHTML = html;

        actions.innerHTML =
            '<a class="v-a" href="/godmode/payments/' + encodeURIComponent(d.agency) + '">Open payments →</a>' +
            '<a class="v-a" href="/godmode/agencies/' + encodeURIComponent(d.agency) + '">Agency details →</a>';
        openModal();
    }

    function stateCls(state) {
        return state === 'settled' ? 'v-paid'
             : state === 'pending' ? 'v-warn'
             : state === 'overdue' ? 'v-bad'
             : 'v-muted';
    }

    function panelModal(srcId) {
        var src = document.getElementById(srcId);
        if (!src) { return; }
        kicker.textContent = src.querySelector('.v-kicker') ? src.querySelector('.v-kicker').textContent : '';
        title.textContent = src.querySelector('h2') ? src.querySelector('h2').textContent : '';
        var rows = src.querySelectorAll('.v-row');
        var html = '';
        for (var i = 0; i < rows.length; i++) { html += rows[i].outerHTML; }
        body.innerHTML = html;
        actions.innerHTML = srcId === 'modal-payments'
            ? '<a class="v-a" href="/godmode/payments">Open full ledger →</a>'
            : '<a class="v-a" href="/godmode/agencies">Open agency list →</a>';
        openModal();
    }

    document.addEventListener('click', function (e) {
        var plot = e.target.closest('.v-plot');
        if (plot) { agencyModal(plot); return; }

        var src = e.target.closest('[data-open]');
        if (src && src.getAttribute('data-open') !== '0') { panelModal(src.dataset.open); return; }

        if (e.target.closest('[data-close]')) { closeModal(); return; }
        if (e.target === modal) { closeModal(); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.getAttribute('data-open') === '1') { closeModal(); }
    });
})();
</script>

@endsection
