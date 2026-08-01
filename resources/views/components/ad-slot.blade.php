@props(['ads', 'placement', 'label'])

@if($ads->canServe($placement))
    <aside class="home-ad-slot" aria-label="Advertisement">
        <span class="home-ad-label">Advertisement</span>
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="{{ $ads->publisher_id }}"
             data-ad-slot="{{ $ads->slot($placement) }}"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>(window.adsbygoogle = window.adsbygoogle || []).push({});</script>
    </aside>
@elseif($ads->show_placeholders && $ads->placementEnabled($placement))
    <aside class="home-ad-slot home-ad-placeholder" aria-label="Advertisement placeholder">
        <span class="home-ad-label">Advertisement</span>
        <div><i class="bi bi-badge-ad"></i><strong>{{ $label }}</strong><small>Ad space available</small></div>
    </aside>
@endif
