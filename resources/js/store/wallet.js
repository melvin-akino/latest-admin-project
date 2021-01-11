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
        text: err.response.data.message
      });
    })
  }
}

export default {
  state, mutations, actions, namespaced: true
}