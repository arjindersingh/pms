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

document.querySelectorAll('.searchable-select').forEach(select => {
    if (select.dataset.enhanced) return;
    select.dataset.enhanced = 'true';
    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'form-control form-control-sm searchable-select-input';
    search.placeholder = select.dataset.placeholder || 'Search options';
    search.setAttribute('aria-label', search.placeholder);
    select.before(search);
    search.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        Array.from(select.options).forEach((option, index) => option.hidden = index > 0 && query !== '' && !option.text.toLowerCase().includes(query));
        if (query && select.options[select.selectedIndex]?.hidden) select.value = '';
    });
});
