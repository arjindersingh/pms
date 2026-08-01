import * as bootstrap from 'bootstrap';
import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Make Bootstrap's programmatic API available to inline scripts and other
// project code (for example: new bootstrap.Modal('#exampleModal')).
window.bootstrap = bootstrap;

// Expose Alpine globally for Alpine plugins and inline project scripts.
window.Alpine = Alpine;

Livewire.start();
