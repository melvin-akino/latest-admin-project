<template>
  <div class="aliases">
    <v-container>
      <v-toolbar flat color="transparent">
        <v-spacer></v-spacer>
        <v-text-field
          v-model="searchKey"
          append-icon="mdi-magnify"
          label="Search"
          hide-details
          dense
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="retries"
        item-key="order_log_id"
        group-by="ml_bet_identifier"
        :server-items-length="totalRetries"
        :options.sync="options"
        :loading="isLoadingRetries"
        loading-text="Loading bet retries"
      >
        <template v-slot:group="{ items, group }">
          <tr class="v-row-group__header">
            <td colspan="8" id="retryHeader" @click="toggle(group)">
              <p class="font-weight-medium">{{group}}</p> 
              <p class="font-italic">{{items.filter(item => item.ml_bet_identifier == group).map(item => item.email)[0]}}</p>
            </td>
          </tr>
          <tr v-for="bet in removeInitialPending(items)" :key="bet.order_log_id" :class="[ hiddenGroups.includes(group) ? 'hidden' : '' ]">
            <td class="text-start">{{bet.order_log_id}}</td>
            <td class="text-start">{{bet.created_at}}</td>
            <td class="text-start">
              <span class="betSelection">{{bet.bet_selection}}</span>
            </td>
            <td class="text-start">{{bet.username}}</td>
            <td class="text-start">{{bet.alias}}</td>
            <td class="text-start">{{bet.status}}</td>
            <td class="text-start">{{bet.reason}}</td>
            <td class="text-start">{{bet.type}}</td>
          </tr>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { getToken } from '../../../../helpers/token'
import { mapActions } from 'vuex'
import bus from '../../../../eventBus'

export default {
  data: () => ({
    headers: [ 
      { text: 'ID', value: 'order_log_id', sortable: false },
      { text: 'Transaction Date and Time', value: 'created_at', sortable: false },
      { text: 'Bet Selection', value: 'bet_selection', sortable: false },
      { text: 'Provider Account', value: 'username', sortable: false },
      { text: 'Provider', value: 'alias', sortable: false },
      { text: 'Status', value: 'status', sortable: false },
      { text: 'Reason', value: 'reason', sortable: false },
      { text: 'Retry Type', value: 'type', sortable: false }
    ],
    retries: [],
    isLoadingRetries: false,
    totalRetries: 0,
    options: {},
    searchKey: '',
    hiddenGroups: []
  }),
  watch: {
    options: {
      deep: true,
      handler(value) {
        let params = {
          page: value.page,
          limit: value.itemsPerPage != -1 ? value.itemsPerPage : this.totalRetries,
          searchKey: value.searchKey
        }
        this.getBetRetries(params)
      }
    },
    searchKey(value) {
      this.$set(this.options, 'searchKey', value)
      this.options.page = 1
    }
  },
  mounted() {
    this.isLoadingRetries = true
  },
  methods: {
    ...mapActions('auth', ['logoutOnError']),
    toggle(group) {
      if(this.hiddenGroups.includes(group)) {
        this.hiddenGroups = this.hiddenGroups.filter(item => item != group)
      } else {
        this.hiddenGroups.push(group)
      }
    },
    removeInitialPending(bets) {
      if(bets.length > 1 && bets[0].status == 'PENDING' && !bets[0].provider_account_id && bets[1].status == 'PENDING') {
        bets.shift()
        return bets
      }
      return bets
    },
    getBetRetries(params) {
      axios.get('orders/bet/retries', { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        this.retries = response.data.pageData
        this.totalRetries = response.data.total
        this.isLoadingRetries = false
      })
      .catch(err => {
        this.logoutOnError(err.response.status)
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      })
    }
  },
  beforeRouteLeave(to, from, next) {
    this.retries = []
    this.totalRetries = 0
    this.isLoadingRetries = true
    next()
  }
}
</script>

<style>
.betSelection {
  white-space: pre-line;
}

#retryHeader {
  cursor: pointer;
  background-color: #EDEDED;
  padding: 8px 16px;
}

#retryHeader p {
  margin: 0;
  padding: 3px;
}

.hidden {
  display: none;
}
</style>
