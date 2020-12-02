import { getToken } from '../helpers/token'

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
  getProviders({commit}) {
    axios.get('providers', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_PROVIDERS', response.data.data)
    })
    .catch(err => {
      console.log(err)
    })
  },
  getCurrencies({commit}) {
    axios.get('currencies', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_CURRENCIES', response.data.data)
    })
    .catch(err => {
      console.log(err)
    })
  }
}

export default {
  state, getters, mutations, actions, namespaced: true
}