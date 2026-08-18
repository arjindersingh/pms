import * as bootstrap from 'bootstrap';
import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Make Bootstrap's programmatic API available to inline scripts and other
// project code (for example: new bootstrap.Modal('#exampleModal')).
window.bootstrap = bootstrap;

// Expose Alpine globally for Alpine plugins and inline project scripts.
window.Alpine = Alpine;

// Clear origin-owned browser data before logout. The server also returns a
// Clear-Site-Data header, which covers HttpOnly cookies and provides the
// authoritative cleanup in browsers that support it.
document.querySelectorAll('form[data-secure-logout]').forEach(form => {
    form.addEventListener('submit', async event => {
        if (form.dataset.cleanupComplete === 'true') return;

        event.preventDefault();
        form.querySelector('button[type="submit"]')?.setAttribute('disabled', 'disabled');

        try { localStorage.clear(); } catch (error) {}
        try { sessionStorage.clear(); } catch (error) {}

        const cleanup = [];
        if ('caches' in window) {
            cleanup.push(caches.keys().then(keys => Promise.all(keys.map(key => caches.delete(key)))));
        }
        if ('serviceWorker' in navigator) {
            cleanup.push(navigator.serviceWorker.getRegistrations().then(registrations => Promise.all(registrations.map(registration => registration.unregister()))));
        }
        if (window.indexedDB?.databases) {
            cleanup.push(indexedDB.databases().then(databases => Promise.all(
                databases.filter(database => database.name).map(database => new Promise(resolve => {
                    const request = indexedDB.deleteDatabase(database.name);
                    request.onsuccess = request.onerror = request.onblocked = () => resolve();
                }))
            )));
        }

        // Cleanup must never trap the user in an authenticated session if a
        // browser API stalls (for example, an IndexedDB connection is open).
        await Promise.race([
            Promise.allSettled(cleanup),
            new Promise(resolve => setTimeout(resolve, 1500)),
        ]);
        form.dataset.cleanupComplete = 'true';
        form.requestSubmit();
    });
});

Alpine.data('portalShell', () => ({
    sidebarOpen: false,
    sidebarCollapsed: (() => {
        try { return localStorage.getItem('portal-sidebar-collapsed') === 'true'; } catch (error) { return false; }
    })(),
    theme: (() => {
        try { return localStorage.getItem('portal-theme') || 'default'; } catch (error) { return 'default'; }
    })(),
    lightSidebar: false,
    themeOptions: [
        { key: 'default', name: 'Workspace', description: 'Your module colour', accent: null, sidebar: null },
        { key: 'indigo', name: 'Indigo', description: 'Focused and professional', accent: '#6657e8', rgb: '102, 87, 232', dark: '#4938c8', sidebar: '#17162b', canvas: '#f5f6fb' },
        { key: 'ocean', name: 'Ocean', description: 'Calm blue workspace', accent: '#1877b8', rgb: '24, 119, 184', dark: '#105486', sidebar: '#102a3a', canvas: '#f3f8fb' },
        { key: 'forest', name: 'Forest', description: 'Fresh and balanced', accent: '#16836f', rgb: '22, 131, 111', dark: '#0d6253', sidebar: '#15312d', canvas: '#f2f8f6' },
        { key: 'sunset', name: 'Sunset', description: 'Warm and energetic', accent: '#df6538', rgb: '223, 101, 56', dark: '#ae4422', sidebar: '#35231f', canvas: '#faf6f2' },
        { key: 'porcelain', name: 'Porcelain Light', description: 'Crisp neutral contrast', accent: '#3157c8', rgb: '49, 87, 200', dark: '#23429d', sidebar: '#ffffff', canvas: '#f1f4f9', light: true },
        { key: 'skyLight', name: 'Sky Light', description: 'Bright blue and airy', accent: '#0877b9', rgb: '8, 119, 185', dark: '#075985', sidebar: '#f7fbff', canvas: '#eef7fc', light: true },
        { key: 'mintLight', name: 'Mint Light', description: 'Clean green contrast', accent: '#087f6b', rgb: '8, 127, 107', dark: '#056052', sidebar: '#f7fcfa', canvas: '#eef8f4', light: true },
    ],
    defaults: {},
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        try { localStorage.setItem('portal-sidebar-collapsed', String(this.sidebarCollapsed)); } catch (error) {}
    },
    applyTheme(key) {
        const root = document.body;
        const properties = ['--portal-accent', '--portal-accent-rgb', '--portal-accent-dark', '--portal-sidebar', '--portal-canvas'];
        if (!Object.keys(this.defaults).length) properties.forEach(property => this.defaults[property] = root.style.getPropertyValue(property));
        const option = this.themeOptions.find(item => item.key === key);
        this.lightSidebar = Boolean(option?.light);
        const values = option?.accent ? [option.accent, option.rgb, option.dark, option.sidebar, option.canvas] : properties.map(property => this.defaults[property]);
        properties.forEach((property, index) => root.style.setProperty(property, values[index]));
    },
    setTheme(key) {
        this.theme = key;
        try { localStorage.setItem('portal-theme', key); } catch (error) {}
        this.applyTheme(key);
    },
}));

