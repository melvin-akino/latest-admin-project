import Vue from 'vue'
import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  primaryProviderLeagues: [],
  unmatchedData: [],
  isLoadingUnmatchedData: false,
  totalUnmatchedData: 0,
  primaryProviderData: [],
  matchedData: [],
  isLoadingMatchedData: false,
  totalMatchedData: 0,
} 

const mutations = {
  SET_UNMATCHED_DATA: (state, data) => {
    state.unmatchedData = data
  },
  SET_IS_LOADING_UNMATCHED_DATA: (state, loadingState) => {
    state.isLoadingUnmatchedData = loadingState
  },
  SET_TOTAL_UNMATCHED_DATA: (state, total) => {
    state.totalUnmatchedData = total
  },
  SET_MATCHED_DATA: (state, data) => {
    state.matchedData = data
  },
  SET_IS_LOADING_MATCHED_DATA: (state, loadingState) => {
    state.isLoadingMatchedData = loadingState
  },
  SET_TOTAL_MATCHED_DATA: (state, total) => {
    state.totalMatchedData = total
  },
  SET_PRIMARY_PROVIDER_LEAGUES: (state, data) => {
    state.primaryProviderLeagues = data
  }
}

const actions = {
  getUnmatchedLeagues({commit, dispatch}, params) {
    axios.get('leagues/unmatched', { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_UNMATCHED_DATA', response.data.pageData)
      commit('SET_TOTAL_UNMATCHED_DATA', response.data.total)
      commit('SET_IS_LOADING_UNMATCHED_DATA', false)
    })
    .catch(err => {
      commit('SET_UNMATCHED_DATA', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
  getMatchedLeagues({commit, dispatch, state}, params) {
    axios.get('leagues/matched', { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_MATCHED_DATA', response.data.pageData)
      commit('SET_TOTAL_MATCHED_DATA', response.data.total)
      commit('SET_IS_LOADING_MATCHED_DATA', false)
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
  getPrimaryProviderMatchedLeagues({commit, dispatch}) {
    axios.get('leagues/matched/primary', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_PRIMARY_PROVIDER_LEAGUES', response.data.data)
    })
    .catch(err => {
      commit('SET_PRIMARY_PROVIDER_LEAGUES', [])
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
  state, mutations, actions, namespaced: true
}