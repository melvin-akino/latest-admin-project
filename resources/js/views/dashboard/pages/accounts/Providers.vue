<template>
  <div class="providers pa-6">
    <v-container>
      <p class="text-h4 text-uppercase">Provider Accounts List</p>
      <div class="mt-6">
        <v-form @submit.prevent="filterProviderAccounts">
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-select
                :items="['All', 'HG', 'ISN', 'PIN', 'SB']"
                label="Bookmaker"
                outlined
                dense
                v-model="search.provider"
                background-color="#fff"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-select
                :items="['All', 'CNY', 'USD']"
                label="Currency"
                outlined
                dense
                v-model="search.currency"
                background-color="#fff"
              ></v-select>
            </v-col>
          </v-row>
          <v-btn type="submit" color="primary" depressed elevation="2" dark height="30">Generate</v-btn>
        </v-form>
      </div>
      <v-toolbar flat color="transparent">
        <v-spacer></v-spacer>
        <button-dialog icon="mdi-plus" label="New Account" width="600">
          <provider-form :update="false"></provider-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="providerAccountsTable"
        :items-per-page="10"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Summary</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.status`]="{ item }">
          <v-select :items="providerStatus" dense v-model="item.status" @change="updateProviderAccountStatus(item.id, item.status)"></v-select>
        </template>
        <template v-slot:[`item.actions`]="{ item }" class="actions">
          <table-action-dialog icon="mdi-pencil" width="600">
            <provider-form :update="true" :provider-account-to-update="item"></provider-form>
          </table-action-dialog>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  name: "Providers",
  components: {
    ButtonDialog: () => import("../../component/ButtonDialog"),
    TableActionDialog: () => import("../../component/TableActionDialog"),
    ProviderForm: () => import("../../components/forms/ProviderForm")
  },
  data: () => ({
    headers: [
      { text: "USERNAME", value: "username" },
      { text: "CREDITS", value: "credits" },
      { text: "P/L", value: "pl" },
      { text: "OPEN ORDERS", value: "open_orders" },
      { text: "TYPE", value: "type" },
      { text: "STATUS", value: "status", width: "15%" },
      { text: "LAST BET", value: "last_bet" },
      { text: "LAST SCRAPE", value: "last_scrape" },
      { text: "LAST SYNC", value: "last_sync" },
      {
        text: "",
        value: "actions",
        width: "10%",
        align: "center",
        sortable: false
      }
    ],
    search: {
      provider: 'All',
      currency: 'All'
    },
    providerAccountsTable: []
  }),
  computed: {
    ...mapState("providers", ["providerAccounts", "providerStatus"]),
  },
  mounted() {
    this.providerAccountsTable = this.providerAccounts
  },
  methods: {
    updateProviderAccountStatus(id, status) {
      this.$store.commit('providers/UPDATE_PROVIDER_ACCOUNT_STATUS', { id, status })
    },
    filterProviderAccounts() {
      let filterParams = [this.search.provider, this.search.currency]
      let filteredProviderAccounts = this.providerAccounts.filter(account => {
        if(filterParams.includes('All')) {
          if(this.search.provider && this.search.provider != 'All') {
            if(account.provider == this.search.provider) {
              return true
            } else {
              return false
            }
          }
          if(this.search.currency && this.search.currency != 'All') {
            if(account.currency == this.search.currency) {
              return true
            } else {
              return false
            }
          }
          return true
        } else {
          return this.search.provider == account.provider && this.search.currency == account.currency
        }
      })
      this.providerAccountsTable = filteredProviderAccounts
    }
  }
};
</script>

<style>
.providers p {
  margin-bottom: 0;
}

.providers .v-toolbar__content {
  padding: 16px;
}

.providers .theme--light.v-label {
  color: #000;
}

.formColumn {
  padding: 0px 10px;
}
</style>