Livewire.start();

const enhanceSelect = select => {
    if (select.dataset.enhanced) return;
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
    if (select.multiple) options.setAttribute('aria-multiselectable', 'true');
    panel.append(search, options);
    if (select.multiple) {
        const actions = document.createElement('div');
        actions.className = 'portal-select-actions';
        const clear = document.createElement('button');
        clear.type = 'button';
        clear.className = 'portal-select-clear';
        clear.textContent = 'Clear all';
        const done = document.createElement('button');
        done.type = 'button';
        done.className = 'portal-select-done';
        done.textContent = 'Done';
        clear.addEventListener('click', () => {
            Array.from(select.options).forEach(option => { option.selected = false; });
            select.dispatchEvent(new Event('change', { bubbles: true }));
            trigger.textContent = label();
            render();
        });
        done.addEventListener('click', () => { close(); trigger.focus(); });
        actions.append(clear, done);
        panel.append(actions);
    }
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
    const optionLabel = option => option.parentElement instanceof HTMLOptGroupElement
        ? `${option.parentElement.label} · ${option.text}`
        : option.text;
    const label = () => {
        if (!select.multiple) return select.selectedOptions[0]?.text || select.dataset.placeholder || 'Select an option';
        const selected = Array.from(select.selectedOptions);
        if (!selected.length) return select.dataset.placeholder || 'Select one or more';
        if (selected.length === 1) return optionLabel(selected[0]);
        return `${selected.length} selected`;
    };
    const render = () => {
        const query = search.value.trim().toLocaleLowerCase();
        options.replaceChildren();
        Array.from(select.options).forEach(option => {
            const text = optionLabel(option);
            if (option.disabled || (query && !text.toLocaleLowerCase().includes(query))) return;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'portal-select-option';
            const marker = document.createElement('span');
            marker.className = 'portal-select-marker';
            marker.textContent = option.selected ? '✓' : '';
            const copy = document.createElement('span');
            copy.textContent = text;
            item.append(marker, copy);
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', String(option.selected));
            if (option.selected) item.classList.add('selected');
            item.addEventListener('click', () => {
                if (select.multiple) option.selected = !option.selected;
                else select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                trigger.textContent = label();
                if (select.multiple) render();
                else { close(); trigger.focus(); }
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

document.querySelectorAll('form select:not([data-native-select])').forEach(enhanceSelect);
new MutationObserver(mutations => mutations.forEach(mutation => mutation.addedNodes.forEach(node => {
    if (!(node instanceof HTMLElement)) return;
    if (node.matches?.('form select:not([data-native-select])')) enhanceSelect(node);
    node.querySelectorAll?.('form select:not([data-native-select])').forEach(enhanceSelect);
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

const formControlLabel = control => {
    const labelledBy = control.getAttribute('aria-labelledby');
    const explicitLabel = control.id
        ? document.querySelector(`label[for="${CSS.escape(control.id)}"]`)
        : null;
    const label = labelledBy
        ? document.getElementById(labelledBy)
        : explicitLabel || control.closest('label');
    const fallback = control.getAttribute('aria-label')
        || control.placeholder
        || control.name?.replaceAll('[]', '').replaceAll('_', ' ')
        || control.value;

    return (label?.textContent || fallback || 'this field')
        .replace(/\s+/g, ' ')
        .replace(/\s*\*\s*$/, '')
        .trim();
};

const formControlTooltip = control => {
    const label = formControlLabel(control);

    if (control.matches('button, input[type="submit"], input[type="button"], input[type="reset"]')) {
        return label;
    }
    if (control.matches('input[type="checkbox"], input[type="radio"]')) {
        return `Toggle ${label}`;
    }
    if (control.matches('select')) {
        return `${control.multiple ? 'Select one or more' : 'Select'} ${label}`;
    }
    if (control.matches('input[type="file"]')) {
        return `Upload ${label}`;
    }
    if (control.matches('input[type="date"], input[type="datetime-local"], input[type="month"], input[type="time"]')) {
        return `Choose ${label}`;
    }
    if (control.matches('input[type="search"]')) {
        return `Search ${label}`;
    }

    return `Enter ${label}`;
};

const addFormTooltip = control => {
    if (control.dataset.tooltip === 'off' || control.matches('input[type="hidden"]')) return;

    const tooltipTarget = control.matches('select')
        ? control.closest('.portal-select')?.querySelector('.portal-select-trigger') || control
        : control;
    const tooltip = tooltipTarget.getAttribute('title') || control.getAttribute('title') || formControlTooltip(control);

    tooltipTarget.setAttribute('title', tooltip);
    tooltipTarget.setAttribute('data-bs-toggle', 'tooltip');
    tooltipTarget.setAttribute('data-bs-placement', 'top');
    bootstrap.Tooltip.getOrCreateInstance(tooltipTarget, { trigger: 'hover focus' });
};

const addFormTooltips = root => {
    const controls = root.matches?.('form input, form select, form textarea, form button')
        ? [root]
        : root.querySelectorAll?.('form input, form select, form textarea, form button') || [];

    controls.forEach(addFormTooltip);
};

addFormTooltips(document);
new MutationObserver(mutations => mutations.forEach(mutation => mutation.addedNodes.forEach(node => {
    if (node instanceof HTMLElement) addFormTooltips(node);
}))).observe(document.body, { childList: true, subtree: true });

const replaceLocationOptions = (select, placeholder, records, selected) => {
    select.replaceChildren();
    const empty = new Option(placeholder, '');
    select.add(empty);
    records.forEach(record => {
        const option = new Option(record.display_name, record.display_name, false, record.display_name === selected);
        option.dataset.id = record.id;
        select.add(option);
    });
    select.disabled = records.length === 0;
    select.closest('.portal-select')?.querySelector('.portal-select-trigger')?.toggleAttribute('disabled', select.disabled);
    select.dispatchEvent(new Event('change', { bubbles: true }));
};

document.querySelectorAll('[data-location-fields]').forEach(fields => {
    const country = fields.querySelector('[data-location-country]');
    const state = fields.querySelector('[data-location-state]');
    const district = fields.querySelector('[data-location-district]');
    let populatingState = false;

    const loadDistricts = async selected => {
        const stateId = state.selectedOptions[0]?.dataset.id;
        if (!stateId) {
            replaceLocationOptions(district, 'Select district', [], '');
            return;
        }
        const response = await fetch(fields.dataset.districtsUrl.replace('__STATE__', encodeURIComponent(stateId)), { headers: { Accept: 'application/json' } });
        replaceLocationOptions(district, 'Select district', response.ok ? await response.json() : [], selected);
    };
    const loadStates = async (selectedState, selectedDistrict) => {
        const countryCode = country.selectedOptions[0]?.dataset.code;
        if (!countryCode) {
            replaceLocationOptions(state, 'Select state / province', [], '');
            replaceLocationOptions(district, 'Select district', [], '');
            return;
        }
        const response = await fetch(fields.dataset.statesUrl.replace('__COUNTRY__', encodeURIComponent(countryCode)), { headers: { Accept: 'application/json' } });
        populatingState = true;
        replaceLocationOptions(state, 'Select state / province', response.ok ? await response.json() : [], selectedState);
        populatingState = false;
        await loadDistricts(selectedDistrict);
    };

    country.addEventListener('change', () => loadStates('', ''));
    state.addEventListener('change', () => { if (!populatingState) loadDistricts(''); });
    loadStates(state.dataset.selected, district.dataset.selected);
});
