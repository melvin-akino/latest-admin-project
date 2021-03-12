import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  currencies: []
} 

const getters = {
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
  SET_CURRENCIES: (state, currencies) => {
    state.currencies = currencies
  }  
} 

const actions = {
  getCurrencies({commit, dispatch}) {
    axios.get('currencies', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_CURRENCIES', response.data.data)
    })
    .catch(err => {
      commit('SET_CURRENCIES', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  }
}

export default {
  state, getters, mutations, actions, namespaced: true
}