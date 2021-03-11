import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

import auth from './auth'
import users from './users'
import admin from './admin'
import providerAccounts from './providerAccounts'
import providers from './providers'
import currencies from './currencies'
import systemConfigurations from './systemConfigurations'
import generalErrors from './generalErrors'
import providerErrors from './providerErrors'
import wallet from './wallet'
import masterlistMatching from './masterlistMatching'

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
    providerAccounts,
    providers,
    currencies,
    systemConfigurations,
    generalErrors,
    providerErrors,
    wallet,
    masterlistMatching
  }
})
