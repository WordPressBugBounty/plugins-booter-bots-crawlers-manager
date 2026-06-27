<template>
    <transition name="modal-fade">
        <div class="modal-backdrop">
            <div class="modal"
                 role="dialog"
                 :aria-labelledby="$id('modalTitle')"
                 :aria-describedby="$id('modalDescription')"
            >
                <header class="modal-header" :id="$id('modalTitle')">
                    <slot name="header"></slot>
                </header>
                <section class="modal-body" :id="$id('modalDescription')">
                    <slot name="body"></slot>
                </section>

                <footer class="modal-footer">
                    <slot name="footer">
                        <button type="button"
                                class="button button-success"
                                @click="close"
                                v-text="booter.trans.close"
                        ></button>
                    </slot>
                </footer>
            </div>
        </div>
    </transition>
</template>

<style>
    .modal-fade-enter.modal-backdrop,
    .modal-fade-leave-active.modal-backdrop {
        opacity: 0;
    }
    .modal-fade-enter .modal,
    .modal-fade-leave-active .modal {
        opacity: 0;
        transform: translateY(100px) scale(0.9);
    }

    .modal-fade-enter-active.modal-backdrop,
    .modal-fade-leave-active.modal-backdrop,
    .modal-fade-enter-active .modal,
    .modal-fade-leave-active .modal {
        transition: opacity .3s ease,
                    transform .1s ease;
    }

    @media (prefers-reduced-motion: reduce) {
        .modal-fade-enter-active.modal-backdrop,
        .modal-fade-leave-active.modal-backdrop,
        .modal-fade-enter-active .modal,
        .modal-fade-leave-active .modal {
            transition: none;
        }
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.3);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1040;
    }

    .modal {
        background: #FFFFFF;
        box-shadow: 2px 2px 20px 1px;
        overflow-x: auto;
        display: flex;
        flex-direction: column;
        min-width: 480px;
        max-width: 80%;
        max-width: 80vw;
        z-index: 1050;
    }

    .modal-header,
    .modal-footer {
        padding: 15px;
        display: flex;
    }

    .modal-header {
        border-bottom: 1px solid #eeeeee;
        justify-content: space-between;
    }

    .modal-footer {
        border-top: 1px solid #eeeeee;
        justify-content: flex-end;
    }

    .modal-body {
        position: relative;
        padding: 20px 10px;
        max-height: 60%;
        max-height: 60vh;
        overflow: auto;
    }

    .btn-close {
        border: none;
        font-size: 20px;
        padding: 20px;
        cursor: pointer;
        font-weight: bold;
        color: #4AAE9B;
        background: transparent;
    }
</style>

<script>
    export default {
        name: 'modal',
        methods: {
            close() {
                this.$emit('close');
            },
        },
    };
</script>
