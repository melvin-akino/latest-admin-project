import Vue from "vue"
import { getToken, getWalletToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  users: [],
  totalUsers: 0,
  isLoadingUsers: false,
  userStatus: [
    {
      text: 'Active',
      value: 1
    },
    {
      text: 'Suspended',
      value: 0
    },
    {
      text: 'View Only',
      value: 2
    },
  ]
};

const mutations = {
  SET_USERS: (state, users) => {
    state.users = users
  },
  SET_TOTAL_USERS: (state, totalUsers) => {
    state.totalUsers = totalUsers
  },
  SET_IS_LOADING_USERS: (state, loadingState) => {
    state.isLoadingUsers = loadingState
  },
  ADD_USER: (state, user) => {
    let newUser = {
      id: user.id,
      email: user.email,
      firstname: user.firstname,
      lastname: user.lastname,
      credits: user.credits,
      currency: user.currency,
      status: user.status,
      uuid: user.uuid,
      created_at: user.created_at,
      open_bets: '-',
      last_bet: '-'
    }
    state.users.unshift(newUser)
  },
  UPDATE_USER: (state, user) => {
    let updatedUser = {
      firstname: user.firstname,
      lastname: user.lastname,
      status: user.status
    }
    state.users.map(account => {
      if (account.id == user.id) {
        Object.keys(updatedUser).map(key => {
          Vue.set(account, key, updatedUser[key]);
        });
      }
    });
  },
  UPDATE_USER_WALLET: (state, payload) => {
    state.users.map(user => {
      if (user.uuid == payload.uuid) {
        if (payload.transactionType == "Deposit") {
          Vue.set(user, "credits", Number(user.credits) + Number(payload.amount));
        } else {
          Vue.set(user, "credits", Number(user.credits) - Number(payload.amount));
        }
        Vue.set(user, "currency", payload.currency);
      }
    });
  }
};

const actions = {
  getUsers({commit, dispatch}, { options, search }) {
    commit('SET_IS_LOADING_USERS', true)
    axios.get('users', { params: { page: options.page, limit: options.itemsPerPage, sortBy: options.sortBy[0], sort: options.sortDesc[0] ? 'DESC' : 'ASC', search: search, wallet_token: getWalletToken() }, headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_IS_LOADING_USERS', false)
      commit('SET_USERS', response.data.data)
      commit('SET_TOTAL_USERS', response.data.total)
    })
    .catch(err => {
      commit('SET_USERS', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
  manageUser({commit, dispatch}, payload) {
    return new Promise((resolve, reject) => {
      axios.post('users/manage', payload, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        if(payload.id) {
          commit('UPDATE_USER', response.data.data)
        } else {
          commit('ADD_USER', response.data.data)
        }
        resolve()
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  updateWallet({commit, dispatch}, payload) {
    return new Promise((resolve, reject) => {
      axios.post('wallet/update', payload, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        commit('UPDATE_USER_WALLET', payload)
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
  state,
  mutations,
  actions,
  namespaced: true
};
