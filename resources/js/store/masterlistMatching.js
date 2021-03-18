import Vue from 'vue'
import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  rawData: [],
  isLoadingRawData: false,
  totalRawData: 0,
  matchedData: [],
  options: {
    type: '',
    providerId: null,
    provider_alias: '',
    page: null, 
    limit: null
  }
} 

const mutations = {
  SET_RAW_DATA: (state, data) => {
    state.rawData = data
  },
  SET_IS_LOADING_RAW_DATA: (state, loadingState) => {
    state.isLoadingRawData = loadingState
  },
  SET_TOTAL_RAW_DATA: (state, total) => {
    state.totalRawData = total
  },
  SET_OPTIONS: (state, data) => {
    Vue.set(state.options, data.option, data.data)
  },
  REMOVE_OPTIONS: (state, key) => {
    Vue.delete(state.options, key)
  },
  SET_MATCHED_DATA: (state, data) => {
    state.matchedData = data
  },
  REMOVE_MATCHED_DATA: (state, id) => {
    state.rawData = state.rawData.filter(data => data.id != id)
  }
}

const actions = {
  getRawData({commit, dispatch, state}) {
    axios.get(`raw-${state.options.type}`, { params: state.options, headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_RAW_DATA', response.data.pageData)
      commit('SET_TOTAL_RAW_DATA', response.data.total)
      commit('SET_IS_LOADING_RAW_DATA', false)
    })
    .catch(err => {
      commit('SET_RAW_DATA', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
  getMatchedData({commit, dispatch, state}) {
    commit('SET_MATCHED_DATA', [])
    axios.get(`matched-${state.options.type}`, { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_MATCHED_DATA', response.data)
    })
    .catch(err => {
      commit('SET_MATCHED_DATA', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
  matchData({commit, dispatch}, payload) {
    return new Promise((resolve, reject) => {
      axios.post(`${payload.type}/match`, payload.data, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        dispatch('getRawData')
        resolve()
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