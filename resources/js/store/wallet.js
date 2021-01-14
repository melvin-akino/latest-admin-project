import Vue from 'vue'
import { getToken, getWalletToken } from '../helpers/token'
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
  },
  ADD_CLIENT: (state, client) => {
    state.clients.push(client)
  },
  UPDATE_CLIENT: (state, client_id) => {
    state.clients.map(client => {
      if(client.client_id == client_id) {
        Vue.set(client, 'revoked', true)
      }
    })
  }
}

const actions = {
  getClients({commit, dispatch}) {
    commit('SET_IS_LOADING_CLIENTS', true)
    axios.get('wallet/clients', { params: { wallet_token: getWalletToken() }, headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_CLIENTS', response.data.data)
      commit('SET_IS_LOADING_CLIENTS', false)
    })
    .catch(err => {
      commit('SET_CLIENTS', [])
      dispatch('auth/logoutOnError', err.response.status, { root: true })
      bus.$emit("SHOW_SNACKBAR", {
        color: "error",
        text: err.response.data.error
      });
    })
  },
  createClient({commit, dispatch}, data) {
    return new Promise((resolve, reject) => {
      axios.post('wallet/create', data, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('ADD_CLIENT', response.data.data)
        resolve(response.data.message)
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  revokeClient({commit, dispatch}, data) {
    return new Promise((resolve, reject) => {
      axios.post('wallet/revoke', data, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('UPDATE_CLIENT', data.client_id)
        resolve(response.data.data.message)
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