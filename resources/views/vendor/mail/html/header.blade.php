@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Agency App')
<img src="{{ asset('images/agency-logo.png') }}" class="logo" alt="Agency App Logo" style="height:65px;width:auto;margin-top:10px;margin-bottom:10px;border-radius:12px;">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
