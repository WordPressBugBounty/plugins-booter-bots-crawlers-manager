<template>
    <tags-list v-model="val" v-bind="$attrs"></tags-list>
</template>

<script>
    export default {
        props: ['value'],
        data() {
            return {
                val: []
            };
        },
        mounted() {
            if(this.value && typeof this.value === 'string') {
                try {
                    this.val = JSON.parse(this.value);
                } catch(err) {
                    console.error(`Failed parsing currnet value: ${err}\n'${this.value}'`);
                }
            } else if(this.value) {
                this.val = this.value;
            }
        },
    }
</script>
