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

        // Which sprite file. Card 6b07f356b6b30ea65c7bc2de — the BODY now
        // comes from the initial axis, and lateness only adds a MARKER, never
        // swaps the body. That is what lets a Paid-up agency that missed a
        // retainer still look like a finished building instead of a ruin.
        $sprite = match ($b['initial_state'] ?? 'initial_none') {
            'initial_paying' => 'tower_initial',
            'initial_paid'   => $shape.'_ok',
            default          => 'block_unbilled',
        };

        // A shape may not have an `_ok` variant on disk; fall back.
        if (! in_array($sprite, ['tower_delay', 'tower_initial', 'tower_notstarted', 'block_unbilled'], true)
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
    /* Plate glow follows the two axes (card §A layer table): the body's state
       tints the plate, and lateness adds a rose ring on top so a paid-but-late
       building still reads as green-with-a-warning, not as a failure. */
    #village-page .v-plot[data-initial-state="initial_paying"] .v-plate {
        border-color:rgba(255,209,102,.34); box-shadow:inset 0 5px 12px rgba(0,0,0,.6),0 0 24px -6px rgba(255,209,102,.55);
    }
    #village-page .v-plot[data-initial-state="initial_paid"] .v-plate {
        border-color:rgba(110,231,183,.34); box-shadow:inset 0 5px 12px rgba(0,0,0,.6),0 0 26px -6px rgba(110,231,183,.5);
    }
    #village-page .v-plot[data-late-monthly="1"] .v-plate,
    #village-page .v-plot[data-late-initial="1"] .v-plate {
        border-color:rgba(251,113,133,.55);
        box-shadow:inset 0 5px 12px rgba(0,0,0,.6),0 0 26px -6px rgba(251,113,133,.6),0 0 0 1px rgba(251,113,133,.35);
    }
    #village-page .v-sprite {
        position:relative; z-index:1; width:158px; height:190px;
        transition:transform .18s ease;
        filter:drop-shadow(0 12px 14px rgba(0,0,0,.65));
    }
    #village-page .v-sprite img { width:100%; height:100%; display:block; }

    /* Card 6b07f356b6b30ea65c7bc2de — layer 1, the BODY.
       A paid building gets a gold roofline highlight so it reads as COMPLETE
       at a glance, not merely as "not grey". An unbilled one is desaturated
       so it recedes. Plate glow alone was too subtle to carry that. */
    #village-page .v-plot[data-initial-state="initial_paid"] .v-sprite::after {
        content:''; position:absolute; left:50%; top:-4%; transform:translateX(-50%);
        width:74%; height:22%; pointer-events:none; border-radius:50% 50% 0 0;
        background:radial-gradient(ellipse at 50% 100%, rgba(255,214,102,.55) 0%, rgba(255,214,102,0) 70%);
        mix-blend-mode:screen;
    }
    #village-page .v-plot[data-initial-state="initial_none"] .v-sprite img {
        filter:saturate(.35) brightness(.82);
    }

    /* Motion: ONLY a building still being paid in pulses. The badge never
       animates — a flashing warning becomes noise (card §D). */
    @keyframes v-pulse { 0%,100% { opacity:.55; } 50% { opacity:1; } }
    #village-page .v-plot[data-initial-state="initial_paying"] .v-plate {
        animation:v-pulse 2.6s ease-in-out infinite;
    }
    @media (prefers-reduced-motion: reduce) {
        #village-page .v-plot[data-initial-state="initial_paying"] .v-plate { animation:none; }
        #village-page .v-sprite, #village-page .v-plot { transition:none; }
    }
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

    /* Card 6b07f35fc3903d22f1b8444f — the SECOND axis. A Delayed marker sits on
       the building without replacing its Paid-up body, which is exactly Cyd's
       "Delayed and Paid up at this point". */
    /* Card 6b07f356b6b30ea65c7bc2de §C4: pinned to the BUILDING corner, not
       the plot corner. Anchoring to the sprite puts it on the roof where the
       eye already is, and keeps it clear of the nameplate below. */
    #village-page .v-alert {
        position:absolute; top:6%; right:6%; z-index:4;
        width:20px; height:20px; border-radius:50%;
        background:var(--v-rose); color:#2a0710;
        font-size:13px; font-weight:900; line-height:20px; text-align:center;
        box-shadow:0 0 0 2px rgba(12,20,34,.55), 0 2px 6px rgba(0,0,0,.45);
        pointer-events:none;
        /* the sprite is scaled per building; undo it so the badge is the same
           size on every roof and never depends on the building's scale */
        transform:scale(var(--v-badge-inv, 1));
    }
    #village-page .v-plot { position:relative; }
    #village-page .v-st-delayed { color:var(--v-rose); }
    #village-page .v-paid  { color:var(--v-mint); }
    #village-page .v-warn  { color:var(--v-gold); }
    #village-page .v-bad   { color:var(--v-rose); }
    #village-page .v-muted { color:var(--v-muted); }

    /* ── legend ── */
    #village-page .v-legend {
        display:flex; gap:15px; flex-wrap:wrap; justify-content:center;
        margin-top:26px; font-size:11px; color:#a9b6c9; position:relative; z-index:2;
    }
    #village-page .v-legend-badge { display:flex; align-items:center; gap:5px; }
    #village-page .v-legend-badge b {
        display:inline-block; width:15px; height:15px; border-radius:50%;
        background:var(--v-rose); color:#2a0710; font-size:10px; font-weight:900;
        line-height:15px; text-align:center;
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

    /* ── Landas billing block in the agency modal (card 6b07eb55) ── */
    #v-bill { margin-top:14px; border-top:1px solid rgba(148,163,184,.22); padding-top:12px; }
    #v-bill .v-bill-head {
        font-size:10px; text-transform:uppercase; letter-spacing:.09em;
        color:var(--v-muted); font-weight:800; margin-bottom:8px;
    }
    #v-bill .v-bill-load { font-size:12px; color:var(--v-muted); }

    /* Card 6b07f359a6a548392171d9de — the modal is a summary now, so the
       blocks have 3 to 6 facts each. auto-fit keeps them readable instead of
       squeezing "Next instalment" into a fixed third of the card. */
    #v-bill .v-bill-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:8px; margin-bottom:14px; }

    /* Two badges, never one: "Paid up" and "Payment due" coexist here on
       purpose — that is the whole correction this card carries. */
    #v-bill .v-bill-badges { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
    #v-bill .v-badge {
        font-size:11px; font-weight:800; padding:3px 9px; border-radius:999px;
        border:1px solid transparent; letter-spacing:.02em;
    }
    #v-bill .v-badge-paid { background:rgba(110,231,183,.16); color:#6ee7b7; border-color:rgba(110,231,183,.4); }
    #v-bill .v-badge-paying { background:rgba(255,209,102,.16); color:#ffd166; border-color:rgba(255,209,102,.4); }
    #v-bill .v-badge-none { background:rgba(148,163,184,.16); color:#a9b6c9; border-color:rgba(148,163,184,.35); }
    #v-bill .v-badge-due { background:rgba(251,113,133,.16); color:#fb7185; border-color:rgba(251,113,133,.45); }
    #v-bill .v-badge-due b { font-weight:900; }

    #v-bill .v-bill-note {
        font-size:11.5px; color:var(--v-muted); font-style:italic;
        padding:2px 0 14px;
    }

    /* Card 6b07f35c — the plan editor. Collapsed by default so a price change
       is never a stray click while scrolling to the payment history. */
    #v-bill .v-plan { margin:0 0 14px; }
    #v-bill .v-plan > summary {
        cursor:pointer; font-size:11px; font-weight:800; text-transform:uppercase;
        letter-spacing:.08em; color:#7dd3fc; padding:6px 0; list-style:none;
    }
    #v-bill .v-plan > summary::-webkit-details-marker { display:none; }
    #v-bill .v-plan > summary::before { content:'▸ '; }
    #v-bill .v-plan[open] > summary::before { content:'▾ '; }
    #v-bill .v-plan-warn {
        font-size:11.5px; line-height:1.45; padding:8px 10px; margin-bottom:10px;
        background:rgba(251,113,133,.10); border:1px solid rgba(251,113,133,.35);
        border-radius:8px; color:#fecdd3;
    }
    #v-bill .v-plan-warn b { color:#fb7185; }
    #v-bill .v-plan-per {
        font-size:11.5px; color:var(--v-muted); margin:2px 0 10px;
    }
    #v-bill .v-plan-per b { color:var(--v-ink); font-variant-numeric:tabular-nums; }
    #v-bill .v-plan-per i { font-style:normal; opacity:.6; }
    #v-bill .v-plan-ack {
        display:flex; align-items:flex-start; gap:8px; font-size:11.5px;
        margin-bottom:10px; cursor:pointer; line-height:1.4;
    }
    #v-bill .v-plan-ack input { width:auto; margin-top:2px; accent-color:#fb7185; }
    #v-bill .v-bill-grid > div {
        background:rgba(8,15,26,.42); border:1px solid rgba(148,163,184,.2);
        border-radius:8px; padding:7px 9px; display:flex; flex-direction:column; gap:1px;
    }
    #v-bill .v-bill-grid span { font-size:10px; color:var(--v-muted); text-transform:uppercase; letter-spacing:.06em; }
    #v-bill .v-bill-grid b { font-size:13px; font-variant-numeric:tabular-nums; }
    #v-bill .v-bill-grid em { font-style:normal; font-size:10.5px; color:var(--v-muted); }
    #v-bill .v-bill-types { display:flex; gap:14px; margin-bottom:8px; }
    #v-bill .v-bill-types label { display:flex; align-items:center; gap:6px; font-size:12px; cursor:pointer; }
    #v-bill .v-bill-types input { accent-color:#38bdf8; }
    #v-bill .v-bill-fields { display:grid; grid-template-columns:1fr 1fr; gap:7px 10px; }
    #v-bill .v-bill-fields label { display:flex; flex-direction:column; gap:3px; font-size:11px; }
    #v-bill .v-bill-fields label > span { color:var(--v-muted); }
    #v-bill .v-bill-fields label > span i { font-style:normal; opacity:.65; }
    #v-bill input, #v-bill select {
        background:rgba(8,15,26,.72); border:1px solid rgba(148,163,184,.34); border-radius:6px;
        padding:6px 8px; color:var(--v-ink); font-size:12px; font-family:inherit; width:100%;
    }
    #v-bill .v-bill-fields label[data-bad="1"] input,
    #v-bill .v-bill-fields label[data-bad="1"] select { border-color:rgba(244,63,94,.85); }
    #v-bill .v-bill-msg { font-style:normal; color:#fda4af; font-size:10.5px; }
    #v-bill .v-bill-err {
        margin:8px 0 0; padding:7px 10px; border-radius:7px; font-size:11.5px;
        background:rgba(244,63,94,.14); border:1px solid rgba(244,63,94,.42); color:#fecdd3;
    }
    #v-bill .v-na-submit { margin-top:10px; }
    #v-bill .v-bill-list { display:flex; flex-direction:column; gap:4px; }
    #v-bill .v-bill-row {
        display:grid; grid-template-columns:1fr auto auto auto; align-items:center; gap:8px;
        font-size:11.5px; padding:5px 7px; background:rgba(8,15,26,.32); border-radius:6px;
    }
    #v-bill .v-bill-row b { font-variant-numeric:tabular-nums; }
    #v-bill .v-bill-row em { font-style:normal; color:var(--v-muted); font-size:10.5px; }
    #v-bill .v-bill-void {
        background:transparent; border:1px solid rgba(244,63,94,.45); color:#fda4af;
        border-radius:5px; font-size:10px; padding:2px 7px; cursor:pointer; font-family:inherit;
    }
    #v-bill .v-bill-void:hover { background:rgba(244,63,94,.16); }

    /* ── modal: new agency (card 6b07eb521243cfef572cd549) ── */
    #v-na {
        position:fixed; inset:0; display:none; align-items:flex-start; justify-content:center;
        background:rgba(6,12,22,.62); z-index:70; padding:28px 16px; overflow:auto;
    }
    #v-na[data-open="1"] { display:flex; }
    #v-na .v-na-card {
        background:var(--v-panel,#16233a); border:1px solid rgba(125,211,252,.28); border-radius:14px;
        width:min(760px,100%); padding:20px 22px 18px; color:var(--v-ink); box-shadow:0 24px 60px rgba(0,0,0,.45);
    }
    #v-na .v-na-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
    #v-na .v-na-head h2 { font-size:17px; font-weight:800; margin:2px 0 0; }
    #v-na .v-na-err {
        margin:12px 0 0; padding:9px 12px; border-radius:8px; font-size:12.5px; line-height:1.45;
        background:rgba(244,63,94,.14); border:1px solid rgba(244,63,94,.42); color:#fecdd3;
    }
    #v-na .v-na-ok {
        background:rgba(52,211,153,.14); border-color:rgba(52,211,153,.45); color:#a7f3d0;
    }
    #v-na .v-na-fs { border:0; margin:16px 0 0; padding:0; }
    #v-na .v-na-fs legend {
        font-size:10px; text-transform:uppercase; letter-spacing:.09em;
        color:var(--v-muted); font-weight:800; padding:0 0 7px;
    }
    #v-na .v-na-grid { display:grid; grid-template-columns:1fr 1fr; gap:9px 12px; }
    #v-na .v-na-wide { grid-column:1 / -1; }
    #v-na .v-na-f { display:flex; flex-direction:column; gap:4px; font-size:11.5px; }
    #v-na .v-na-f > span { color:var(--v-muted); }
    #v-na .v-na-f > span i { font-style:normal; opacity:.65; }
    #v-na input, #v-na select {
        background:rgba(8,15,26,.72); border:1px solid rgba(148,163,184,.34); border-radius:7px;
        padding:7px 9px; color:var(--v-ink); font-size:12.5px; font-family:inherit; width:100%;
    }
    #v-na input:focus, #v-na select:focus { outline:none; border-color:rgba(125,211,252,.75); }
    #v-na .v-na-f[data-bad="1"] input, #v-na .v-na-f[data-bad="1"] select { border-color:rgba(244,63,94,.85); }
    #v-na .v-na-msg { font-style:normal; color:#fda4af; font-size:11px; min-height:0; }
    #v-na .v-na-pw { display:flex; gap:7px; }
    #v-na .v-na-gen {
        flex:0 0 auto; background:rgba(125,211,252,.16); border:1px solid rgba(125,211,252,.4);
        color:var(--v-ink); border-radius:7px; padding:0 11px; font-size:11.5px; cursor:pointer; font-family:inherit;
    }
    #v-na .v-na-actions { display:flex; justify-content:flex-end; gap:9px; margin-top:18px; }
    #v-na .v-na-submit {
        background:#38bdf8; border:0; color:#062033; font-weight:800; font-size:12.5px;
        padding:9px 18px; border-radius:8px; cursor:pointer; font-family:inherit;
    }
    #v-na .v-na-submit[disabled] { opacity:.6; cursor:default; }
    @media (max-width:620px) { #v-na .v-na-grid { grid-template-columns:1fr; } }
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
            {{-- Card 6b07eb521243cfef572cd549 — the primary way to stand up a
                 new tenant. Opens the modal; never navigates to a page. --}}
            <button type="button" class="v-chip" id="v-new-agency" title="Create a new agency" style="font-size:11px;font-weight:700"><i>＋</i>New agency</button>
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
                @php
                    // Card 6b07f35fc3903d22f1b8444f — two independent axes.
                    // The body/label still comes from the compatibility `state`
                    // so the existing sprite mapping is untouched; the alert
                    // marker is the SECOND axis and is what makes
                    // "Paid up + Delayed" visible as one building.
                    $meta = $stateMeta[$b['state']];
                    $initialLabel = [
                        'initial_none'   => 'No billing yet',
                        'initial_paying' => 'Paying in',
                        'initial_paid'   => 'Paid up',
                    ][$b['initial_state']] ?? $meta['label'];
                    $lateInitial = (bool) ($b['late_initial'] ?? false);
                    $lateMonthly = (bool) ($b['late_monthly'] ?? false);
                    // AC7: the accessible name states BOTH axes, because the
                    // whole point is that they are independent facts.
                    $a11y = $b['name'].' — '.$initialLabel.($lateMonthly || $lateInitial ? ', payment overdue' : '');
                @endphp
                <button type="button" class="v-plot"
                        data-state="{{ $b['state'] }}"
                        data-initial-state="{{ $b['initial_state'] }}"
                        data-monthly-state="{{ $b['monthly_state'] }}"
                        data-late-initial="{{ $lateInitial ? '1' : '0' }}"
                        data-late-monthly="{{ $lateMonthly ? '1' : '0' }}"
                        data-due-initial="{{ $b['initial_due_on'] }}"
                        data-due-monthly="{{ $b['monthly_due_on'] }}"
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
                        title="{{ $a11y }}"
                        aria-label="{{ $a11y }}">
                    <div class="v-plate"></div>
                    <div class="v-sprite" style="transform:scale({{ $b['scale'] }}); --v-badge-inv:{{ round(1 / max($b['scale'], 0.01), 4) }}">
                        <img src="{{ asset('images/village/'.$b['sprite'].'.svg') }}" alt="">
                        @if ($lateMonthly || $lateInitial)
                            <span class="v-alert" title="Delayed">!</span>
                        @endif
                    </div>
                    <div class="v-lbl">
                        <div class="v-n">{{ $b['name'] }}</div>
                        <div class="v-st {{ $meta['cls'] }}">
                            <span class="v-dot" style="background:{{ $meta['dot'] }}"></span>{{ $initialLabel }}
                            @if ($lateMonthly || $lateInitial)
                                <span class="v-st-delayed">· Delayed</span>
                            @endif
                        </div>
                    </div>
                </button>
                {{-- AC9: the billing summary ships WITH the page. One script
                     tag per building holding JSON; the modal reads it and
                     makes no request. Rendered as application/json so it is
                     never executed and never treated as markup. --}}
                @if (isset($billingByAgency[$b['id']]))
                    <script type="application/json" id="v-bill-{{ $b['id'] }}" class="v-bill-src">{!! json_encode($billingByAgency[$b['id']], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                @endif
            @endforeach
        </div>

        <div class="v-legend">
            <span><i style="background:var(--v-muted)"></i>No billing yet</span>
            <span><i style="background:var(--v-gold)"></i>Paying in</span>
            <span><i style="background:var(--v-mint)"></i>Paid up</span>
            <span><i style="background:var(--v-rose)"></i>Delayed</span>
            <span class="v-legend-badge"><b>!</b> A red badge means a payment is due</span>
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

{{-- ── Modal: new agency (card 6b07eb521243cfef572cd549) ────────────────
     Three sections in one submit: identity, the commercial agreement, and the
     first admin account. Plain form inside the existing modal shell, submitted
     with fetch() so validation errors land here instead of wiping the form. --}}
<div id="v-na" data-open="0" role="dialog" aria-modal="true" aria-labelledby="v-na-title">
    <form class="v-na-card" id="v-na-form" method="POST" action="{{ route('godmode.village.agencies.store') }}" novalidate>
        @csrf
        <div class="v-na-head">
            <div>
                <div class="v-kicker">Landas · New tenant</div>
                <h2 id="v-na-title">New agency</h2>
            </div>
            <button type="button" class="v-close" data-na-close aria-label="Close">✕</button>
        </div>

        <div class="v-na-err" id="v-na-err" hidden></div>
        <div class="v-na-err v-na-ok" id="v-na-done" hidden></div>

        <fieldset class="v-na-fs">
            <legend>A · Tenant</legend>
            <div class="v-na-grid">
                <label class="v-na-f" data-f="name"><span>Agency name</span><input name="name" required maxlength="255" autocomplete="off"><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="subdomain"><span>Subdomain</span><input name="subdomain" required maxlength="63" autocomplete="off" placeholder="auto from name"><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="status"><span>Status</span><select name="status"><option value="active">active</option><option value="pending">pending</option></select><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="num_branches"><span>Number of branches</span><input name="num_branches" type="number" min="0" max="200" value="1"><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="contact_person"><span>Contact person</span><input name="contact_person" required maxlength="255" autocomplete="off"><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="email"><span>Email <i>(optional)</i></span><input name="email" type="email" maxlength="255" autocomplete="off"><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="city"><span>City <i>(optional)</i></span><input name="city" maxlength="100" autocomplete="off"><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="address"><span>Address <i>(optional)</i></span><input name="address" maxlength="255" autocomplete="off"><em class="v-na-msg"></em></label>
            </div>
        </fieldset>

        <fieldset class="v-na-fs">
            <legend>B · Commercial agreement</legend>
            <div class="v-na-grid">
                <label class="v-na-f" data-f="initial_payment"><span>Initial payment (₱)</span><input name="initial_payment" type="number" step="0.01" min="0" required><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="initial_terms"><span>Initial clears in (payments)</span><input name="initial_terms" type="number" min="1" max="120" value="1" required><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="monthly_amount"><span>Monthly amount (₱)</span><input name="monthly_amount" type="number" step="0.01" min="0" required><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="terms"><span>Number of monthly payments</span><input name="terms" type="number" min="1" max="120" value="12" required><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="starts_on"><span>Starts on</span><input name="starts_on" type="date" required value="{{ now()->toDateString() }}"><em class="v-na-msg"></em></label>
                <label class="v-na-f v-na-wide" data-f="notes"><span>Notes <i>(optional)</i></span><input name="notes" maxlength="2000" autocomplete="off"><em class="v-na-msg"></em></label>
            </div>
        </fieldset>

        <fieldset class="v-na-fs">
            <legend>C · First admin account</legend>
            <div class="v-na-grid">
                <label class="v-na-f" data-f="admin_name"><span>Admin name</span><input name="admin_name" required maxlength="255" autocomplete="off"><em class="v-na-msg"></em></label>
                <label class="v-na-f" data-f="admin_email"><span>Admin email</span><input name="admin_email" type="email" required maxlength="255" autocomplete="off"><em class="v-na-msg"></em></label>
                <label class="v-na-f v-na-wide" data-f="admin_password"><span>Admin password</span>
                    <div class="v-na-pw">
                        <input name="admin_password" type="text" required minlength="8" autocomplete="new-password">
                        <button type="button" class="v-na-gen" id="v-na-gen">Generate</button>
                    </div><em class="v-na-msg"></em>
                </label>
            </div>
        </fieldset>

        <div class="v-na-actions">
            <button type="button" class="v-a" data-na-close>Cancel</button>
            <button type="submit" class="v-na-submit" id="v-na-submit">Create agency</button>
        </div>
    </form>
</div>

<script>
(function () {
    var modal   = document.getElementById('v-modal');
    var kicker  = document.getElementById('v-modal-kicker');
    var title   = document.getElementById('v-modal-title');
    var body    = document.getElementById('v-modal-body');
    var actions = document.getElementById('v-modal-actions');
    var lastFocus = null;

    // One CSRF token for the whole page: every write in the village modal
    // (create agency, record payment, void) reuses it from a hidden field the
    // create-agency form already carries.
    var CSRF = (function () {
        var el = document.querySelector('#v-na-form input[name="_token"]');
        return el ? el.value : '';
    })();

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

        // Card 6b07f359a6a548392171d9de: the modal is now the Landas billing
        // summary. The old rows here were `receivables` — the agency's own
        // money, not what it owes Landas — which made "Outstanding ₱0" next to
        // a red building look like a contradiction. Receivables still exist in
        // the agency's own screens; they do not belong in this one.
        body.innerHTML = '<div id="v-bill" data-agency="' + esc(d.agency) + '">' +
                         '<div class="v-bill-load">Loading billing…</div></div>';
        actions.innerHTML =
            '<a class="v-a" href="/godmode/payments/' + encodeURIComponent(d.agency) + '">View full ledger →</a>' +
            '<a class="v-a" href="/godmode/agencies/' + encodeURIComponent(d.agency) + '/settings">Agency settings →</a>';
        openModal();
        loadBilling(d.agency);
    }

    /* ── Landas billing (card 6b07eb557762396b008a7080) ───────────────────
       Record what an agency paid LANDAS. Distinct from the "Collected" rows
       above, which are agency-internal receivables. Two payment types, and
       the amount pre-fills from the outstanding for whichever is picked. */
    var BILL_STATE = null;

    function loadBilling(agencyId) {
        var host = document.getElementById('v-bill');
        if (!host) { return; }

        // AC9: no fetch. The summary was computed server-side and is sitting in
        // a <script type="application/json"> next to the building. If the
        // element is missing we say so rather than quietly hitting the API —
        // a missing payload is a bug worth seeing, not worth papering over.
        var src = document.getElementById('v-bill-' + agencyId);
        if (!src) {
            host.innerHTML = '<div class="v-bill-load">No billing data was shipped for this agency.</div>';
            return;
        }

        var s;
        try { s = JSON.parse(src.textContent); }
        catch (err) {
            host.innerHTML = '<div class="v-bill-load">Billing data could not be read.</div>';
            return;
        }

        BILL_STATE = s;
        host.innerHTML = billingHtml(s, agencyId);
        wireBilling(host, agencyId);
    }

    function billingHtml(s, agencyId) {
        if (!s.has_plan) {
            // Card 6b07f35ccd9659edc7b8438e: this used to dead-end on a "Set
            // one" link to the agency settings page, which has no billing
            // fields at all — so an agency without a plan could not be given one
            // anywhere in the console. The editor is the only place that can
            // create a plan, so it renders here too, with the create path.
            return '<div class="v-bill-head">Landas billing</div>' +
                   '<div class="v-bill-load" style="margin-bottom:12px">No billing plan yet.</div>' +
                   planForm({ plan: {}, payment_count: 0, total_paid: '0.00' }, agencyId);
        }

        // ── Two badges, never one (card §Header). An agency can be Paid up
        // AND Delayed; collapsing them to a single chip erases a real fact.
        var h = '<div class="v-bill-badges">' +
                '<span class="v-badge ' + badgeCls(s.initial_state) + '">' + esc(initialLabel(s.initial_state)) + '</span>';
        if (s.late_monthly || s.late_initial) {
            h += '<span class="v-badge v-badge-due"><b>!</b> Payment due</span>';
        }
        h += '</div>';

        // ── Initial payment block ──
        h += '<div class="v-bill-head">Initial payment</div>';
        h += blk([
            ['Agreed', PESO(s.initial_due) + (s.initial_terms ? ' over ' + s.initial_terms + ' payments' : '')],
            // AC2/Card 6b07f35c: the progress line is money AND count, in the
            // card's exact wording. `initial_count` is a ROW count — a partial
            // payment raises the money here without advancing "n of N".
            ['Paid so far', PESO(s.initial_paid) + ' of ' + PESO(s.initial_due)],
            ['Progress', (s.initial_progress ? PESO(s.initial_progress.paid) + ' of ' + PESO(s.initial_due) + ' paid' : '') +
                         ' — ' + s.initial_count + ' of ' + (s.initial_terms || 0) + ' initial payments'],
            ['State', initialLabel(s.initial_state) + (s.initial_cleared_on ? ' — cleared ' + esc(fmtDate(s.initial_cleared_on)) : '')],
            ['First payment', s.anchor_on ? esc(fmtDate(s.anchor_on)) : '—'],
            ['Next instalment', s.initial_due_on ? esc(fmtDate(s.initial_due_on)) + (s.late_initial ? ' · OVERDUE' : '') : '—'],
        ]);

        // ── Monthly block: only once the initial is cleared (AC3). A monthly
        // obligation beside an unclear initial is arithmetic the operator
        // cannot reconcile, so it is withheld with a note rather than zeroed.
        if (s.initial_cleared) {
            h += '<div class="v-bill-head">Monthly retainer</div>';
            h += blk([
                ['Amount', PESO(s.monthly_amount) + (s.monthly_terms ? ' × ' + s.monthly_terms + ' months' : '')],
                ['Paid so far', PESO(s.monthly_paid) + ' of ' + PESO(s.monthly_due)],
                ['Months paid', s.monthly_count + ' of ' + (s.monthly_expected || 0) + ' due so far'],
                ['Next due', s.monthly_due_on ? esc(fmtDate(s.monthly_due_on)) + (s.late_monthly ? ' · OVERDUE' : '') : '—'],
            ]);
        } else {
            h += '<div class="v-bill-note">Monthly billing starts once the initial is cleared.</div>';
        }

        // ── Totals (AC4, AC5) ──
        var bal = parseFloat(s.balance || 0);
        h += '<div class="v-bill-head">Totals</div>';
        h += blk([
            ['Contract value', PESO(s.total_contract)],
            ['Total paid', PESO(s.total_paid)],
            ['Balance remaining', '<span class="' + (bal > 0 ? 'v-bad' : (bal < 0 ? 'v-paid' : '')) + '">' + PESO(s.balance) + '</span>' +
                                 (bal < 0 ? ' <em>overpaid</em>' : '')],
        ]);

        // ── Payment history (AC6) ──
        if (s.payments && s.payments.length) {
            var shown = s.payments.slice(0, 10);
            h += '<div class="v-bill-head">Payments <em>' + s.payment_count + '</em></div><div class="v-bill-list">';
            shown.forEach(function (p) {
                h += '<div class="v-bill-row"><span>' + esc(fmtDate(p.paid_on)) + ' · ' + esc(p.payment_type) + '</span>' +
                     '<b>' + PESO(p.amount) + '</b>' +
                     '<em>' + esc(p.mode || '') + (p.recorder ? ' · ' + esc(p.recorder.name) : '') +
                     (p.reference ? ' · ' + esc(p.reference) : '') + '</em>' +
                     '<button type="button" class="v-bill-void" data-void="' + esc(p.id) + '">Void</button></div>';
            });
            h += '</div>';
            if (s.payment_count > shown.length) {
                h += '<div class="v-bill-load">Showing ' + shown.length + ' of ' + s.payment_count +
                     '. <a class="v-a" href="/godmode/payments/' + encodeURIComponent(agencyId) + '">View full ledger</a></div>';
            }
        }

        return h + planForm(s, agencyId) + recordForm(s, agencyId);
    }

    function blk(rows) {
        var h = '<div class="v-bill-grid">';
        rows.forEach(function (r) {
            h += '<div><span>' + esc(r[0]) + '</span><b>' + r[1] + '</b></div>';
        });
        return h + '</div>';
    }

    /* After any write the page's baked summary is stale. Reload it with the
       building marked so the modal comes straight back up. */
    function replayBilling(agencyId) {
        var u = new URL(window.location.href);
        u.searchParams.set('open', agencyId);
        window.location.replace(u.toString());
    }

    function initialLabel(st) {
        return st === 'initial_paid' ? 'Paid up'
             : st === 'initial_paying' ? 'Paying in'
             : 'No billing yet';
    }

    function badgeCls(st) {
        return st === 'initial_paid' ? 'v-badge-paid'
             : st === 'initial_paying' ? 'v-badge-paying'
             : 'v-badge-none';
    }

    /* ── Edit the agreement (card 6b07f35ccd9659edc7b8438e) ──────────────
       Collapsed by default: changing a price is a deliberate act, not
       something to scroll past. Saving SUPERSEDES the plan server-side, so
       the copy here says "replace", not "edit in place".

       AC6: when money already exists the confirm box is shown and required.
       It states the amount plainly rather than a vague "are you sure". */
    function planForm(s, agencyId) {
        var hasMoney = (s.payment_count || 0) > 0;
        var p = s.plan || {};
        var isCreate = !s.has_plan;
        var d = (p.starts_on || '').slice(0, 10) || today();

        var h = '<details class="v-plan"' + (isCreate ? ' open' : '') + '>' +
                '<summary>' + (isCreate ? 'Set a billing plan' : 'Edit billing plan') + '</summary>' +
                '<form class="v-plan-form" data-agency="' + esc(agencyId) + '">';

        if (hasMoney) {
            h += '<div class="v-plan-warn"><b>This agency has ' + PESO(s.total_paid) +
                 ' recorded.</b> Changing the agreement will not alter past payments. ' +
                 'The current plan is kept and replaced by a new one.</div>';
        }

        h += '<div class="v-bill-fields">' +
             '<label data-f="initial_payment"><span>Initial payment</span>' +
             '<input name="initial_payment" type="number" step="0.01" min="0" required value="' + esc(p.initial_payment || '0.00') + '"><em class="v-bill-msg"></em></label>' +
             '<label data-f="initial_terms"><span>Payments to clear it</span>' +
             '<input name="initial_terms" type="number" step="1" min="1" required value="' + esc(p.initial_terms || 1) + '"><em class="v-bill-msg"></em></label>' +
             '<label data-f="monthly_amount"><span>Monthly amount</span>' +
             '<input name="monthly_amount" type="number" step="0.01" min="0" required value="' + esc(p.monthly_amount || '0.00') + '"><em class="v-bill-msg"></em></label>' +
             '<label data-f="terms"><span>Months</span>' +
             '<input name="terms" type="number" step="1" min="1" required value="' + esc(p.terms || 12) + '"><em class="v-bill-msg"></em></label>' +
             '<label data-f="starts_on"><span>Starts on</span>' +
             '<input name="starts_on" type="date" required value="' + esc(d) + '"><em class="v-bill-msg"></em></label>' +
             '<label data-f="notes"><span>Notes <i>(optional)</i></span>' +
             '<input name="notes" maxlength="1000" value="' + esc(p.notes || '') + '"><em class="v-bill-msg"></em></label>' +
             '</div>';

        h += '<div class="v-plan-per">Per instalment: <b id="v-plan-per">—</b> <i>(derived, never stored)</i></div>';

        if (hasMoney) {
            h += '<label class="v-plan-ack"><input type="checkbox" name="acknowledged" value="1">' +
                 '<span>I understand past payments stay exactly as recorded.</span></label>';
        }

        h += '<div class="v-bill-err" hidden></div>' +
             '<button type="submit" class="v-na-submit">' + (isCreate ? 'Set billing plan' : 'Replace billing plan') + '</button>' +
             '</form></details>';

        return h;
    }

    function recordForm(s, agencyId) {
        var h = '<div class="v-bill-head">Record a payment</div>';
        h += '<form class="v-bill-form" data-agency="' + esc(agencyId) + '">' +
             '<div class="v-bill-types">' +
             '<label><input type="radio" name="payment_type" value="initial" required><span>Initial payment</span></label>' +
             '<label><input type="radio" name="payment_type" value="monthly" required><span>Monthly payment</span></label>' +
             '</div>' +
             '<div class="v-bill-fields">' +
             '<label data-f="amount"><span>Amount</span><input name="amount" type="number" step="0.01" min="0.01" required><em class="v-bill-msg"></em></label>' +
             '<label data-f="paid_on"><span>Paid on</span><input name="paid_on" type="date" required value="' + today() + '"><em class="v-bill-msg"></em></label>' +
             '<label data-f="mode"><span>Mode</span><select name="mode">' +
             ['Cash','Cheque','Fund Transfer','Credit','GCash'].map(function (m) { return '<option>' + m + '</option>'; }).join('') +
             '</select><em class="v-bill-msg"></em></label>' +
             '<label data-f="reference"><span>Reference <i>(optional)</i></span><input name="reference" maxlength="255"><em class="v-bill-msg"></em></label>' +
             '</div>' +
             '<div class="v-bill-err" hidden></div>' +
             '<button type="submit" class="v-na-submit">Record payment</button>' +
             '</form>';
        return h;
    }

    function today() {
        var d = new Date();
        var m = ('0' + (d.getMonth() + 1)).slice(-2);
        var day = ('0' + d.getDate()).slice(-2);
        return d.getFullYear() + '-' + m + '-' + day;
    }

    function fmtDate(s) {
        if (!s) { return ''; }
        return String(s).slice(0, 10);
    }

    /* Card 6b07f35ccd9659edc7b8438e — per-instalment is derived for display
       and never persisted, so it is computed here from the two live inputs
       rather than shipped from the server. */
    function wirePlanForm(host, agencyId) {
        var form = host.querySelector('.v-plan-form');
        if (!form) { return; }

        var perOut = form.querySelector('#v-plan-per');
        function recompute() {
            var amt = parseFloat(form.elements['initial_payment'].value || 0);
            var n = parseInt(form.elements['initial_terms'].value || 0, 10);
            perOut.textContent = (n > 0 && amt >= 0) ? PESO(amt / n) : '—';
        }
        form.elements['initial_payment'].addEventListener('input', recompute);
        form.elements['initial_terms'].addEventListener('input', recompute);
        recompute();

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var errBox = form.querySelector('.v-bill-err');
            errBox.hidden = true;
            form.querySelectorAll('.v-bill-fields label').forEach(function (l) {
                l.removeAttribute('data-bad');
                var m = l.querySelector('.v-bill-msg'); if (m) { m.textContent = ''; }
            });

            var ack = form.querySelector('input[name="acknowledged"]');
            // AC6: the browser asks once more, in plain words, because this
            // changes what the client owes.
            if (ack && !ack.checked) {
                errBox.textContent = 'Tick the box to confirm past payments stay as recorded.';
                errBox.hidden = false;
                return;
            }

            var btn = form.querySelector('.v-na-submit');
            var resetLabel = btn.textContent;
            btn.disabled = true; btn.textContent = 'Saving…';

            fetch('/godmode/village/agencies/' + encodeURIComponent(agencyId) + '/plan', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form),
                credentials: 'same-origin'
            }).then(function (r) {
                return r.json().catch(function () { return {}; }).then(function (d) { return { status: r.status, data: d }; });
            }).then(function (res) {
                if (res.data && res.data.ok) { replayBilling(agencyId); return; }
                btn.disabled = false; btn.textContent = resetLabel;
                var errs = (res.data && res.data.errors) || {};
                var general = [];
                Object.keys(errs).forEach(function (k) {
                    var el = form.querySelector('[data-f="' + k + '"]');
                    var msg = Array.isArray(errs[k]) ? errs[k][0] : errs[k];
                    if (el) { el.setAttribute('data-bad', '1'); el.querySelector('.v-bill-msg').textContent = msg; }
                    else { general.push(msg); }
                });
                if (general.length) { errBox.textContent = general.join(' '); errBox.hidden = false; }
            }).catch(function () {
                btn.disabled = false; btn.textContent = resetLabel;
                errBox.textContent = 'Network error. The plan was not changed.';
                errBox.hidden = false;
            });
        });
    }

    function wireBilling(host, agencyId) {
        wirePlanForm(host, agencyId);

        var form = host.querySelector('.v-bill-form');
        if (!form) { return; }
        var amount = form.elements['amount'];
        var radios = form.querySelectorAll('input[name="payment_type"]');

        // Pre-fill from what is still owed for the chosen type. Choosing a
        // type is required, so nothing is pre-filled until one is picked.
        for (var i = 0; i < radios.length; i++) {
            radios[i].addEventListener('change', function () {
                var type = this.value;
                var owed = type === 'initial' ? BILL_STATE.initial_outstanding : BILL_STATE.monthly_amount;
                amount.value = parseFloat(owed) > 0 ? parseFloat(owed).toFixed(2) : '';
                amount.placeholder = parseFloat(owed) > 0 ? owed : 'nothing outstanding';
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var errBox = form.querySelector('.v-bill-err');
            errBox.hidden = true;
            form.querySelectorAll('.v-bill-fields label').forEach(function (l) {
                l.removeAttribute('data-bad');
                var m = l.querySelector('.v-bill-msg'); if (m) { m.textContent = ''; }
            });
            var btn = form.querySelector('.v-na-submit');
            btn.disabled = true; btn.textContent = 'Recording…';

            fetch('/godmode/village/agencies/' + encodeURIComponent(agencyId) + '/payments', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form),
                credentials: 'same-origin'
            }).then(function (r) {
                return r.json().catch(function () { return {}; }).then(function (data) { return { status: r.status, data: data }; });
            }).then(function (res) {
                if (res.status === 201 || (res.data && res.data.ok)) {
                    // The summary is baked into the page (AC9), so refreshing it
                    // means going back to the server. Reload with the building
                    // flagged so the modal reopens on the updated numbers —
                    // without this the operator records a payment and stares at
                    // the old balance. No XHR here either: this is a navigation.
                    replayBilling(agencyId);
                    return;
                }
                btn.disabled = false; btn.textContent = 'Record payment';
                var errs = (res.data && res.data.errors) || {};
                var general = [];
                Object.keys(errs).forEach(function (k) {
                    var el = form.querySelector('[data-f="' + k + '"]');
                    var msg = Array.isArray(errs[k]) ? errs[k][0] : errs[k];
                    if (el) { el.setAttribute('data-bad', '1'); el.querySelector('.v-bill-msg').textContent = msg; }
                    else { general.push(msg); }
                });
                if (general.length) { errBox.textContent = general.join(' '); errBox.hidden = false; }
            }).catch(function () {
                btn.disabled = false; btn.textContent = 'Record payment';
                errBox.textContent = 'Network error. Nothing was recorded.';
                errBox.hidden = false;
            });
        });

        // Void is the only correction. No edit, by design.
        host.querySelectorAll('[data-void]').forEach(function (b) {
            b.addEventListener('click', function () {
                if (!window.confirm('Void this payment? It leaves every total but stays in the audit trail.')) { return; }
                b.disabled = true;
                fetch('/godmode/village/agencies/' + encodeURIComponent(agencyId) + '/payments/' + encodeURIComponent(b.dataset.void) + '/void', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: '_token=' + encodeURIComponent(CSRF),
                    credentials: 'same-origin'
                }).then(function () { replayBilling(agencyId); })
                  .catch(function () { b.disabled = false; });
            });
        });
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

    // Reopen the building named in ?open= — set by replayBilling() after a
    // recorded or voided payment so the modal returns with fresh numbers.
    (function () {
        var want = new URLSearchParams(window.location.search).get('open');
        if (!want) { return; }
        var el = document.querySelector('.v-plot[data-agency="' + want.replace(/[^0-9]/g, '') + '"]');
        if (el) { agencyModal(el); }
    })();

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

    /* ── New agency modal (card 6b07eb521243cfef572cd549) ─────────────────
       Three rows are written in one transaction server-side; here we only
       have to (a) not lose what the operator typed when validation fails and
       (b) not let a double-click create two tenants. Errors come back as
       Laravel's 422 `errors` bag and land under the field that caused them. */
    var na        = document.getElementById('v-na');
    var naForm    = document.getElementById('v-na-form');
    var naErr     = document.getElementById('v-na-err');
    var naSubmit  = document.getElementById('v-na-submit');
    var naGen     = document.getElementById('v-na-gen');
    var naOpenBtn = document.getElementById('v-new-agency');
    var naSub     = naForm ? naForm.elements['subdomain'] : null;

    // Auto-suggest the subdomain from the name, but stop the moment the
    // operator types their own: an overridden value must never be rewritten.
    var subTouched = false;
    if (naSub) {
        naSub.addEventListener('input', function () { subTouched = true; });
    }
    function slug(s) {
        return String(s || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 63);
    }

    function naClearErrors() {
        naErr.hidden = true;
        naErr.textContent = '';
        var fields = naForm.querySelectorAll('.v-na-f');
        for (var i = 0; i < fields.length; i++) {
            fields[i].removeAttribute('data-bad');
            var m = fields[i].querySelector('.v-na-msg');
            if (m) { m.textContent = ''; }
        }
    }

    function naShowErrors(errors) {
        naClearErrors();
        var first = null;
        var general = [];
        Object.keys(errors).forEach(function (key) {
            var el = naForm.querySelector('.v-na-f[data-f="' + key + '"]');
            var msg = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
            if (el) {
                el.setAttribute('data-bad', '1');
                var slot = el.querySelector('.v-na-msg');
                if (slot) { slot.textContent = msg; }
                if (!first) { first = el; }
            } else {
                general.push(msg);
            }
        });
        if (general.length) {
            naErr.textContent = general.join(' ');
            naErr.hidden = false;
        }
        if (first) {
            var input = first.querySelector('input,select');
            if (input) { input.focus(); }
        }
    }

    function naOpen() {
        naClearErrors();
        na.setAttribute('data-open', '1');
        var nameInput = naForm.elements['name'];
        if (nameInput) { nameInput.focus(); }
    }

    function naClose() {
        na.setAttribute('data-open', '0');
        if (naOpenBtn) { naOpenBtn.focus(); }
    }

    if (naOpenBtn) { naOpenBtn.addEventListener('click', naOpen); }

    if (na) {
        na.addEventListener('click', function (e) {
            if (e.target.closest('[data-na-close]') || e.target === na) { naClose(); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && na.getAttribute('data-open') === '1') { naClose(); }
        });
    }

    var naName = naForm ? naForm.elements['name'] : null;
    if (naName && naSub) {
        naName.addEventListener('input', function () {
            if (!subTouched) { naSub.value = slug(naName.value); }
        });
    }

    if (naGen) {
        naGen.addEventListener('click', function () {
            // Operator-visible on purpose: they have to hand this to the client.
            var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
            var out = '';
            var arr = new Uint32Array(16);
            (window.crypto || window.msCrypto).getRandomValues(arr);
            for (var i = 0; i < arr.length; i++) { out += chars[arr[i] % chars.length]; }
            naForm.elements['admin_password'].value = out;
        });
    }

    if (naForm) {
        naForm.addEventListener('submit', function (e) {
            e.preventDefault();
            naClearErrors();
            naSubmit.disabled = true;
            naSubmit.textContent = 'Creating…';

            fetch(naForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': naForm.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(naForm),
                credentials: 'same-origin'
            }).then(function (r) {
                return r.json().catch(function () {
                    return { errors: { _: ['The server answered with something unexpected. Nothing was created.'] } };
                }).then(function (data) {
                    return { status: r.status, data: data };
                });
            }).then(function (res) {
                if (res.status === 201 || (res.data && res.data.ok)) {
                    // The tenant exists at this point, and so does its admin.
                    // Show the note that survives the reload (the subdomain URL
                    // is still a manual step — card 6b099ed8), then reload so
                    // the new building is laid out server-side.
                    var note = document.getElementById('v-na-done');
                    if (note && res.data && res.data.message) {
                        note.textContent = res.data.message;
                        note.hidden = false;
                    }
                    setTimeout(function () { window.location.reload(); }, 2600);
                    return;
                }
                naSubmit.disabled = false;
                naSubmit.textContent = 'Create agency';
                naShowErrors((res.data && res.data.errors) || {
                    _: [(res.data && res.data.message) || 'Could not create the agency.']
                });
            }).catch(function () {
                naSubmit.disabled = false;
                naSubmit.textContent = 'Create agency';
                naShowErrors({ _: ['Network error. Nothing was created — check and try again.'] });
            });
        });
    }
})();
</script>

@endsection
