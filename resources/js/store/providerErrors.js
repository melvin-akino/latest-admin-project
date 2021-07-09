import Vue from 'vue'
import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  providerErrors: [],
  isLoadingProviderErrors: false,
  retryTypes: []
}

const getters = {
  sortedProviderErrors(state) {
    return state.providerErrors.sort((a, b) => (a.message.toLowerCase() > b.message.toLowerCase()) ? 1 : -1)
  }
}

const mutations = {
  SET_PROVIDER_ERRORS: (state, errors) => {
    state.providerErrors = errors
  },
  SET_IS_LOADING_PROVIDER_ERRORS: (state, loadingState) => {
    state.isLoadingProviderErrors = loadingState
  },
  SET_RETRY_TYPES: (state, retryTypes) => {
    state.retryTypes = retryTypes
  },
  ADD_PROVIDER_MESSAGE: (state, error) => {
    state.providerErrors.push(error)
  },
  UPDATE_PROVIDER_MESSAGE: (state, error) => {
    state.providerErrors.map(providerError => {
      if(providerError.id == error.id) {
        Vue.set(providerError, 'message', error.message)
        Vue.set(providerError, 'error', error.error) 
        Vue.set(providerError, 'retry_type_id', error.retry_type_id) 
        Vue.set(providerError, 'odds_have_changed', error.odds_have_changed) 
      }
    })
  }
}

const actions = {
  getProviderErrors({commit, dispatch}) {
    commit('SET_IS_LOADING_PROVIDER_ERRORS', true)
    axios.get('provider-errors', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_PROVIDER_ERRORS', response.data)
      commit('SET_IS_LOADING_PROVIDER_ERRORS', false)
    })
    .catch(err => {
      commit('SET_PROVIDER_ERRORS', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
  manageProviderErrors({commit, dispatch, rootState}, payload) {
    return new Promise((resolve, reject) => {
      axios.post('provider-errors/manage', payload, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        let errorMessage = rootState.generalErrors.generalErrors.filter(error => error.id === response.data.data.error_message_id).map(error => error.error)[0]
        Vue.set(response.data.data, 'error', errorMessage)
        if(payload.id) {
          commit('UPDATE_PROVIDER_MESSAGE', response.data.data)
        } else {
          commit('ADD_PROVIDER_MESSAGE', response.data.data)
        }
        resolve()
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  getRetryTypes({commit, dispatch}) {
    axios.get('retry-types', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_RETRY_TYPES', response.data.data)
    })
    .catch(err => {
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
}

export default {
  state, getters, mutations, actions, namespaced: true
}