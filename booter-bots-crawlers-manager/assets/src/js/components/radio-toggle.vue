<template>
    <div class="radio-con" role="radiogroup">
        <input ref="element" :id="id" type="hidden" :name="name" v-bind="$attrs" :value="selected">
        <div class="toggle" v-for="(name, val) in items">
            <input :id="`${id}-${val}`"
                   type="radio"
                   :key="val"
                   :value="val"
                   :checked="selected === val"
                   @change="e => emit(e, val)"
            >
            <label :for="`${id}-${val}`" v-text="name"></label>
        </div>
    </div>
</template>

<script>
    export default {
        props: [ 'name', 'value', 'options', 'id' ],
        data() {
            return {
                selected: '',
                items: []
            }
        },
        mounted() {
            this.selected = this.value;
            this.items = JSON.parse(this.options);

            document.addEventListener('DOMContentLoaded', () => {
                let evt = new Event("change", { bubbles: true });
                this.$refs.element.dispatchEvent(evt);
            });
        },
        methods: {
            emit(e, val) {
                this.selected = val;
                this.$refs.element.value = val;
                let evt = new Event("change", { bubbles: true });
                this.$refs.element.dispatchEvent(evt);
            }
        }
    }
</script>

<style>
    .radio-con {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-start;
    }

    .radio-con .toggle {
        display: inline-block;
    }
    .radio-con .toggle input {
        position: absolute;
        overflow: hidden;
        clip: rect(0 0 0 0);
        height: 1px;
        width: 1px;
        margin: -1px;
        padding: 0;
        border: 0;
    }
    .radio-con .toggle input:focus ~ label {
        box-shadow: 0 0 0 2px #A6C8FF;
    }
    .radio-con .toggle label {
        position: relative;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        transition: background-color 200ms, padding 100ms, border 100ms;
        min-width: 3em;
        max-width: 10em;
        border: 1px solid;
        background-color: #fff;
        border-radius: 0.25em;
        margin: 0.25em 0.2em;
        padding: 0.75em 1.25em;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 3px 5px rgba(0, 0, 0, 0.05);
        transform: translateZ(0);
        will-change: background, padding, border;
    }
    .radio-con .toggle input:checked ~ label {
        border: 2px solid #007bff;
        padding: 0.688em 1.188em;
        padding-right: 2.588em; /* left padding + ::before width */
    }
    .radio-con .toggle input ~ label::before {
        content: ' ';
        display: block;
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        width: 1.6em;
        background: #007bff;
        transition: transform 100ms linear;
        transform-origin: top right;
        transform: scaleX(0);
        will-change: transform;
    }
    .radio-con .toggle input:checked ~ label::before {
        transform: scaleX(1);
    }
    .radio-con .toggle input:checked ~ label::after {
        content: '✓';
        display: block;
        position: absolute;
        top: 1.3em;
        right: 0.55em;
        font-size: 0.75em;
        color: white;
    }
    .radio-con .toggle label:hover,
    .radio-con .toggle input:checked ~ label:hover {
        background-color: #d3f2f8;
        color: black;
    }
    @media screen and (prefers-reduced-motion: reduce) {
        .radio-con .toggle label,
        .radio-con .toggle input ~ label::before {
            transition: none;
        }
    }
</style>
