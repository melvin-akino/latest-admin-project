import Vue from 'vue'

const state = {
  providerAccounts: [
    {
      id: 1,
      username: 'CSY513',
      credits: '1,000.00',
      pl: '1,000.00',
      open_orders: '0.00',
      type: 'BET NORMAL',
      status: 'Active',
      last_bet: '2020-11-16 12:30:10',
      last_scrape: '2020-11-16 12:30:10',
      last_sync: 'BALANCE',
      provider: 'HG',
      currency: 'CNY',
      punter_percentage: 45,
      idle: 'No'
    },
    {
      id: 2,
      username: 'PSJML',
      credits: '1,000.00',
      pl: '1,000.00',
      open_orders: '0.00',
      type: 'BET VIP',
      status: 'Active',
      last_bet: '2020-11-16 12:30:10',
      last_scrape: '2020-11-16 12:30:10',
      last_sync: 'SETTLEMENT',
      provider: 'ISN',
      currency: 'USD',
      punter_percentage: 45,
      idle: 'No'
    }
  ],
  providerStatus: ['Active', 'Inactive'],
  providerAccountTypes: ['BET NORMAL', 'BET VIP', 'SCRAPER']
}

const mutations = {
  ADD_PROVIDER_ACCOUNT: (state, payload) => {
    let id = state.providerAccounts.length + 1
    Vue.set(payload, 'id', id)
    Vue.set(payload, 'credits', '1,000.00')
    Vue.set(payload, 'pl', '1,000.00')
    Vue.set(payload, 'open_orders', '0.00')
    Vue.set(payload, 'provider', 'HG')
    Vue.set(payload, 'currency', 'CNY')
    state.providerAccounts.unshift(payload)
  },
  UPDATE_PROVIDER_ACCOUNT: (state, payload) => {
    let fieldsToUpdate = ["username", "punter_percentage", "type", "status", "idle"];
    state.providerAccounts.map(account => {
      if (account.id == payload.id) {
        fieldsToUpdate.map(field => {
          Vue.set(account, field, payload[field]);
        });
      }
    });
  },
  UPDATE_PROVIDER_ACCOUNT_STATUS: (state, payload) => {
    state.providerAccounts.map(account => {
      if (account.id == payload.id) {
        Vue.set(account, 'status', payload.status)
      }
    });
  }
}

export default {
  state, mutations, namespaced: true
}
