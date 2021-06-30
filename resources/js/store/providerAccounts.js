import Vue from 'vue'
import { getToken, getWalletToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  providerAccounts: [],
  filteredProviderAccounts: [],
  isLoadingProviderAccounts: false,
  providerAccountStatus: [
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
  providerAccountIdleOptions: [
    {
      text: 'Yes',
      value: true
    },
    {
      text: 'No',
      value: false
    }
  ],
  providerAccountUsages: []
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
  SET_PROVIDER_ACCOUNT_USAGES: (state, usages) => {
    state.providerAccountUsages = usages
  },
  ADD_PROVIDER_ACCOUNT: (state, providerAccount) => {
    let newProviderAccount = {
      id: providerAccount.id,
      line: providerAccount.line,
      username: providerAccount.username,
      password: providerAccount.password,
      type: providerAccount.type,
      punter_percentage: providerAccount.punter_percentage,
      credits: 0,
      is_enabled: providerAccount.is_enabled,
      is_idle: providerAccount.is_idle,
      provider: providerAccount.provider,
      provider_id: providerAccount.provider_id,
      currency_id: providerAccount.currency_id,
      uuid: providerAccount.uuid,
      usage: providerAccount.usage,
      pl: '-',
      open_orders: '-',
      last_bet: '-',
      last_scrape: '-',
      last_sync: '-'
    }
    state.providerAccounts.unshift(newProviderAccount)
  },
  UPDATE_PROVIDER_ACCOUNT: (state, providerAccount) => {
    let updatedProviderAccount = {
      line: providerAccount.line,
      username: providerAccount.username,
      password: providerAccount.password,
      type: providerAccount.type,
      punter_percentage: providerAccount.punter_percentage,
      is_enabled: providerAccount.is_enabled,
      is_idle: providerAccount.is_idle,
      provider: providerAccount.provider,
      provider_id: providerAccount.provider_id,
      currency_id: providerAccount.currency_id,
      usage: providerAccount.usage
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
  getProviderAccounts({dispatch}) { 
    return new Promise((resolve, reject) => {
      axios.get('provider-accounts', { headers: { 'Authorization': `Bearer ${getToken()}` } })
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
  getProviderAccountOrders({dispatch}, id) {
    return new Promise((resolve, reject) => {
      axios.get('orders', { params: { id }, headers: { 'Authorization': `Bearer ${getToken()}` } })
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
  async getProviderAccountsList({state, commit, dispatch}) {
    try {
      commit('SET_IS_LOADING_PROVIDER_ACCOUNTS', true)
      let providerAccounts = await dispatch('getProviderAccounts')
      await dispatch('getProviderAccountUsages')
      commit('SET_PROVIDER_ACCOUNTS', providerAccounts)
      commit('SET_FILTERED_PROVIDER_ACCOUNTS', providerAccounts)
      commit('SET_IS_LOADING_PROVIDER_ACCOUNTS', false)
      let providerAccountOtherData = ['pl', 'open_orders', 'last_bet', 'last_scrape', 'last_sync']
      state.providerAccounts.map(async account => {
        let providerAccountOrder = await dispatch('getProviderAccountOrders', account.id)
        let wallet = await dispatch('wallet/getWalletBalance', { uuid: account.uuid, currency: account.currency, wallet_token: getWalletToken() }, { root: true })
        if(providerAccountOrder.length != 0) {
          providerAccountOtherData.map(key => {
            Vue.set(account, key, providerAccountOrder[key])
          })
        } else {
          providerAccountOtherData.map(key => {
            Vue.set(account, key, '-')
          })
        }
        if(wallet) {
          Vue.set(account, 'credits', wallet.balance)
        } else {
          Vue.set(account, 'credits', '-')
        }
      })
    } catch(err) {
      commit('SET_PROVIDER_ACCOUNTS', [])
      commit('SET_FILTERED_PROVIDER_ACCOUNTS', [])
      bus.$emit("SHOW_SNACKBAR", {
        color: "error",
        text: err.response.data.message
      });
    }
  },
  manageProviderAccount({commit, dispatch, rootState}, payload) {
    return new Promise((resolve, reject) => {
      axios.post('provider-accounts/manage', payload, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        let currency = rootState.providers.providers.filter(provider => provider.id == response.data.data.provider_id).map(provider => provider.currency_id)
        Vue.set(response.data.data, 'currency_id', currency[0])
        if(payload.id) {
          commit('UPDATE_PROVIDER_ACCOUNT', response.data.data)
        } else {
          commit('ADD_PROVIDER_ACCOUNT', response.data.data)
        }
        resolve()
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  createSettlement({dispatch}, payload) {
    return new Promise((resolve, reject) => {
      axios.post('settlements/create', payload, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        resolve(response.data.message)
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  getProviderAccountUsages({commit, dispatch}) {
    return new Promise((resolve, reject) => {
      axios.get('provider-accounts/usages', { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('SET_PROVIDER_ACCOUNT_USAGES', response.data.data)
        resolve()
      })
      .catch(err => {
        if(!axios.isCancel(err)) {
          reject(err)
          dispatch('auth/logoutOnError', err.response.status, { root: true })
        }
      })
    })
  }
}

export default {
  state, mutations, actions, namespaced: true
}
