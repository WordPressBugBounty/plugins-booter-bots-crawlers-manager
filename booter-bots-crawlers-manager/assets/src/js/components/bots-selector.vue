<template>
    <div>
        <button type="button" @click="getBadBotsList" class="button button-info" :disabled="isDownloading" :aria-busy="isDownloading">
            <span v-if="isDownloading" class="spinner is-active" aria-hidden="true"></span>
            <span v-else class="dashicons dashicons-update" aria-hidden="true"></span>
            <span v-text="booter.trans.update_bad_robots_list"></span>
        </button>

        <p class="description" v-text="booter.trans.bad_bots_description"></p>

      <tags-list
          :id="id"
          :add-label-text="booter.trans.add_user_agent"
          :name="name"
          v-model="bots"
          :disabled="isDownloading">
      </tags-list>
    </div>
</template>

<script>
    export default {
        props: ['action', 'value', 'id', 'name'],

        data() {
            return {
                isDownloading: false,
                bots: []
            };
        },
        mounted() {
            if(typeof this.value === 'string') {
                this.bots = JSON.parse(this.value) || [];
            }
        },
        methods: {
            updateBotsList(newlist) {
                const remaining = newlist.filter(b => !this.bots.includes(b));
                this.bots = this.bots.concat(remaining);
            },

            getBadBotsList() {
                this.isDownloading = true;

                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.booter.ajax_url, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.addEventListener('load', () => {
                    const newlist = JSON.parse(xhr.responseText) || [];
                    this.updateBotsList(newlist);
                });

                xhr.addEventListener('loadend', () => this.isDownloading = false);

                xhr.send(`action=${encodeURIComponent(this.action)}&_wpnonce=${encodeURIComponent(this.booter.ajax_nonce)}`);
            }
        },
    };
</script>
