import Vue from 'vue'
import { getToken, getWalletToken } from '../helpers/token'
import { handleAPIErrors } from '../helpers/errors'
import bus from '../eventBus'
import moment from 'moment'

const state = {
  clients: [],
  isLoadingClients: false,
  currencies: [],
  isLoadingCurrencies: false,
  currencyOptions: [
    {
      text: 'Yes',
      value: true
    },
    {
      text: 'No',
      value: false
    }
  ]
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
  },
  SET_CURRENCIES: (state, currencies) => {
    state.currencies = currencies
  },
  SET_IS_LOADING_CURRENCIES: (state, loadingState) => {
    state.isLoadingCurrencies = loadingState
  },
  ADD_CURRENCY: (state, currency) => {
    let newCurrency = {
      name: currency.name,
      is_enabled: Boolean(Number(currency.is_enabled)),
      created_at: moment.utc(currency.created_at).format('YYYY-MM-DD HH:mm:ss')
    }
    state.currencies.unshift(newCurrency)
  },
  UPDATE_CURRENCY: (state, updatedCurrency) => {
    state.currencies.map(currency => {
      if(currency.name == updatedCurrency.name) {
        Vue.set(currency, 'is_enabled', updatedCurrency.is_enabled)
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
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: handleAPIErrors(err)
        });
      }
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
  },
  getCurrencies({commit, dispatch}) {
    commit('SET_IS_LOADING_CURRENCIES', true)
    axios.get('wallet/currencies', { params: { wallet_token: getWalletToken() }, headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_CURRENCIES', response.data.data)
      commit('SET_IS_LOADING_CURRENCIES', false)
    })
    .catch(err => {
      commit('SET_CURRENCIES', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: handleAPIErrors(err)
        });
      }
    })
  },
  createCurrency({commit, dispatch}, data) {
    return new Promise((resolve, reject) => {
      axios.post('wallet/currencies/create', data, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('ADD_CURRENCY', response.data.data)
        resolve()
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  updateCurrency({commit, dispatch}, data) {
    return new Promise((resolve, reject) => {
      axios.post('wallet/currencies/update', data, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('UPDATE_CURRENCY', data)
        resolve(response.data.message)
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  getWalletBalance({dispatch}, user) {
    return new Promise((resolve, reject) => {
      axios.get('wallet/balance', { params: user, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        resolve(response.data.data)
      })
      .catch(err => {
        if(!axios.isCancel(err)) {
          reject(err)
          dispatch('auth/logoutOnError', err.response.status, { root: true })
        }
      })
    })
  },
}

export default {
  state, mutations, actions, namespaced: true
}