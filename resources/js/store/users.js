import Vue from "vue"
import { getToken, getWalletToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  users: [],
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

const getters = {
  usersTable(state) {
    let usersTable = []
    state.users.map(user => {
      let full_name = `${user.firstname} ${user.lastname}`
      let userObject = { ...user }
      Vue.set(userObject, 'full_name', full_name)
      usersTable.push(userObject)
    })
    return usersTable
  }
}

const mutations = {
  SET_USERS: (state, users) => {
    state.users = users
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
      currency_id: user.currency_id,
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
  getUsers({dispatch}) {
    return new Promise((resolve, reject) => {
      axios.get('users', { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        resolve(response.data)
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  getUserOpenOrders({dispatch}, user_id) {
    return new Promise((resolve, reject) => {
      axios.get('orders/open', { params: { user_id }, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        resolve(response.data)
      })
      .catch(err => {
        reject(err)
        dispatch('auth/logoutOnError', err.response.status, { root: true })
      })
    })
  },
  async getUsersList({state, commit, dispatch}) {
    try {
      commit('SET_IS_LOADING_USERS', true)
      let users = await dispatch('getUsers')
      commit('SET_USERS', users)
      commit('SET_IS_LOADING_USERS', false)
      state.users.map(async user => {
        let openOrders = await dispatch('getUserOpenOrders', user.id)
        let wallet = await dispatch('wallet/getWalletBalance', { uuid: user.uuid, currency: user.currency_code, wallet_token: getWalletToken() }, { root: true })
        Vue.set(user, 'open_bets', openOrders.open_orders)
        Vue.set(user, 'last_bet', openOrders.last_bet)
        Vue.set(user, 'credits', wallet.balance)
        Vue.set(user, 'currency', wallet.currency)
      })
    } catch(err) {
      commit('SET_USERS', [])
      bus.$emit("SHOW_SNACKBAR", {
        color: "error",
        text: err.response.data.message
      });
    }
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
  getters,
  mutations,
  actions,
  namespaced: true
};
