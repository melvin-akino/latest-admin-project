import Vue from 'vue'
import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  generalErrors: [],
  isLoadingGeneralErrors: false
}

const getters = {
  sortedGeneralErrors(state) {
    return state.generalErrors.sort((a, b) => (a.error.toLowerCase() > b.error.toLowerCase()) ? 1 : -1)
  }
}

const mutations = {
  SET_GENERAL_ERRORS: (state, errors) => {
    state.generalErrors = errors
  },
  SET_IS_LOADING_GENERAL_ERRORS: (state, loadingState) => {
    state.isLoadingGeneralErrors = loadingState
  },
  ADD_GENERAL_ERROR: (state, error) => {
    state.generalErrors.push(error)
  },
  UPDATE_GENERAL_ERROR: (state, error) => {
    state.generalErrors.map(generalError => {
      if(generalError.id == error.id) {
        Vue.set(generalError, 'error', error.error)
      }
    })
  }
}

const actions = {
  getGeneralErrors({commit, dispatch}) {
    commit('SET_IS_LOADING_GENERAL_ERRORS', true)
    axios.get('general-errors', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_GENERAL_ERRORS', response.data.data)
      commit('SET_IS_LOADING_GENERAL_ERRORS', false)
    })
    .catch(err => {
      commit('SET_GENERAL_ERRORS', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
  manageGeneralErrors({commit, dispatch}, payload) {
    return new Promise((resolve, reject) => {
      axios.post('general-errors/manage', payload, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        if(payload.id) {
          commit('UPDATE_GENERAL_ERROR', response.data.data)
        } else {
          commit('ADD_GENERAL_ERROR', response.data.data)
        }
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
  state, getters, mutations, actions, namespaced: true
}