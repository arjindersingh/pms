@php($compatibility = $systemCompatibility ?? ['supported' => true, 'issues' => [], 'browser' => 'Unknown', 'browser_version' => null, 'operating_system' => 'Unknown'])
<noscript>
    <div class="compatibility-backdrop">
        <section class="compatibility-dialog" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            <h1>JavaScript is required</h1>
            <p>This portal needs JavaScript for navigation, forms, validation, and account security. Enable JavaScript and reload this page.</p>
        </section>
    </div>
</noscript>
<div class="compatibility-backdrop" data-compatibility-gate hidden>
    <section class="compatibility-dialog" role="alertdialog" aria-labelledby="compatibility-title" aria-describedby="compatibility-description">
        <i class="bi bi-display"></i>
        <h1 id="compatibility-title">Your device needs an update</h1>
        <p id="compatibility-description">This portal may not work reliably with the current browser or device configuration.</p>
        <ul data-compatibility-issues>
            @foreach($compatibility['issues'] as $issue)<li>{{ $issue }}</li>@endforeach
        </ul>
        <p class="compatibility-detected">Detected: {{ $compatibility['browser'] }} {{ $compatibility['browser_version'] }} · {{ $compatibility['operating_system'] }}</p>
        <div><button class="btn btn-brand" type="button" data-compatibility-recheck>Check again</button><button class="btn btn-outline-secondary" type="button" data-compatibility-continue>Continue anyway</button></div>
    </section>
</div>
<script>
(() => {
    const gate = document.querySelector('[data-compatibility-gate]');
    if (!gate) return;
    const serverIssues = @json($compatibility['issues']);
    const warningDismissed = () => { try { return sessionStorage.getItem('compatibility-warning-dismissed') === 'true'; } catch (error) { return false; } };
    const setWarningDismissed = value => { try { value ? sessionStorage.setItem('compatibility-warning-dismissed', 'true') : sessionStorage.removeItem('compatibility-warning-dismissed'); } catch (error) {} };
    const check = () => {
        const issues = [...serverIssues];
        if (!navigator.cookieEnabled) issues.push('Cookies must be enabled to sign in and keep your session secure.');
        try { localStorage.setItem('__portal_check', '1'); localStorage.removeItem('__portal_check'); }
        catch (error) { issues.push('Browser storage is unavailable. Allow site storage or leave private browsing mode.'); }
        if (!window.fetch || !window.Promise || !window.FormData) issues.push('Required browser features are unavailable. Update your browser to its latest version.');
        if (!window.File || !window.FileReader || !window.Blob) issues.push('File upload support is unavailable. Update your browser before managing documents or photographs.');
        const list = gate.querySelector('[data-compatibility-issues]');
        list.replaceChildren(...issues.map(issue => { const item = document.createElement('li'); item.textContent = issue; return item; }));
        gate.hidden = issues.length === 0 || warningDismissed();
    };
    gate.querySelector('[data-compatibility-recheck]').addEventListener('click', () => { setWarningDismissed(false); check(); });
    gate.querySelector('[data-compatibility-continue]').addEventListener('click', () => { setWarningDismissed(true); gate.hidden = true; });
    check();
})();
</script>
