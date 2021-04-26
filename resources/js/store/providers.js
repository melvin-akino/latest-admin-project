import Vue from "vue"
import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  providers: [],
  isLoadingProviders: false,
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
  sortedProviders(state) {
    return state.providers.sort((a, b) => (a.name.toLowerCase() > b.name.toLowerCase()) ? 1 : -1)
  }
}

const mutations = {
  SET_PROVIDERS: (state, providers) => {
    state.providers = providers
  },
  SET_IS_LOADING_PROVIDERS: (state, loadingState) => {
    state.isLoadingProviders = loadingState
  },
  ADD_PROVIDER: (state, newProvider) => {
    state.providers.push(newProvider)
  },
  UPDATE_PROVIDER: (state, updatedProvider) => {
    let providerToUpdate = {
      alias: updatedProvider.alias,
      punter_percentage: updatedProvider.punter_percentage,
      is_enabled: updatedProvider.is_enabled,
      currency_id: updatedProvider.currency_id,
      updated_at: updatedProvider.updated_at
    }

    state.providers.map(provider => {
      if(provider.name == updatedProvider.name) {
        Object.keys(providerToUpdate).map(key => {
          Vue.set(provider, key, providerToUpdate[key])
        }) 
      }
    })
  }
}

const actions = {
  getProviders({commit, dispatch}, non_primary = false) {
    commit('SET_IS_LOADING_PROVIDERS', true)
    let path = non_primary ? 'providers/non-primary' : 'providers'
    axios.get(path, { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_PROVIDERS', response.data.data)
      commit('SET_IS_LOADING_PROVIDERS', false)
    })
    .catch(err => {
      commit('SET_PROVIDERS', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
  addProvider({commit, dispatch}, provider) {
    return new Promise((resolve, reject) => {
      axios.post('providers/create', provider, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('ADD_PROVIDER', response.data.data)
        resolve(response.data.message)
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  updateProvider({commit, dispatch}, provider) {
    return new Promise((resolve, reject) => {
      axios.post('providers/update', provider, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('UPDATE_PROVIDER', response.data.data)
        resolve(response.data.message)
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  }
}

export default {
  state, getters, mutations, actions, namespaced: true
}