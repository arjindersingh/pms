import * as bootstrap from 'bootstrap';
import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Make Bootstrap's programmatic API available to inline scripts and other
// project code (for example: new bootstrap.Modal('#exampleModal')).
window.bootstrap = bootstrap;

// Expose Alpine globally for Alpine plugins and inline project scripts.
window.Alpine = Alpine;

Alpine.data('portalShell', () => ({
    sidebarOpen: false,
    sidebarCollapsed: localStorage.getItem('portal-sidebar-collapsed') === 'true',
    theme: localStorage.getItem('portal-theme') || 'default',
    themeOptions: [
        { key: 'default', name: 'Workspace', description: 'Your module colour', accent: null, sidebar: null },
        { key: 'indigo', name: 'Indigo', description: 'Focused and professional', accent: '#6657e8', rgb: '102, 87, 232', dark: '#4938c8', sidebar: '#17162b', canvas: '#f5f6fb' },
        { key: 'ocean', name: 'Ocean', description: 'Calm blue workspace', accent: '#1877b8', rgb: '24, 119, 184', dark: '#105486', sidebar: '#102a3a', canvas: '#f3f8fb' },
        { key: 'forest', name: 'Forest', description: 'Fresh and balanced', accent: '#16836f', rgb: '22, 131, 111', dark: '#0d6253', sidebar: '#15312d', canvas: '#f2f8f6' },
        { key: 'sunset', name: 'Sunset', description: 'Warm and energetic', accent: '#df6538', rgb: '223, 101, 56', dark: '#ae4422', sidebar: '#35231f', canvas: '#faf6f2' },
    ],
    defaults: {},
    applyTheme(key) {
        const root = document.body;
        const properties = ['--portal-accent', '--portal-accent-rgb', '--portal-accent-dark', '--portal-sidebar', '--portal-canvas'];
        if (!Object.keys(this.defaults).length) properties.forEach(property => this.defaults[property] = root.style.getPropertyValue(property));
        const option = this.themeOptions.find(item => item.key === key);
        const values = option?.accent ? [option.accent, option.rgb, option.dark, option.sidebar, option.canvas] : properties.map(property => this.defaults[property]);
        properties.forEach((property, index) => root.style.setProperty(property, values[index]));
    },
    setTheme(key) {
        this.theme = key;
        localStorage.setItem('portal-theme', key);
        this.applyTheme(key);
    },
}));

Livewire.start();

const enhanceSelect = select => {
    if (select.dataset.enhanced || select.multiple) return;
    select.dataset.enhanced = 'true';

    const root = document.createElement('div');
    root.className = 'portal-select';
    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'portal-select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    const panel = document.createElement('div');
    panel.className = 'portal-select-panel';
    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'portal-select-search';
    search.placeholder = select.dataset.placeholder || 'Type to search…';
    search.setAttribute('aria-label', search.placeholder);
    const options = document.createElement('div');
    options.className = 'portal-select-options';
    options.setAttribute('role', 'listbox');
    panel.append(search, options);
    root.append(trigger, panel);
    select.before(root);
    root.append(select);
    select.classList.add('portal-native-select');

    const close = () => {
        root.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
        search.value = '';
        render();
    };
    const label = () => select.selectedOptions[0]?.text || 'Select an option';
    const render = () => {
        const query = search.value.trim().toLocaleLowerCase();
        options.replaceChildren();
        Array.from(select.options).forEach(option => {
            if (option.disabled || (query && !option.text.toLocaleLowerCase().includes(query))) return;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'portal-select-option';
            item.textContent = option.text;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', String(option.selected));
            if (option.selected) item.classList.add('selected');
            item.addEventListener('click', () => {
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                trigger.textContent = label();
                close();
                trigger.focus();
            });
            options.append(item);
        });
        if (!options.children.length) {
            const empty = document.createElement('span');
            empty.className = 'portal-select-empty';
            empty.textContent = 'No matching options';
            options.append(empty);
        }
    };

    trigger.textContent = label();
    trigger.disabled = select.disabled;
    render();
    trigger.addEventListener('click', () => {
        const opening = !root.classList.contains('open');
        document.querySelectorAll('.portal-select.open').forEach(item => item !== root && item.classList.remove('open'));
        root.classList.toggle('open', opening);
        trigger.setAttribute('aria-expanded', String(opening));
        if (opening) { render(); search.focus(); }
    });
    search.addEventListener('input', render);
    root.addEventListener('keydown', event => { if (event.key === 'Escape') { close(); trigger.focus(); } });
    select.addEventListener('change', () => { trigger.textContent = label(); render(); });
    select.addEventListener('invalid', () => root.classList.add('invalid'));
    select.form?.addEventListener('reset', () => setTimeout(() => { trigger.textContent = label(); render(); }, 0));
};

