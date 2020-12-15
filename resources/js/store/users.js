import Vue from "vue"
import { getToken } from '../helpers/token'

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
  ADD_USER: (state, payload) => {
    let id = state.users.length + 1
    Vue.set(payload, 'id', id)
    Vue.set(payload, 'bets', [])
    state.users.unshift(payload);
  },
  UPDATE_USER: (state, payload) => {
    let fieldsToUpdate = ["first_name", "last_name", "status", "currency"];
    state.users.map(user => {
      if (user.id == payload.id) {
        fieldsToUpdate.map(field => {
          Vue.set(user, field, payload[field]);
        });
      }
    });
  },
  UPDATE_USER_STATUS: (state, payload) => {
    state.users.map(user => {
      if (user.id == payload.id) {
        Vue.set(user, "status", payload.status);
      }
    });
  },
  UPDATE_USER_WALLET: (state, payload) => {
    state.users.map(user => {
      if (user.id == payload.id) {
        if (payload.wallet.transactionType == "Deposit") {
          Vue.set(
            user,
            "credits",
            Number(user.credits) + Number(payload.wallet.credits)
          );
        } else {
          Vue.set(
            user,
            "credits",
            Number(user.credits) - Number(payload.wallet.credits)
          );
        }
        Vue.set(user, "currency", payload.wallet.currency);
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
        if(openOrders.length != 0) {
          Vue.set(user, 'open_bets', openOrders[0].open_orders)
        } else {
          Vue.set(user, 'open_bets', '-')
        }
      })
    } catch(err) {
      commit('SET_USERS', [])
      bus.$emit("SHOW_SNACKBAR", {
        color: "error",
        text: err.response.data.message
      });
    }
  }
}

export default {
  state,
  getters,
  mutations,
  actions,
  namespaced: true
};
