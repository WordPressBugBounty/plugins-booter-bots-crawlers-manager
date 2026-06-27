<template>
    <div>
        <input type="hidden" :name="name" :value="JSON.stringify(strings)">
        <table class="widefat strings-list">
            <thead>
            <tr>
                <td style="width: 1px;">
                    <button type="button" class="button" @click.prevent="add" :title="booter.trans.add">
                        <span class="screen-reader-text" v-text="booter.trans.add"></span>
                        <i class="dashicons dashicons-plus-alt2"></i>
                    </button>
                </td>
                <td>
                    <span class="screen-reader-text" v-text="addLabelText"></span>
                    <div class="flex align-items-center nowrap" :style="booter.is_rtl ? 'direction: ltr; text-align: right;' : ''">
                        <span v-if="beforeInputText" v-text="beforeInputText"></span>
                        <input :id="id || $id('strings-list')" type="text" style="margin: 0 8px;" class="text large-text" :class="{ error: hasError }" :placeholder="addLabelText" v-model="newString" @keypress.enter.prevent="add">
                    </div>
                </td>
            </tr>
            </thead>
            <tbody>
                <tr v-for="(string, index) in strings">
                    <td style="width: 1px;">
                        <button type="button" class="button" @click.prevent="remove(index)" :title="booter.trans.remove">
                            <span class="screen-reader-text" v-text="booter.trans.remove">Remove</span>
                            <i class="dashicons dashicons-trash"></i>
                        </button>
                    </td>
                    <td>
                        <div class="flex align-items-center nowrap" :style="booter.is_rtl ? 'direction: ltr; text-align: right;' : ''">
                            <span v-if="beforeInputText" v-text="beforeInputText"></span>
                            <input type="text" style="margin: 0 8px;" class="text large-text" :value="string" disabled>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        props: ['before-input-text', 'add-label-text', 'value', 'name', 'id', 'force-start-with'],
        data() {
            return {
                hasError: false,
                newString: '',
                strings: []
            };
        },
        mounted() {
            if(this.value && typeof this.value === 'string') {
                try {
                    this.strings = JSON.parse(this.value);
                } catch(err) {
                    console.error(`Failed parsing currnet value: ${err}\n'${this.value}'`);
                }
            } else if(this.value) {
                this.strings = this.value;
            }
        },
        methods: {
            add() {
                this.hasError = false;

                if(this.newString === '' || this.strings.some(s => s.trim().toLowerCase() === this.newString.trim().toLowerCase())) {
                    this.hasError = true;
                    return;
                }

                if(this.forceStartWith !== undefined) {
                    if(this.newString.indexOf(this.forceStartWith) !== 0) {
                        this.newString = this.forceStartWith + this.newString;
                    }
                }

                this.strings.push(this.newString.trim());
                this.newString = '';
                this.$emit('input', this.strings);
            },
            remove(index) {
                this.strings.splice(index, 1);
                this.$emit('input', this.strings);
            }
        }
    }
</script>
