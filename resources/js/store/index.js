import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

import users from './users'
import admin from './admin'
import providers from './providers'

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
    users,
    admin,
    providers
  }
})
