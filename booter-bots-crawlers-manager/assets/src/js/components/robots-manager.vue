<template>
    <div>
        <div id="contextual-help-wrap" class="robots-manager">
            <div id="contextual-help-back"></div>
            <div id="contextual-help-columns">
                <div class="contextual-help-tabs">
                    <ul role="tablist">
                        <li role="presentation" v-for="(robot, index) in robots" :id="$id(`tab-robots-${index}`)" :class="{ active: activeIndex === index, danger: (robot.useragent.length <= 0) }">
                            <button type="button" @click="showModal(index)" :aria-label="booter.trans.delete_crawler"><i class="dashicons dashicons-dismiss" aria-hidden="true"></i></button>
                            <a role="tab" :href="`#tab-panel-robots-${index}`"
                               :id="$id(`tab-robots-${index}`)"
                               aria-controls="tab-panel-robots-global"
                               v-text="robot.useragent === '*' ? booter.trans.all_crawlers : (robot.useragent.length > 0 ? robot.useragent : booter.trans.new_crawler_useragent)"
                               @click.prevent="activeIndex = index"
                            ></a>
                        </li>
                        <li role="presentation" @click.prevent="add" class="new-crawler" :class="{ active: robots.length <= 0}">
                            <a role="button" href="#" v-text="booter.trans.add_new_crawler"></a>
                        </li>
                    </ul>
                </div>

                <div class="contextual-help-tabs-wrap">
                    <div v-if="robots.length">
                        <section role="tabpanel"
                                 :aria-labelledby="$id(`tab-robots-${index}`)"
                                 v-for="(robot, index) in robots"
                                 :id="$id(`tab-panel-robots-${index}`)"
                                 class="help-tab-content"
                                 :class="activeIndex === index ? 'active' : ''"
                                 :hidden="activeIndex !== index">
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row"><label :for="$id(`robots-${index}-useragent`)" v-text="booter.trans.user_agent"></label></th>
                                    <td>
                                        <input :id="$id(`robots-${index}-useragent`)"
                                               type="text"
                                               class="text"
                                               :class="{ error: uaExists(robot) }"
                                               placeholder="Googlebot"
                                               :name="`${name}[${index}][useragent]`"
                                               v-model="robot.useragent"
                                               required @invalid="e => focusRobot(e, index)"
                                               list="known-robots"
                                        >
                                        <p class="description" v-text="booter.trans.user_agent_star"></p>
                                        <p v-if="uaExists(robot)" class="notice notice-error" v-text="booter.trans.user_agent_exists"></p>
                                        <p v-if="!uaValid(robot)" class="notice notice-error" v-text="booter.trans.user_agent_invalid"></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><label :for="$id(`robots-${index}-crawl_rate`)" v-text="booter.trans.crawl_rate"></label></th>
                                    <td>
                                        <select :id="$id(`robots-${index}-crawl_rate`)" :name="`${name}[${index}][crawl_rate]`" v-model="robot.crawl_rate">
                                            <option value="0" v-text="booter.trans.not_defined"></option>
                                            <option value="5" v-text="booter.trans.n_seconds.replace('%s', 5)"></option>
                                            <option value="10" v-text="booter.trans.n_seconds.replace('%s', 10)"></option>
                                            <option value="20" v-text="booter.trans.n_seconds.replace('%s', 20)"></option>
                                            <option value="60" v-text="booter.trans.n_minutes.replace('%s', 1)"></option>
                                            <option value="120" v-text="booter.trans.n_minutes.replace('%s', 2)"></option>
                                        </select>
                                        <strong v-text="booter.trans.between_scans"></strong>
                                        <p class="description">
                                            <span v-text="booter.trans.crawl_rate_description"></span>
                                            <br v-if="robot.crawl_rate === '0'">
                                            <span v-if="robot.crawl_rate === '0'" v-text="booter.trans.no_crawl_rate_description"></span>
                                        </p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><label :for="$id(`robots-${index}-restrict_wp_admin`)" v-text="booter.trans.disallow_dashboard"></label></th>
                                    <td>
                                        <booter-switch :id="$id(`robots-${index}-restrict_wp_admin`)" :name="`${name}[${index}][restrict_wp_admin]`" v-model="robot.restrict_wp_admin" :text-on="booter.trans.yes" :text-off="booter.trans.no"></booter-switch>
                                        <p class="description" v-html="booter.trans.disallow_dashboard_description"></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><label :for="$id(`robots-${index}-allow_wp_ajax`)" v-text="booter.trans.allow_ajax"></label></th>
                                    <td>
                                        <booter-switch :id="$id(`robots-${index}-allow_wp_ajax`)" :name="`${name}[${index}][allow_wp_ajax]`" v-model="robot.allow_wp_ajax" :text-on="booter.trans.yes" :text-off="booter.trans.no"></booter-switch>
                                        <p class="description" v-html="booter.trans.allow_ajax_description"></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row" v-text="booter.trans.path_rules"></th>
                                    <td class="margin-bottom: 0; padding-bottom: 0;">
                                        <p class="notice">
                                            <span class="dashicons dashicons-info" aria-hidden="true"></span>
                                            <span v-text="booter.trans.empty_robots_disallow"></span><br>
                                        </p>

                                        <p class="description">
                                            <span v-text="booter.trans.allow_disallow_wildcards"></span><br>
                                            <span v-html="booter.trans.allow_disallow_wildcards_star"></span><br>
                                            <span v-html="booter.trans.allow_disallow_wildcards_dollar"></span>
                                        </p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label :for="$id(`robots-${index}-disallow`)" v-text="booter.trans.disallowed_links"></label></th>
                                    <td class="margin-top: 0; padding-top: 0;">
                                        <strings-list :id="$id(`robots-${index}-disallow`)"
                                                      :name="`${name}[${index}][disallow]`"
                                                      :add-label-text="booter.trans.add_disallowed_url"
                                                      :before-input-text="booter.site_url"
                                                      force-start-with="/"
                                                      v-model="robot.disallow"></strings-list>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label :for="$id(`robots-${index}-allow`)" v-text="booter.trans.allowed_links"></label></th>
                                    <td>
                                        <strings-list :id="$id(`robots-${index}-allow`)"
                                                      :name="`${name}[${index}][allow]`"
                                                      :add-label-text="booter.trans.add_allowed_url"
                                                      :before-input-text="booter.site_url"
                                                      force-start-with="/"
                                                      v-model="robot.allow"></strings-list>
                                    </td>
                                </tr>
                            </table>
                        </section>
                    </div>
                    <div v-else class="text-center no-robots">
                        <p v-text="booter.trans.no_crawlers_defined"></p>
                        <button type="button" class="button" v-text="booter.trans.add_new_crawler" @click="add"></button>
                    </div>
                </div>
            </div>
        </div>

        <booter-modal v-show="modal.visible" @close="closeModal">
            <template slot="header"><strong v-text="booter.trans.confirm_title"></strong></template>
            <template slot="body"><span v-text="booter.trans.confirm_delete_crawler.replace('%s', (modal.robot && modal.robot.useragent ? modal.robot.useragent : booter.trans.new_crawler_useragent))"></span></template>
            <template slot="footer">
                <div>
                    <button type="button" class="button" v-text="booter.trans.cancel" @click="closeModal"></button>
                    <button type="button" class="button button-danger" v-text="booter.trans.delete" @click="remove(modal.robotIndex)"></button>
                </div>
            </template>
        </booter-modal>
    </div>
