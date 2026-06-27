import Vue from 'vue';
import RobotsManager from './components/robots-manager';
import TagsList from './components/tags-list';
import TagsListSingle from './components/tags-list-single';
import StringsList from './components/strings-list';
import Modal from './components/modal';
import Switch from './components/switch';
import BotSelector from './components/bots-selector';
import RadioToggle from './components/radio-toggle';
import TogglePanel from './components/toggle-panel';
import UniqueId from 'vue-unique-id';
import Fragment from 'vue-fragment';

Vue.use(UniqueId);
Vue.use(Fragment.Plugin);

Vue.component('robots-manager', RobotsManager);
Vue.component('booter-modal', Modal);
Vue.component('booter-switch', Switch);
Vue.component('strings-list', StringsList);
Vue.component('tags-list', TagsList);
Vue.component('tags-list-single', TagsListSingle);
Vue.component('bots-selector', BotSelector);
Vue.component('radio-toggle', RadioToggle);
Vue.component('toggle-panel', TogglePanel);

Vue.mixin({
    data() {
        return {
            get booter() {
                return window.wp_booter;
            }
        }
    }
});

new Vue({
    el: '#booter-options'
});


document.addEventListener('click', e => {
    if(!e.target.classList.contains('js-booter-tab')) {
        return;
    }

    e.preventDefault();
    showTab.call(e.target, e);
});
function showTab(e) {
    e.preventDefault();

    var target = this.getAttribute('aria-controls');
    if(!target) return;

    window.location.hash = this.getAttribute('href').substr(1);

    var panels = document.querySelectorAll('.js-booter-tabpanel');
    panels.forEach(panel => {
        if(panel.id === target) {
            panel.removeAttribute('hidden');
        } else {
            panel.setAttribute('hidden', '');
        }
    });

    const activeTab = document.querySelector(`.booter-options .nav-tab-wrapper [href="${this.getAttribute('href')}"]`);
    activeTab.classList.add('nav-tab-active');
    activeTab.setAttribute('aria-selected', 'true');

    document.querySelectorAll('.booter-options .nav-tab-wrapper .js-booter-tab').forEach(tab => {
        if(tab === activeTab) {
            return;
        }

        tab.classList.remove('nav-tab-active');
        tab.removeAttribute('aria-selected');
    });
}
if(window.location.hash) {
    const target = document.querySelector(`.js-booter-tab[href="${window.location.hash}"]`);
    if(target) {
        target.dispatchEvent(new Event("click", { bubbles: true }));
    }
}

// listen for change events from elements with data-toggle attribute
document.addEventListener('change', function(e) {
    if(e.target.dataset.toggleOn === undefined && e.target.dataset.toggleOff === undefined) {
        return;
    }

    let toggle = e.target;
    let on_value = e.target.dataset.onValue || '1';
    let value = toggle.tagName.toLowerCase() === 'input' && toggle.type === 'checkbox' ? (toggle.checked ? '1' : '0') : toggle.value;

    if(value === on_value) {
        document.querySelectorAll(toggle.dataset.toggleOn).forEach(function (el) {
            el.removeAttribute('hidden');
        });
        document.querySelectorAll(toggle.dataset.toggleOff).forEach(function (el) {
            el.setAttribute('hidden', '');
        });
    } else {
        document.querySelectorAll(toggle.dataset.toggleOn).forEach(function (el) {
            el.setAttribute('hidden', '');
        });
        document.querySelectorAll(toggle.dataset.toggleOff).forEach(function (el) {
            el.removeAttribute('hidden');
        });
    }
});

// make sure to fire changed event for any select elements with the data-toggle attribute
document.querySelectorAll('.booter-options [data-toggle]').forEach(function(toggle) {
    let evt = new Event("change", { bubbles: true });
    toggle.dispatchEvent(evt);
});
