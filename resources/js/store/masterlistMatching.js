import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  rawData: [],
  isLoadingRawData: false,
  matchedData: []
} 

const mutations = {
  SET_RAW_DATA: (state, data) => {
    state.rawData = data
  },
  SET_IS_LOADING_RAW_DATA: (state, loadingState) => {
    state.isLoadingRawData = loadingState
  },
  SET_MATCHED_DATA: (state, data) => {
    state.matchedData = data
  },
  REMOVE_MATCHED_DATA: (state, index) => {
    state.rawData.splice(index, 1)
  }
}

const actions = {
  getRawData({commit, dispatch}, payload) {
    commit('SET_IS_LOADING_RAW_DATA', true)
    commit('SET_RAW_DATA', [])
    axios.get(`raw-${payload.type}/${payload.provider_id}`, { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_RAW_DATA', response.data)
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
  getMatchedData({commit, dispatch}, type) {
    commit('SET_MATCHED_DATA', [])
    axios.get(`matched-${type}`, { headers: { 'Authorization': `Bearer ${getToken()}` } })
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
        commit('REMOVE_MATCHED_DATA', payload.index)
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