</template>

<script>
    export default {
        props: ['name', 'value'],
        data() {
            return {
                activeIndex: 0,
                robots: [],
                modal: {
                    visible: false,
                    robotIndex: null,
                    robot: null
                }
            };
        },
        mounted() {
            if(this.value && typeof this.value === 'string') {
                try {
                    this.robots = JSON.parse(this.value);
                } catch(err) {
                    console.error(`Failed parsing currnet value: ${err}\n'${this.value}'`);
                }
            }

            // make sure we have at lease one crawler defined
            if(this.robots.length <= 0) {
                this.robots.push({
                    useragent: '*',
                    crawl_rate: '0',
                    restrict_wp_admin: true,
                    allow_wp_ajax: true,
                    disallow: [
                        '/*public_html/',
                        '/*index.php?',
                    ],
                    allow: []
                });
            }
        },
        methods: {
            uaValid(robot) {
                return ! /[()<>@,;:\\"\/\[\]?={}\s\t]/.test(robot.useragent);
            },
            uaExists(robot) {
                return this.robots.some(r => r !== robot && r.useragent.trim().toLowerCase() === robot.useragent.trim().toLowerCase());
            },
            add() {
                this.robots.push({
                    useragent: '',
                    crawl_rate: '0',
                    restrict_wp_admin: true,
                    allow_wp_ajax: true,
                    disallow: [
                        '/*public_html/',
                        '/*index.php?',
                    ],
                    allow: []
                });
                this.activeIndex = this.robots.length - 1;
            },
            showModal(index) {
                let robot = this.robots[index];
                this.modal.visible = true;
                this.modal.robot = robot;
                this.modal.robotIndex = index;
            },
            closeModal() {
                this.modal.visible = false;
                this.modal.robot = null;
                this.modal.robotIndex = null;
            },
            remove(index) {
                this.closeModal();
                this.robots.splice(index, 1);
                if(this.activeIndex === this.robots.length) {
                    this.activeIndex = this.robots.length - 1;
                }
            },
            focusRobot(e, index) {
                this.activeIndex = index;
                e.target.focus();
            },
            updateRobot(e, opt, index) {
                this.robots[index][opt] = e;
            }
        }
    }
</script>


<style scoped>
    #contextual-help-back {
        right: 0;
    }
    body.rtl #contextual-help-back {
        left: 0;
        right: 150px;
    }
    .contextual-help-tabs li button {
        background: none;
        color: #0073aa;
        border: 1px solid transparent;
        display: block;
        border-left: none;
        border-right: none;
    }
    .contextual-help-tabs .danger.active {
        border-right-color: #dc3232;
        background-color: #f6cbcb;
    }
    .contextual-help-tabs .danger {
        background-color: #f8d8d8;
    }
    .contextual-help-tabs .active button,
    .contextual-help-tabs li:hover button {
        color: #32373c;
    }
    .contextual-help-tabs .danger a,
    .contextual-help-tabs .danger button {
        color: #004a6d;
    }
    .contextual-help-tabs .active button {
        border-color: #e1e1e1;
    }
    .contextual-help-tabs .new-crawler {
        border-left-color: #00a0d2;
        margin-top: 1em;
        background: linear-gradient(to right, #bee1ec, transparent);
    }
    body.rtl .contextual-help-tabs .new-crawler {
        border-left-color: transparent;
        border-right-color: #00a0d2;
        margin-top: 1em;
        background: linear-gradient(to left, #bee1ec, transparent);
    }

    .contextual-help-tabs li {
        display: flex;
    }
    .contextual-help-tabs li .dashicons {
        width: 1em;
        height: 1em;
        font-size: 1em;
    }
    .contextual-help-tabs li a {
        flex: 1;
    }
</style>
