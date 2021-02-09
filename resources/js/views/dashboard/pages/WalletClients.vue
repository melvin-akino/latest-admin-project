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
        <button-dialog icon="mdi-plus" label="New Client" width="600" @clearFilters="clearFilters">
          <wallet-client-form></wallet-client-form>
        </button-dialog>
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
        <template v-slot:[`item.revoked`]="{ item }">
          <v-checkbox v-model="item.revoked" :disabled="item.revoked" readonly :class="[ !item.revoked ? `revokeCheckBox-${item.client_id}` : '']"></v-checkbox>
          <confirm-dialog
            v-if="!item.revoked"
            title="Confirm Revoke Client" 
            message="Are you sure you want to revoke this client? This action cannot be undone." 
            :activator="`.revokeCheckBox-${item.client_id}`"
            @confirm="revokeWalletClient(item)"
            >
          </confirm-dialog>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import bus from '../../../eventBus'
import { getWalletToken } from '../../../helpers/token'
import { handleAPIErrors } from '../../../helpers/errors'

export default {
  name: 'WalletClients',
  components: {
    ButtonDialog: () => import("../component/ButtonDialog"),
    ConfirmDialog: () => import("../component/ConfirmDialog"),
    WalletClientForm: () => import("../components/forms/WalletClientForm")
  },
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
    ...mapActions('wallet', ['getClients', 'revokeClient']),
    clearFilters() {
      this.search = ''
      this.page = 1
    },
    getPage(pagination) {
      this.page = pagination.page
    },
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    async revokeWalletClient(wallet) {
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Revoking wallet client..."
        });
        this.$set(wallet, 'wallet_token', getWalletToken())
        let response = await this.revokeClient(wallet)
        this.closeDialog()
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: response
        });
      } catch(err) {        
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: handleAPIErrors(err)
        });
      }
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