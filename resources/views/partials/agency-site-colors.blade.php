{{-- Agency banner color override.
     When the agency picks a banner color, every gold gradient on the site
     (hero banners, tinted headers, gradient text) adopts it. The gradient
     end and readable text color are auto-derived in app_site_banner_style(). --}}
@php($bannerStyle = app_site_banner_style())
@if($bannerStyle)
<style>
    :root { {{ $bannerStyle }} }
    [class*="from-primary"], [class*="via-primary"], [class*="to-secondary"] {
        --color-primary: var(--banner-c1);
        --color-secondary: var(--banner-c2);
        --color-primary-content: var(--banner-content);
    }
</style>
@endif
