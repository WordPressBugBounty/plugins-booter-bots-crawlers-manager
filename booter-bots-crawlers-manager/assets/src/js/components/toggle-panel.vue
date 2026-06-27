<template>
    <div class="widget" :class="{open: isExpanded}">
        <div class="widget-top" @click="isExpanded = !isExpanded">
            <div class="widget-title-action">
                <button type="button" class="widget-action" aria-expanded="true" :aria-expanded="isExpanded" :aria-controls="$id('panel')">
                    <span class="screen-reader-text" v-text="booter.trans.toggle_panel"></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="widget-title ui-sortable-handle">
                <h3 :id="$id('title')">
                    <slot name="title"></slot>
                    <span class="in-widget-title"></span>
                </h3>
            </div>
        </div>

        <div class="widget-inside" :aria-describedby="$id('title')" :id="$id('panel')">
            <div class="widget-content">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .widget-content {
        border-top: none;
        padding: 1px 15px 15px 15px;
        line-height: 1.2;
    }
    .widget-top {
        cursor: pointer !important;
        border-radius: 4px;
        background: #F7FAFC;
    }
    .widget-top .dashicons {
        vertical-align: middle;
        color: #A0AEC0;
        padding-bottom: 1px;
    }
    .widget.open .widget-inside {
        display: block;
        border-top: 0;
    }
    .widget.open .widget-top {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
    .widget.open .widget-content {
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }
    .widget {
        margin-top: 18px;
    }
</style>
<script>
    export default {
        props: ['start-open'],
        data() {
            return {
                isExpanded: this.startOpen !== undefined,
            };
        },
    };
</script>
