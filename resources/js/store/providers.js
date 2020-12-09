import Vue from 'vue'
import { getToken } from '../helpers/token'

const state = {
  providerAccounts: [],
  filteredProviderAccounts: [],
  isLoadingProviderAccounts: false,
  providerStatus: [
    {
      text: 'Active',
      value: true
    },
    { 
      text: 'Inactive',
      value: false
    }
  ],
  providerAccountTypes: ['BET_NORMAL', 'BET_VIP', 'SCRAPER', 'SCRAPER_MIN_MAX'],
  providerIdleOptions: [
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
  SET_PROVIDER_ACCOUNTS: (state, providerAccounts) => {
    state.providerAccounts = providerAccounts
  },
  SET_FILTERED_PROVIDER_ACCOUNTS: (state, providerAccounts) => {
    state.filteredProviderAccounts = providerAccounts
  },
  SET_IS_LOADING_PROVIDER_ACCOUNTS: (state, loadingState) => {
    state.isLoadingProviderAccounts = loadingState
  },
  ADD_PROVIDER_ACCOUNT: (state, providerAccount) => {
    let newProviderAccount = {
      id: providerAccount.id,
      username: providerAccount.username,
      password: providerAccount.password,
      type: providerAccount.type,
      punter_percentage: providerAccount.punter_percentage,
      credits: providerAccount.credits,
      is_enabled: providerAccount.is_enabled,
      is_idle: providerAccount.is_idle,
      provider_id: providerAccount.provider_id,
      currency_id: providerAccount.currency_id,
      pl: '-',
      open_orders: '-',
      last_bet: '-',
    }
    state.providerAccounts.unshift(newProviderAccount)
  },
  UPDATE_PROVIDER_ACCOUNT: (state, providerAccount) => {
    let updatedProviderAccount = {
      username: providerAccount.username,
      password: providerAccount.password,
      type: providerAccount.type,
      punter_percentage: providerAccount.punter_percentage,
      is_enabled: providerAccount.is_enabled,
      is_idle: providerAccount.is_idle,
      provider_id: providerAccount.provider_id,
      currency_id: providerAccount.currency_id
    }
    state.providerAccounts.map(account => {
      if (account.id == providerAccount.id) {
        Object.keys(updatedProviderAccount).map(key => {
          Vue.set(account, key, updatedProviderAccount[key])
        })
      }
    });
  }
}

const actions = {
  getProviderAccounts() { 
    return new Promise((resolve, reject) => {
      axios.get('provider-accounts', { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        resolve(response.data.data)
      })
      .catch(err => {
        console.log(err)
        reject()
      })
    })
  },
  getProviderAccountOrders({}, id) {
    return new Promise((resolve, reject) => {
      axios.get('orders', { params: { id }, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        resolve(response.data)
      })
      .catch(err => {
        console.log(err)
        reject()
      })
    })
  },
  async getProviderAccountsList({state, commit, dispatch}) {
    commit('SET_IS_LOADING_PROVIDER_ACCOUNTS', true)
    let providerAccounts = await dispatch('getProviderAccounts')
    commit('SET_PROVIDER_ACCOUNTS', providerAccounts)
    commit('SET_FILTERED_PROVIDER_ACCOUNTS', providerAccounts)
    commit('SET_IS_LOADING_PROVIDER_ACCOUNTS', false)
    state.providerAccounts.map(async account => {
      let providerAccountOrder = await dispatch('getProviderAccountOrders', account.id)
      if(providerAccountOrder.length != 0) {
        Vue.set(account, 'pl', providerAccountOrder.pl)
        Vue.set(account, 'open_orders', providerAccountOrder.open_orders)
        Vue.set(account, 'last_bet', providerAccountOrder.last_bet)
        Vue.set(account, 'last_scrape', providerAccountOrder.last_scrape)
        Vue.set(account, 'last_sync', providerAccountOrder.last_sync)
      } else {
        Vue.set(account, 'pl', '-')
        Vue.set(account, 'open_orders', '-')
        Vue.set(account, 'last_bet', '-')
        Vue.set(account, 'last_scrape', '-')
        Vue.set(account, 'last_sync', '-')
      }
    })
  },
  manageProviderAccount({commit, rootState}, payload) {
    return new Promise((resolve, reject) => {
      axios.post('provider-accounts/manage', payload, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        let currency = rootState.resources.providers.filter(provider => provider.id == response.data.data.provider_id).map(provider => provider.currency_id)
        Vue.set(response.data.data, 'currency_id', currency[0])
        if(payload.id) {
          commit('UPDATE_PROVIDER_ACCOUNT', response.data.data)
        } else {
          commit('ADD_PROVIDER_ACCOUNT', response.data.data)
        }
        resolve()
      })
      .catch(err => {
        console.log(err)
        reject()
      })
    })
  }
}

export default {
  state, mutations, actions, namespaced: true
}
