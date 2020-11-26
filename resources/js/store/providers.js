import Vue from 'vue'
import Cookies from 'js-cookie'
const token = Cookies.get('access_token')

const state = {
  providerAccounts: [],
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
      is_idle: providerAccount.is_idle
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
      is_idle: providerAccount.is_idle
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
  getProviderAccounts({commit}) {
    commit('SET_IS_LOADING_PROVIDER_ACCOUNTS', true)
    axios.get('provider_accounts', { params: { id: 1 }, headers: { 'Authorization': `Bearer ${token}` } })
    .then(response => {
      commit('SET_PROVIDER_ACCOUNTS', response.data.data)
      commit('SET_IS_LOADING_PROVIDER_ACCOUNTS', false)
    })
    .catch(err => {
      console.log(err)
    })
  },
  manageProviderAccount({commit}, payload) {
    return new Promise((resolve, reject) => {
      axios.post('provider_accounts/manage', payload, { headers: { 'Authorization': `Bearer ${token}` } })
      .then(response => {
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
