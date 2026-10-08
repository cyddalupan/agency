{{-- Agency Color Theme override.
     When an agency picks a Color Theme (Settings → Color Theme), the DaisyUI
     colour tokens are overridden on :root so the whole system adopts the
     palette. The 'default' theme emits nothing (Landas gold). Mirrors the
     banner-colour override in partials/agency-site-colors.blade.php. --}}
@php($colorThemeStyle = app_color_theme_style())
@if($colorThemeStyle)
<style>
    :root { {{ $colorThemeStyle }} }
</style>
@endif
