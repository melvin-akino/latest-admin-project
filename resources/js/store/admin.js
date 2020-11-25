import Vue from "vue"

const state = {
  admin: [
    {
      id: 1,
      email: 'admin1@npt.com',
      password: '123123',
      first_name: 'Admin',
      last_name: 'User',
      role: 'admin',
      status: 'Active',
      created_date: '2020-11-09 08:00:00',
      last_access_date: '2020-11-09 08:00:00',
      activity_logs: [
        {
          module:'Account',
          action: 'Add',
          description: 'Registered giorodriguez021@gmail.com',
          ip_address: '10.10.10.10',
          created_date: '2020-11-09 08:00:00'
        },
        {
          module:'Providers',
          action: 'Edit',
          description: 'Edited provider888 (status: active to inactive)',
          ip_address: '10.10.10.10',
          created_date: '2020-11-16 08:00:00'
        },
      ]
    },
    {
      id: 2,
      email: 'acct1@npt.com',
      password: '123123',
      first_name: 'Accounting',
      last_name: 'User',
      role: 'accounting',
      status: 'Active',
      created_date: '2020-11-09 08:00:00',
      last_access_date: '2020-11-09 08:00:00',
      activity_logs: [
        {
          module:'Account',
          action: 'Add',
          description: 'Registered kawow@nba.com',
          ip_address: '69.69.69.69',
          created_date: '2020-11-11 08:00:00'
        },
        {
          module:'Account',
          action: 'Edit',
          description: 'Edited user id: 2 (status: active to view only)',
          ip_address: '69.69.69.69',
          created_date: '2020-11-16 09:00:00'
        }
      ]
    }
  ],
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
  adminStatus: ['Active', 'Suspended'],
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
  ADD_ADMIN: (state, payload) => {
    let id = state.admin.length + 1
    Vue.set(payload, 'id', id)
    Vue.set(payload, 'activity_logs', [])
    state.admin.unshift(payload)
  },
  UPDATE_ADMIN: (state, payload) => {
    let fieldsToUpdate = ['first_name', 'last_name', 'role', 'status']
    state.admin.map(admin => {
      if(admin.id == payload.id) {
        fieldsToUpdate.map(field => {
          Vue.set(admin, field, payload[field])
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
  UPDATE_ADMIN_STATUS: (state, payload) => {
    state.admin.map(admin => {
      if(admin.id == payload.id) {
        Vue.set(admin, 'status', payload.status)
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

export default {
  state,
  mutations,
  namespaced: true
}
