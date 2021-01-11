import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

import auth from './auth'
import users from './users'
import admin from './admin'
import providers from './providers'
import resources from './resources'
import systemConfigurations from './systemConfigurations'
import generalErrors from './generalErrors'
import providerErrors from './providerErrors'
import wallet from './wallet'

export default new Vuex.Store({
  state: {
    drawer: null
  },
  mutations: {
    SET_DRAWER: (state, payload) => {
      state.drawer = payload
    }
  },
  modules: {
    auth,
    users,
    admin,
    providers,
    resources,
    systemConfigurations,
    generalErrors,
    providerErrors,
    wallet
  }
})