document.querySelectorAll('form select:not([multiple]):not([data-native-select])').forEach(enhanceSelect);
new MutationObserver(mutations => mutations.forEach(mutation => mutation.addedNodes.forEach(node => {
    if (!(node instanceof HTMLElement)) return;
    if (node.matches?.('form select:not([multiple]):not([data-native-select])')) enhanceSelect(node);
    node.querySelectorAll?.('form select:not([multiple]):not([data-native-select])').forEach(enhanceSelect);
}))).observe(document.body, { childList: true, subtree: true });
document.addEventListener('click', event => {
    document.querySelectorAll('.portal-select.open').forEach(root => {
        if (!root.contains(event.target)) {
            root.classList.remove('open');
            root.querySelector('.portal-select-trigger')?.setAttribute('aria-expanded', 'false');
        }
    });
});

document.querySelectorAll('[data-photo-capture]').forEach(workspace => {
    const input = workspace.querySelector('[data-photo-input]');
    const preview = workspace.querySelector('[data-photo-preview]');
    const placeholder = workspace.querySelector('[data-photo-placeholder]');
    const status = workspace.querySelector('[data-photo-status]');
    const save = workspace.querySelector('[data-photo-save]');
    const panel = workspace.querySelector('[data-camera-panel]');
    const video = workspace.querySelector('[data-camera-video]');
    const canvas = workspace.querySelector('[data-camera-canvas]');
    let stream = null;
    let previewUrl = null;
    const stopCamera = () => {
        stream?.getTracks().forEach(track => track.stop());
        stream = null; video.srcObject = null; panel.hidden = true;
    };
    const showFile = file => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl; preview.hidden = false;
        placeholder?.setAttribute('hidden', 'hidden');
        status.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB`;
        save.disabled = false;
    };
    input.addEventListener('change', () => { if (input.files[0]) showFile(input.files[0]); });
    workspace.querySelector('[data-camera-start]').addEventListener('click', async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            status.textContent = 'Camera access is not supported by this browser. Please upload a photograph.';
            return;
        }
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 1280 } }, audio: false });
            video.srcObject = stream; panel.hidden = false;
        } catch (error) {
            status.textContent = 'Camera permission was unavailable. You can still upload a photograph.';
        }
    });
    workspace.querySelector('[data-camera-cancel]').addEventListener('click', stopCamera);
    workspace.querySelector('[data-camera-capture]').addEventListener('click', () => {
        if (!video.videoWidth) return;
        const size = Math.min(video.videoWidth, video.videoHeight);
        canvas.width = 900; canvas.height = 900;
        const x = (video.videoWidth - size) / 2; const y = (video.videoHeight - size) / 2;
        canvas.getContext('2d').drawImage(video, x, y, size, size, 0, 0, 900, 900);
        canvas.toBlob(blob => {
            if (!blob) return;
            const file = new File([blob], `camera-${Date.now()}.jpg`, { type: 'image/jpeg' });
            const transfer = new DataTransfer(); transfer.items.add(file); input.files = transfer.files;
            showFile(file); stopCamera();
        }, 'image/jpeg', .9);
    });
    window.addEventListener('pagehide', stopCamera);
});

document.querySelectorAll('[data-education-subjects]').forEach(section => {
    const select = section.querySelector('[data-subject-select]');
    const chips = section.querySelector('[data-subject-chips]');
    const status = section.querySelector('[data-subject-status]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const setBusy = busy => { select.disabled = busy; section.classList.toggle('loading', busy); };

    select.addEventListener('change', async () => {
        if (!select.value) return;
        const subjectId = select.value;
        const option = select.selectedOptions[0];
        setBusy(true); status.textContent = 'Adding subject…';
        try {
            const response = await fetch(section.dataset.addUrl, { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ subject_id: subjectId }) });
            if (!response.ok) throw new Error('Unable to add subject');
            const { subject } = await response.json();
            if (!chips.querySelector(`[data-subject-id="${subject.id}"]`)) {
                const chip = document.createElement('span'); chip.className = 'subject-chip'; chip.dataset.subjectId = subject.id;
                chip.append(document.createTextNode(subject.name));
                const remove = document.createElement('button'); remove.type = 'button'; remove.dataset.removeSubject = ''; remove.textContent = '×'; remove.setAttribute('aria-label', `Remove ${subject.name}`);
                chip.append(remove); chips.append(chip);
            }
            option.disabled = true; status.textContent = 'Subject added.';
        } catch (error) { status.textContent = 'Could not add the subject. Please try again.'; }
        finally { select.value = ''; setBusy(false); select.dispatchEvent(new Event('change', { bubbles: true })); }
    });

    chips.addEventListener('click', async event => {
        const button = event.target.closest('[data-remove-subject]'); if (!button) return;
        const chip = button.closest('[data-subject-id]'); const subjectId = chip.dataset.subjectId;
        button.disabled = true; status.textContent = 'Removing subject…';
        try {
            const url = section.dataset.deleteUrl.replace('__SUBJECT__', subjectId);
            const response = await fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
            if (!response.ok) throw new Error('Unable to remove subject');
            select.querySelector(`option[value="${subjectId}"]`)?.removeAttribute('disabled');
            chip.remove(); select.dispatchEvent(new Event('change', { bubbles: true })); status.textContent = 'Subject removed.';
        } catch (error) { button.disabled = false; status.textContent = 'Could not remove the subject. Please try again.'; }
    });
});

document.querySelectorAll('[data-new-education-subjects]').forEach(section => {
    const select = section.querySelector('[data-new-subject-select]');
    const chips = section.querySelector('[data-new-subject-chips]');
    select.addEventListener('change', () => {
        if (!select.value || chips.querySelector(`[data-subject-id="${select.value}"]`)) return;
        const option = select.selectedOptions[0];
        const chip = document.createElement('span'); chip.className = 'subject-chip'; chip.dataset.subjectId = select.value;
        chip.append(document.createTextNode(option.text));
        const input = document.createElement('input'); input.type = 'hidden'; input.name = 'subjects[]'; input.value = select.value; chip.append(input);
        const remove = document.createElement('button'); remove.type = 'button'; remove.dataset.removeNewSubject = ''; remove.textContent = '×'; remove.setAttribute('aria-label', `Remove ${option.text}`); chip.append(remove);
        chips.append(chip); option.disabled = true; select.value = ''; select.dispatchEvent(new Event('change', { bubbles: true }));
    });
    chips.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-new-subject]'); if (!button) return;
        const chip = button.closest('[data-subject-id]'); select.querySelector(`option[value="${chip.dataset.subjectId}"]`)?.removeAttribute('disabled');
        chip.remove(); select.dispatchEvent(new Event('change', { bubbles: true }));
    });
});

document.querySelectorAll('[data-degree-select]').forEach(degree => {
    const level = degree.form?.querySelector('[name="qualification_level_id"]');
    if (!level) return;
    const updateDegrees = () => {
        const selectedLevel = level.value;
        Array.from(degree.options).forEach((option, index) => { if (index) option.disabled = !selectedLevel || option.dataset.qualificationLevel !== selectedLevel; });
        if (degree.selectedOptions[0]?.disabled) degree.value = '';
        degree.disabled = !selectedLevel;
        const trigger = degree.closest('.portal-select')?.querySelector('.portal-select-trigger'); if (trigger) trigger.disabled = degree.disabled;
        degree.options[0].text = selectedLevel ? 'Select degree / course' : 'Select qualification level first';
        degree.dispatchEvent(new Event('change', { bubbles: true }));
    };
    level.addEventListener('change', updateDegrees); updateDegrees();
});

document.querySelectorAll('[data-saved-subject-chips]').forEach(chips => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    chips.addEventListener('click', async event => {
        const button = event.target.closest('[data-remove-saved-subject]'); if (!button) return;
        const chip = button.closest('[data-subject-id]'); button.disabled = true;
        try {
            const response = await fetch(chips.dataset.deleteUrl.replace('__SUBJECT__', chip.dataset.subjectId), { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
            if (!response.ok) throw new Error('Unable to remove subject');
            chip.remove();
            if (!chips.querySelector('.subject-chip')) { const empty = document.createElement('span'); empty.className = 'text-secondary'; empty.textContent = 'No subjects selected'; chips.append(empty); }
        } catch (error) { button.disabled = false; }
    });
});
