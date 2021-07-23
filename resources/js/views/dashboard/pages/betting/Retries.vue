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
        <template v-slot:[`group.header`]="{ headers, toggle, group, items }">
          <td :colspan="headers.length" @click="toggle" id="retryHeader">
            <p class="font-weight-medium">{{group}}</p>
            <p class="font-italic">{{items.filter(item => item.ml_bet_identifier == group).map(item => item.email)[0]}}</p>
          </td>
        </template>
        <template v-slot:[`item.bet_selection`]="{ item }">
          <span class="betSelection">{{item.bet_selection}}</span>
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
    searchKey: ''
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
</style>
