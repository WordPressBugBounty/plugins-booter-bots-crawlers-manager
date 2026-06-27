<template>
    <div class="components-form-token-field__input-container" tabindex="-1" @click="$refs.input.focus()">
        <input type="hidden" :name="name" :value="JSON.stringify(value)">
        <span v-for="(string, index) in value" class="components-form-token-field__token">
            <span class="components-form-token-field__token-text" :id="$id(`components-form-token-field__token-text-${index}`)" :title="string">
                <span v-text="string"></span>
            </span>
            <button type="button" :aria-label="booter.trans.remove" :describedby="$id(`components-form-token-field__token-text-${index}`)" class="components-button components-icon-button components-form-token-field__remove-token" @click="remove(index)">
                <svg aria-hidden="true" role="img" focusable="false" class="dashicon dashicons-dismiss" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                    <path d="M10 2c4.42 0 8 3.58 8 8s-3.58 8-8 8-8-3.58-8-8 3.58-8 8-8zm5 11l-3-3 3-3-2-2-3 3-3-3-2 2 3 3-3 3 2 2 3-3 3 3z"></path>
                </svg>
            </button>
        </span>
        <input ref="input"
               :id="id || $id('components-form-token-input')"
               type="text"
               :size="Math.max(1, newString.length)"
               class="components-form-token-field__input"
               role="combobox"
               aria-expanded="false"
               aria-autocomplete="list"
               :aria-label="addLabelText"
               v-model="newString"
               @keypress.enter.prevent="add"
               @keydown.delete="removeLast"
               @paste.prevent="parsePastedInput"
        >
    </div>
</template>

<script>
    export default {
        props: ['before-input-text', 'add-label-text', 'value', 'name', 'id'],
        data() {
            return {
                hasError: false,
                newString: ''
            };
        },
        methods: {
            add() {
                this.hasError = false;

                if(this.newString === '' || this.value.some(s => s.trim().toLowerCase() === this.newString.trim().toLowerCase())) {
                    this.newString = '';
                    this.hasError = true;
                    return;
                }

                const strings = this.value.slice(0);
                strings.push(this.newString.trim());
                this.newString = '';
                this.$emit('input', strings);
            },
            remove(index) {
                const strings = this.value.slice(0);
                strings.splice(index, 1);
                this.$emit('input', strings);
            },
            removeLast(e) {
                if(this.newString.length > 0) {
                    return;
                }

                e.preventDefault();
                this.remove(this.value.length-1);
            },
            parsePastedInput(e) {
                let paste = (e.clipboardData || window.clipboardData).getData('text');

                if(/\r\n|\r|\n/.test(paste.trim())) {
                    paste = paste.trim().split(/\r\n|\r|\n/);
                } else {
                    paste = paste.trim().split(/\s+/);
                }

                let strings = this.value.slice(0);
                paste = paste.filter(b => !strings.includes(b));

                strings = strings.concat(paste);
                this.$emit('input', strings);
            }
        }
    }
</script>

