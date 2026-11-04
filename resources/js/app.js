import './bootstrap';
import Alpine from 'alpinejs';
import spa from './spa';

window.Alpine = Alpine;
window.TERMINAL_SPA = window.WARKOP_SPA = window.WARLAB_SPA = spa;

Alpine.start();
spa.init();
