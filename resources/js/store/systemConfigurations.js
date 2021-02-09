import Vue from 'vue'
import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  systemConfigurations: [],
  isLoadingSystemConfigurations: false
}

const mutations = {
  SET_SYSTEM_CONFIGURATIONS: (state, systemConfigurations) => {
    state.systemConfigurations = systemConfigurations
  },
  SET_LOADING_SYSTEM_CONFIGURATIONS: (state, loadingState) => {
    state.isLoadingSystemConfigurations = loadingState
  },
  UPDATE_SYSTEM_CONFIGURATION: (state, systemConfiguration) => {
    state.systemConfigurations.map(config => {
      if(config.id == systemConfiguration.id) {
        Vue.set(config, 'value', systemConfiguration.value)
        Vue.set(config, 'module', systemConfiguration.module)
      }
    })
  }
}

const actions = {
  getSystemConfigurations({commit, dispatch}) {
    commit('SET_LOADING_SYSTEM_CONFIGURATIONS', true)
    axios.get('system-configurations', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_SYSTEM_CONFIGURATIONS', response.data.data)
      commit('SET_LOADING_SYSTEM_CONFIGURATIONS', false)
    })
    .catch(err => {
      commit('SET_SYSTEM_CONFIGURATIONS', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
  manageSystemConfiguration({commit, dispatch}, systemConfiguration) {
    return new Promise((resolve, reject) => {
      axios.post('system-configurations/manage', systemConfiguration, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(() => {
        commit('UPDATE_SYSTEM_CONFIGURATION', systemConfiguration)
        resolve()
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  }
}

export default {
  state, mutations, actions, namespaced: true
}