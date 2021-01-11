<template>
  <div class="walletClients pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Manage Wallet Clients</p>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Clients"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="clients"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingClients"
        loading-text="Loading Wallet Clients"
        :page="page"
        @pagination="getPage"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Clients: {{ clients.length }}</p>
          </v-toolbar>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'


export default {
  name: 'WalletClients',
  data: () => ({
    headers: [
      { text: 'CLIENT ID', value: 'client_id' },
      { text: 'CLIENT SECRET', value: 'client_secret' },
      { text: 'NAME', value: 'name' },
      { text: 'REVOKED', value: 'revoked' },
      { text: 'CREATED DATE', value: 'created_at' },
    ],
    search: '',
    page: null
  }),
  computed:{
    ...mapState('wallet', ['clients', 'isLoadingClients']),
  },
  mounted() {
    this.getClients()
  },
  methods: {
    ...mapMutations('wallet', { setClients: 'SET_CLIENTS' }),
    ...mapActions('wallet', ['getClients']),
    clearFilters() {
      this.search = ''
      this.page = 1
    },
    getPage(pagination) {
      this.page = pagination.page
    }
  },
  beforeRouteLeave(to, from, next) {
    this.setClients([])
    next()
  }
}
</script>

<style>
.walletClients p {
  margin-bottom: 0;
}

.walletClients .v-toolbar__content {
  padding: 16px;
}
</style>