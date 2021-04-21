import Vue from 'vue'
import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  matchingFilters: {
    leagues: {
      matched: null,
      unmatched: null
    },
    events: {
      league: null,
      leagueId: null,
      masterLeagueId: null,
      schedule: null,
      inplay: null,
      today: null,
      early: null
    }
  },
  primaryProviderLeagues: [],
  unmatchedData: [],
  isLoadingUnmatchedData: false,
  totalUnmatchedData: 0,
  primaryProviderData: [],
  isLoadingPrimaryProviderData: false,
  totalPrimaryProviderData: 0,
  matchedData: [],
  isLoadingMatchedData: false,
  totalMatchedData: 0,
  primaryProviderId: null,
  matchId: null
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
  },
  SET_FILTER: (state, data) => {
    Vue.set(state.matchingFilters[data.type], data.filter, data.data)
  },
  SET_UNMATCHED_EVENTS_FOR_LEAGUE: (state, data) => {
    state.unmatchedData.map(league => {
      if(league.id == data.leagueId) {
        Vue.set(league, 'events', data.data)
      }
    })
  },
  REMOVE_UNMATCHED_EVENTS_FOR_LEAGUE: (state, leagueId) => {
    state.unmatchedData.map(league => {
      if(league.id == leagueId) {
        Vue.delete(league, 'events')
      }
    })
  },
  SET_PRIMARY_PROVIDER_DATA: (state, data) => {
    state.primaryProviderData = data
  },
  SET_IS_LOADING_PRIMARY_PROVIDER_DATA: (state, loadingState) => {
    state.isLoadingPrimaryProviderData = loadingState
  },
  SET_TOTAL_PRIMARY_PROVIDER_DATA: (state, total) => {
    state.totalPrimaryProviderData = total
  },
  SET_PRIMARY_PROVIDER_ID: (state, id) => {
    state.primaryProviderId = id
  },
  SET_MATCH_ID: (state, id) => {
    state.matchId = id
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
  getMatchedLeagues({commit, dispatch}, params) {
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
  },
  getUnmatchedEventsByLeague({commit, dispatch}, data) {
    let { leagueId, paginated } = data
    if(leagueId) {
      axios.get(`events/unmatched/league/${leagueId}`, { params: { paginated }, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('SET_UNMATCHED_EVENTS_FOR_LEAGUE', { leagueId, data: response.data.pageData })
      })
      .catch(err => {
        if(!axios.isCancel(err)) {
          dispatch('auth/logoutOnError', err.response.status, { root: true })
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.message
          });
        }
      })
    }
  },
  getUnmatchedEventsByMasterLeague({commit, dispatch, state}, params) {
    let masterLeagueId = state.matchingFilters.events.masterLeagueId
    if(masterLeagueId) {
      axios.get(`events/unmatched/master-league/${masterLeagueId}`, { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
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
    }
  },
  getPrimaryProviderEventsByLeague({commit, dispatch, state}, params) {
    let leagueId = state.matchingFilters.events.leagueId || params.leagueId
    if(leagueId) {
      axios.get(`events/matched/league/${leagueId}`, { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('SET_PRIMARY_PROVIDER_DATA', response.data.pageData)
        commit('SET_TOTAL_PRIMARY_PROVIDER_DATA', response.data.total)
        commit('SET_IS_LOADING_PRIMARY_PROVIDER_DATA', false)
      })
      .catch(err => {
        commit('SET_PRIMARY_PROVIDER_DATA', [])
        if(!axios.isCancel(err)) {
          dispatch('auth/logoutOnError', err.response.status, { root: true })
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.message
          });
        }
      })
    }
  },
  matchLeague({dispatch, state}) {
    return new Promise((resolve, reject) => {
      let payload = {
        primary_provider_league_id: state.primaryProviderId,
        match_league_id: state.matchId
      }
      axios.post('leagues/match', payload , { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(() => {
        dispatch('getUnmatchedLeagues')
        dispatch('getMatchedLeagues')
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