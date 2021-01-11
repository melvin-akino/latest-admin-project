import { getToken } from '../helpers/token'
import bus from '../eventBus'
import { axios } from '../helpers/axios'

const state = {
  providers: [],
  currencies: []
} 

const getters = {
  providerFilters(state) {
    let allOption = { text: 'All', value: 'all' }
    let filters = [allOption]
    state.providers.map(provider => {
      let option = { text: provider.alias, value: provider.id }
      filters.push(option)
    })
    return filters
  },
  currencyFilters(state) {
    let allOption = { text: 'All', value: 'all' }
    let filters = [allOption]
    state.currencies.map(currency => {
      let option = { text: currency.code, value: currency.id }
      filters.push(option)
    })
    return filters
  }
}

const mutations = {
  SET_PROVIDERS: (state, providers) => {
    state.providers = providers
  },
  SET_CURRENCIES: (state, currencies) => {
    state.currencies = currencies
  }  
} 

const actions = {
  getProviders({commit, dispatch}) {
    axios.get('providers', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_PROVIDERS', response.data.data)
    })
    .catch(err => {
      commit('SET_PROVIDERS', [])
      dispatch('auth/logoutOnError', err.response.status, { root: true })
      bus.$emit("SHOW_SNACKBAR", {
        color: "error",
        text: err.response.data.message
      });
    })
  },
  getCurrencies({commit, dispatch}) {
    axios.get('currencies', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_CURRENCIES', response.data.data)
    })
    .catch(err => {
      commit('SET_CURRENCIES', [])
      dispatch('auth/logoutOnError', err.response.status, { root: true })
      bus.$emit("SHOW_SNACKBAR", {
        color: "error",
        text: err.response.data.message
      });
    })
  }
}

export default {
  state, getters, mutations, actions, namespaced: true
}