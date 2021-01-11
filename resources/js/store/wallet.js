import { getWalletToken } from '../helpers/token'
import { walletAxios } from '../helpers/axios'
import bus from '../eventBus'

const state = {
  clients: [],
  isLoadingClients: false
}

const mutations = {
  SET_CLIENTS: (state, clients) => {
    state.clients = clients
  },
  SET_IS_LOADING_CLIENTS: (state, loadingState) => {
    state.isLoadingClients = loadingState
  }
}

const actions = {
  login({}, walletCredentials) {
    return new Promise((resolve, reject) => {
      walletAxios.post('oauth/token', walletCredentials)
      .then(response => {
        resolve(response.data.data.access_token)
      })
      .catch(err => {
        reject(err)
      });
    })
  },
  getClients({commit, dispatch}) {
    commit('SET_IS_LOADING_CLIENTS', true)
    walletAxios.get('clients', { headers: { 'Authorization': `Bearer ${getWalletToken()}` } })
    .then(response => {
      commit('SET_CLIENTS', response.data.data)
      commit('SET_IS_LOADING_CLIENTS', false)
    })
    .catch(err => {
      commit('SET_CLIENTS', [])
      dispatch('auth/logoutOnError', err.response.status, { root: true })
      bus.$emit("SHOW_SNACKBAR", {
        color: "error",
        text: err.response.data.message
      });
    })
  }
}

export default {
  state, mutations, actions, namespaced: true
}