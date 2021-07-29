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
  matchId: null,
  unmatchingData: null,
  unmatchedDataParams: {},
  primaryProviderDataParams: {},
  matchedDataParams: {}
} 

const getters = {
  gameScheduleParams(state) {
    let gameSchedule = []
    let { inplay, today, early } = state.matchingFilters.events
    if(inplay) gameSchedule.push('inplay')
    if(today) gameSchedule.push('today')
    if(early) gameSchedule.push('early')
    return gameSchedule
  }
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
  },
  SET_TABLE_PARAMS: (state, data) => {
    state[data.type] = data.data
  },
  SET_UNMATCHING_DATA: (state, data) => {
    state.unmatchingData = data
  },
  SET_LEAGUE_ALIAS: (state, data) => {
    state.matchedData.map(item => {
      if(item.master_league_id == data.master_league_id) {
        Vue.set(item, 'alias', data.alias)
      }
    })
  },
  SET_TEAM_ALIAS: (state, data) => {
    state.matchedData.map(item => {
      if(item.master_team_id == data.master_team_id) {
        Vue.set(item, 'alias', data.alias)
      }
    })
  }
}

const actions = {
  getUnmatchedLeagues({commit, dispatch, state}) {
    let params = state.unmatchedDataParams
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
  getMatchedLeagues({commit, dispatch, state}) {
    let params = state.matchedDataParams
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
  getUnmatchedEventsByMasterLeague({commit, dispatch, state, getters}) {
    let masterLeagueId = state.matchingFilters.events.masterLeagueId
    let params = state.unmatchedDataParams
    Vue.set(params, 'gameSchedule', getters.gameScheduleParams)
    let apiUrl = masterLeagueId ? `events/unmatched/master-league/${masterLeagueId}` : `events/unmatched/master-league`
    axios.get(apiUrl, { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
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
  getPrimaryProviderEventsByLeague({commit, dispatch, state, getters}, data) {
    let { leagueId, type } = data
    let params = state.primaryProviderDataParams
    let apiUrl = leagueId ? `events/matched/league/${leagueId}` : `events/matched/league`
    let fetchData = type == 'leagues' && !leagueId ? false : true
    let paginated = type == 'leagues' ? false : true
    Vue.set(params, 'gameSchedule', getters.gameScheduleParams)  
    Vue.set(params, 'paginated', paginated)  
    if(fetchData) {
      axios.get(apiUrl, { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
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
  getMatchedEvents({commit, dispatch, state}) {
    let params = state.matchedDataParams
    axios.get('events/matched', { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
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
  getMatchedTeams({commit, dispatch, state}) {
    let params = state.matchedDataParams
    axios.get('teams/matched', { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
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
  },
  matchEvent({dispatch, state}) {
    return new Promise((resolve, reject) => {
      let payload = {
        primary_provider_event_id: state.primaryProviderId,
        match_event_id: state.matchId
      }
      axios.post('events/match', payload , { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(() => {
        dispatch('getUnmatchedEventsByMasterLeague')
        dispatch('getPrimaryProviderEventsByLeague', { leagueId: state.matchingFilters.events.leagueId, type: 'events' })
        dispatch('getMatchedEvents')
        resolve()
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  unmatchLeague({dispatch, state}) {
    return new Promise((resolve, reject) => {
      let payload = {
        league_id: state.unmatchingData.data_id,
        provider_id: state.unmatchingData.provider_id,
        sport_id: state.unmatchingData.sport_id
      }
      axios.post('leagues/unmatch', payload , { headers: { 'Authorization': `Bearer ${getToken()}` } })
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
  },
  unmatchEvent({dispatch, state}) {
    return new Promise((resolve, reject) => {
      let payload = {
        event_id: state.unmatchingData.data_id,
        provider_id: state.unmatchingData.provider_id,
        sport_id: state.unmatchingData.sport_id
      }
      axios.post('events/unmatch', payload , { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(() => {
        dispatch('getUnmatchedEventsByMasterLeague')
        dispatch('getPrimaryProviderEventsByLeague', { leagueId: state.matchingFilters.events.leagueId, type: 'events' })
        dispatch('getMatchedEvents')
        resolve()
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  setMasterLeaguePriority({dispatch}, params) {
    return new Promise((resolve, reject) => {
      let payload = {
        master_league_id: params.masterLeagueId,
        is_priority: params.isPriority
      }
      axios.post('leagues/toggle-priority', payload , { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(() => {
        dispatch('getUnmatchedLeagues')
        dispatch('getPrimaryProviderMatchedLeagues')
        dispatch('getMatchedLeagues')
        resolve()
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  updateLeagueAlias({commit, dispatch}, params) {
    return new Promise((resolve, reject) => {
      let payload = {
        master_league_id: params.id,
        alias: params.alias
      }
      axios.post('leagues/alias', payload , { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('SET_LEAGUE_ALIAS', payload)
        resolve(response.data.message)
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  updateTeamAlias({commit, dispatch}, params) {
    return new Promise((resolve, reject) => {
      let payload = {
        master_team_id: params.id,
        alias: params.alias
      }
      axios.post('teams/alias', payload , { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('SET_TEAM_ALIAS', payload)
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