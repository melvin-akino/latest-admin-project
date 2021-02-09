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
    drawer: null,
    cancelTokens: []
  },
  mutations: {
    SET_DRAWER: (state, payload) => {
      state.drawer = payload
    },
    ADD_CANCEL_TOKEN: (state, token) => {
      state.cancelTokens.push(token)
    },
    CLEAR_CANCEL_TOKENS: (state) => {
      state.cancelTokens = []
    }
  },
  actions: {
    cancelPendingRequests({state, commit}) {
      state.cancelTokens.map((request, index) => {
        if(request.cancel) {
          request.cancel('Pending request cancelled')
        }
      })
      commit('CLEAR_CANCEL_TOKENS')
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