<style>
    .components-form-token-field__input-container {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        width: 100%;
        margin: 0;
        padding: 4px;
        background-color: #fff;
        color: #32373c;
        cursor: text;
        box-shadow: 0 0 0 transparent;
        transition: box-shadow .1s linear;
        border-radius: 4px;
        border: 1px solid #8d96a0
    }

    .components-form-token-field__input-container.is-disabled {
        background: #e2e4e7;
        border-color: #ccd0d4
    }

    .components-form-token-field__input-container:focus-within,
    .components-form-token-field__input-container.is-active {
        color: #191e23;
        border-color: #00a0d2;
        box-shadow: 0 0 0 1px #00a0d2;
        outline: 2px solid transparent;
        outline-offset: -2px
    }

    .components-form-token-field__input-container input[type=text].components-form-token-field__input {
        display: inline-block;
        width: 100%;
        max-width: 100%;
        margin: 2px 0 2px 8px;
        padding: 0;
        min-height: 24px;
        background: inherit;
        border: 0;
        color: #23282d;
        box-shadow: none
    }

    .components-form-token-field.is-active .components-form-token-field__input-container input[type=text].components-form-token-field__input,.components-form-token-field__input-container input[type=text].components-form-token-field__input:focus {
        outline: none;
        box-shadow: none
    }

    .components-form-token-field__input-container .components-form-token-field__token+input[type=text].components-form-token-field__input {
        width: auto
    }

    .components-form-token-field__label {
        display: inline-block;
        margin-bottom: 4px
    }

    .components-form-token-field__token {
        font-size: 13px;
        display: flex;
        margin: 2px 4px 2px 0;
        color: #32373c;
        overflow: hidden
    }

    .components-form-token-field__token.is-success .components-form-token-field__remove-token,.components-form-token-field__token.is-success .components-form-token-field__token-text {
        background: #4ab866
    }

    .components-form-token-field__token.is-error .components-form-token-field__remove-token,.components-form-token-field__token.is-error .components-form-token-field__token-text {
        background: #d94f4f
    }

    .components-form-token-field__token.is-validating .components-form-token-field__remove-token,.components-form-token-field__token.is-validating .components-form-token-field__token-text {
        color: #555d66
    }

    .components-form-token-field__token.is-borderless {
        position: relative;
        padding: 0 16px 0 0
    }

    .components-form-token-field__token.is-borderless .components-form-token-field__token-text {
        background: transparent;
        color: #11a0d2
    }

    body.admin-color-sunrise .components-form-token-field__token.is-borderless .components-form-token-field__token-text {
        color: #c8b03c
    }

    body.admin-color-ocean .components-form-token-field__token.is-borderless .components-form-token-field__token-text {
        color: #a89d8a
    }

    body.admin-color-midnight .components-form-token-field__token.is-borderless .components-form-token-field__token-text {
        color: #77a6b9
    }

    body.admin-color-ectoplasm .components-form-token-field__token.is-borderless .components-form-token-field__token-text {
        color: #c77430
    }

    body.admin-color-coffee .components-form-token-field__token.is-borderless .components-form-token-field__token-text {
        color: #9fa47b
    }

    body.admin-color-blue .components-form-token-field__token.is-borderless .components-form-token-field__token-text {
        color: #d9ab59
    }

    body.admin-color-light .components-form-token-field__token.is-borderless .components-form-token-field__token-text {
        color: #c75726
    }

    .components-form-token-field__token.is-borderless .components-form-token-field__remove-token {
        background: transparent;
        color: #555d66;
        position: absolute;
        top: 1px;
        right: 0
    }

    .components-form-token-field__token.is-borderless.is-success .components-form-token-field__token-text {
        color: #4ab866
    }

    .components-form-token-field__token.is-borderless.is-error .components-form-token-field__token-text {
        color: #d94f4f;
        border-radius: 4px 0 0 4px;
        padding: 0 4px 0 6px
    }

    .components-form-token-field__token.is-borderless.is-validating .components-form-token-field__token-text {
        color: #23282d
    }

    .components-form-token-field__token.is-disabled .components-form-token-field__remove-token {
        cursor: default
    }

    .components-form-token-field__remove-token.components-icon-button,.components-form-token-field__token-text {
        display: inline-block;
        line-height: 24px;
        background: #e2e4e7;
        transition: all .2s cubic-bezier(.4,1,.4,1)
    }

    .components-form-token-field__token-text {
        border-radius: 12px 0 0 12px;
        padding: 0 4px 0 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 8em;
    }
    .components-form-token-field__token-text span {
        display: inline-block;
    }

    .components-form-token-field__remove-token.components-icon-button {
        cursor: pointer;
        border-radius: 0 12px 12px 0;
        padding: 0 2px;
        color: #555d66;
        line-height: 10px;
        overflow: initial
    }

    .components-form-token-field__remove-token.components-icon-button:hover {
        color: #32373c
    }

    .components-form-token-field__suggestions-list {
        flex: 1 0 100%;
        min-width: 100%;
        max-height: 9em;
        overflow-y: scroll;
        transition: all .15s ease-in-out;
        list-style: none;
        border-top: 1px solid #6c7781;
        margin: 4px -4px -4px;
        padding-top: 3px
    }

    .components-form-token-field__suggestion {
        color: #555d66;
        display: block;
        font-size: 13px;
        padding: 4px 8px;
        cursor: pointer
    }

    .components-form-token-field__suggestion.is-selected {
        background: #0071a1;
        color: #fff
    }

    .components-form-token-field__suggestion-match {
        text-decoration: underline
    }
    .components-icon-button {
        display: flex;
        align-items: center;
        padding: 8px;
        margin: 0;
        border: none;
        background: none;
        color: #555d66;
        position: relative;
        overflow: hidden;
        border-radius: 4px
    }

    .components-icon-button .dashicon {
        display: inline-block;
        flex: 0 0 auto
    }

    .components-icon-button svg {
        fill: currentColor;
        outline: none
    }

    .components-icon-button.has-text svg {
        margin-left: 4px
    }

    .components-icon-button:not(:disabled):not([aria-disabled=true]):not(.is-default):hover {
        background-color: #fff;
        color: #191e23;
        box-shadow: inset 0 0 0 1px #e2e4e7,inset 0 0 0 2px #fff,0 1px 1px rgba(25,30,35,.2)
    }

    .components-icon-button:not(:disabled):not([aria-disabled=true]):not(.is-default):active {
        outline: none;
        background-color: #fff;
        color: #191e23;
        box-shadow: inset 0 0 0 1px #ccd0d4,inset 0 0 0 2px #fff
    }

    .components-icon-button:disabled:focus,.components-icon-button[aria-disabled=true]:focus {
        box-shadow: none
    }

    .components-menu-group {
        width: 100%;
        padding: 7px 0
    }

    .components-menu-group__label {
        margin-bottom: 8px;
        color: #6c7781;
        padding: 0 7px
    }

    .components-menu-item__button,.components-menu-item__button.components-icon-button {
        width: 100%;
        padding: 8px 15px;
        text-align: right;
        color: #40464d
    }

    .components-menu-item__button.components-icon-button .components-menu-items__item-icon,.components-menu-item__button.components-icon-button .dashicon,.components-menu-item__button.components-icon-button>span>svg,.components-menu-item__button .components-menu-items__item-icon,.components-menu-item__button .dashicon,.components-menu-item__button>span>svg {
        margin-left: 4px
    }

    .components-menu-item__button.components-icon-button .components-menu-items__item-icon,.components-menu-item__button .components-menu-items__item-icon {
        display: inline-block;
        flex: 0 0 auto
    }

    .components-menu-item__button.components-icon-button:hover:not(:disabled):not([aria-disabled=true]),.components-menu-item__button:hover:not(:disabled):not([aria-disabled=true]) {
        color: #555d66
    }

    @media (min-width: 782px) {
        .components-menu-item__button.components-icon-button:hover:not(:disabled):not([aria-disabled=true]),.components-menu-item__button:hover:not(:disabled):not([aria-disabled=true]) {
            color:#191e23;
            border: none;
            box-shadow: none;
            background: #f3f4f5
        }
    }

    .components-menu-item__button.components-icon-button:hover:not(:disabled):not([aria-disabled=true]) .components-menu-item__shortcut,.components-menu-item__button:hover:not(:disabled):not([aria-disabled=true]) .components-menu-item__shortcut {
        opacity: 1
    }

    .components-menu-item__button.components-icon-button:focus:not(:disabled):not([aria-disabled=true]),.components-menu-item__button:focus:not(:disabled):not([aria-disabled=true]) {
        color: #191e23;
        border: none;
        box-shadow: none;
        outline-offset: -2px;
        outline: 1px dotted #555d66
    }

    body.rtl .components-form-token-field__token {
        margin: 4px 0 4px 8px;
    }
    body.rtl .components-form-token-field__token.is-borderless {
        padding: 0 0 0 16px
    }

    body.rtl .components-form-token-field__token.is-borderless .components-form-token-field__remove-token {
        left: 0;
        right: unset;
    }
    body.rtl .components-form-token-field__token.is-borderless.is-error .components-form-token-field__token-text {
        border-radius: 0 4px 4px 0;
        padding: 0 6px 0 4px
    }
    body.rtl .components-form-token-field__token-text {
        border-radius: 0 12px 12px 0;
        padding: 0 8px 0 4px;
    }

    body.rtl .components-form-token-field__remove-token.components-icon-button {
        border-radius: 12px 0 0 12px;
    }
</style>
