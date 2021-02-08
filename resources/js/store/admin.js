import Vue from "vue"
import { getToken } from '../helpers/token'
import bus from '../eventBus'

const state = {
  adminUsers: [],
  isLoadingAdminUsers: false,
  adminRoles:  [
    {
      text: 'Admin',
      value: 'admin'
    },
    {
      text: 'Accounting',
      value: 'accounting'
    },
    {
      text: 'Support',
      value: 'support'
    }
  ],
  adminStatus: [
    {
      text: 'Active',
      value: 1
    },
    {
      text: 'Suspended',
      value: 0
    }
  ],
  rolesSettings: {
    admin: {
      accounts: { create: true, modify: true, view: true, delete: true  },
      provider_accounts: { create: true, modify: true, view: true, delete: true  },
      admin: { create: true, modify: true, view: true, delete: true  }
    },
    accounting: {
      accounts: { create: true, modify: true, view: true, delete: false  },
      provider_accounts: { create: true, modify: true, view: true, delete: false  },
      admin: { create: false, modify: false, view: true, delete: false  }
    },
    support: {
      accounts: { create: true, modify: true, view: true, delete: false  },
      provider_accounts: { create: true, modify: true, view: true, delete: false  },
      admin: { create: true, modify: true, view: true, delete: false  }
    }
  }
}

const mutations = {
  SET_ADMIN_USERS: (state, adminUsers) => {
    state.adminUsers = adminUsers
  },
  SET_IS_LOADING_ADMIN_USERS: (state, loadingState) => {
    state.isLoadingAdminUsers = loadingState
  },
  ADD_ADMIN_USER: (state, adminUser) => {
    let newAdminUser = {
      id: adminUser.id,
      name: adminUser.name, 
      email: adminUser.email, 
      status: adminUser.status,
      created_at: adminUser.created_at
    }
    state.adminUsers.unshift(newAdminUser)
  },
  UPDATE_ADMIN_USER: (state, adminUser) => {
    let updatedAdminUser = {
      name: adminUser.name,
      status: adminUser.status 
    }
    state.adminUsers.map(admin => {
      if(admin.id == adminUser.id) {
        Object.keys(updatedAdminUser).map(key => {
          Vue.set(admin, key, updatedAdminUser[key])
        })
      }
    })
  },
  UPDATE_ADMIN_ROLE: (state, payload) => {
    state.admin.map(admin => {
      if(admin.id == payload.id) {
        Vue.set(admin, 'role', payload.role)
      }
    })
  },
  ADD_NEW_ROLE: (state, payload) => {
    let text = payload.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.substring(1)).join(' ')
    let value = payload.toLowerCase().split(' ').join('_')
    state.adminRoles.push({ text, value })
    let defaultRoleSettings = {
      accounts: { create: false, modify: false, view: true, delete: false  },
      provider_accounts: { create: false, modify: false, view: true, delete: false  },
      admin: { create: false, modify: false, view: true, delete: false  }
    }
    Vue.set(state.rolesSettings, value, defaultRoleSettings)
  },
  UPDATE_ROLE: (state, payload) => {
    let text = payload.updatedRole.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.substring(1)).join(' ')
    let value = payload.updatedRole.toLowerCase().split(' ').join('_')
    let rolesSettings = state.rolesSettings[payload.role.value]
    if(payload.role.value != value) {
      Vue.set(state.rolesSettings, value, rolesSettings)
      Vue.delete(state.rolesSettings, payload.role.value)
      state.adminRoles.map(role => {
        if(role.value == payload.role.value) {
          Vue.set(role, 'text', text)
          Vue.set(role, 'value', value)
        }
      })
    }
  },
  UPDATE_ROLE_ACTION: (state, payload) => {
    Vue.set(state.rolesSettings[payload.role][payload.feature], payload.action, payload.value)
  }
}

const actions = {
  getAdminUsers({commit, dispatch}) {
    commit('SET_IS_LOADING_ADMIN_USERS', true)
    axios.get('admin-users', { headers: { 'Authorization': `Bearer ${getToken()}` } })
    .then(response => {
      commit('SET_ADMIN_USERS', response.data)
      commit('SET_IS_LOADING_ADMIN_USERS', false)
    })
    .catch(err => {
      commit('SET_ADMIN_USERS', [])
      if(!axios.isCancel(err)) {
        dispatch('auth/logoutOnError', err.response.status, { root: true })
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    })
  },
  manageAdminUser({commit, dispatch}, payload) {
    return new Promise((resolve, reject) => {
      axios.post('admin-users/manage', payload, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        if(payload.id) {
          commit('UPDATE_ADMIN_USER', response.data.data)
        } else {
          commit('ADD_ADMIN_USER', response.data.data)
        }
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
  state,
  mutations,
  actions,
  namespaced: true
}
