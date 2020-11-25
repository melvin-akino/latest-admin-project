import Vue from 'vue'
require('./bootstrap');

import App from './App.vue'
import router from './router'
import store from './store'
import './plugins/base'
import './plugins/chartist'
import './plugins/vuelidate'
import vuetify from './plugins/vuetify'
import i18n from './i18n'
Vue.config.devtools = true
Vue.config.productionTip = false

const app = new Vue({
    el: '#app',
    router, 
    store,
    vuetify,
    i18n,
    components: {
        App
    }
});
