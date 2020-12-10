<template>
  <div class="providers pa-6">
    <v-container>
      <p class="text-h4 text-uppercase">Provider Accounts List</p>
      <div class="mt-6">
        <v-form @submit.prevent="filterProviderAccounts">
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-select
                :items="providerFilters"
                label="Bookmaker"
                outlined
                dense
                v-model="search.provider"
                background-color="#fff"
                @change="setCurrencyFilter(search.provider)"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-select
                :items="currencyFilters"
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
        <button-dialog icon="mdi-plus" label="New Account" width="600" @clearFilters="clearFilters">
          <provider-form :update="false"></provider-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="filteredProviderAccounts"
        :items-per-page="10"
        :loading="isLoadingProviderAccounts"
        loading-text="Loading Provider Accounts"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Summary</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.pl`]="{ item }">
          <span v-if="!item.hasOwnProperty('pl')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{item.pl}}</span>    
        </template>
        <template v-slot:[`item.open_orders`]="{ item }">
          <span v-if="!item.hasOwnProperty('open_orders')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{item.open_orders}}</span>    
        </template>
        <template v-slot:[`item.status`]="{ item }">
          <v-select :items="providerStatus" dense v-model="item.is_enabled" @change="updateProviderAccountStatus(item)"></v-select>
        </template>
        <template v-slot:[`item.last_bet`]="{ item }">
          <span v-if="!item.hasOwnProperty('last_bet')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{item.last_bet}}</span>    
        </template>
        <template v-slot:[`item.last_scrape`]="{ item }">
          <span v-if="!item.hasOwnProperty('last_scrape')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{item.last_scrape}}</span>    
        </template>
        <template v-slot:[`item.last_sync`]="{ item }">
          <span v-if="!item.hasOwnProperty('last_sync')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{item.last_sync}}</span>    
        </template>
        <template v-slot:[`item.actions`]="{ item }" class="actions">
          <table-action-dialog icon="mdi-pencil" width="600" @clearFilters="clearFilters">
            <provider-form :update="true" :provider-account-to-update="item"></provider-form>
          </table-action-dialog>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapGetters, mapMutations, mapActions } from "vuex";
import bus from '../../../../eventBus'

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
      provider: 'all',
      currency: 'all'
    },
    providerAccountsTable: []
  }),
  computed: {
    ...mapState("providers", ["providerAccounts", "filteredProviderAccounts", "isLoadingProviderAccounts", "providerStatus"]),
    ...mapState("resources", ["providers"]),
    ...mapGetters("resources", ["providerFilters", "currencyFilters"])
  },
  mounted() {
    this.getProviders()
    this.getCurrencies()
    this.getProviderAccountsList()
  },
  methods: {
    ...mapMutations("providers", { setProviderAccounts: "SET_PROVIDER_ACCOUNTS", setFilteredProviderAccounts: "SET_FILTERED_PROVIDER_ACCOUNTS" }),
    ...mapActions("providers", ["getProviderAccountsList", "manageProviderAccount"]),
    ...mapActions("resources", ["getProviders", "getCurrencies"]),
    async updateProviderAccountStatus(providerAccount) {
      bus.$emit("SHOW_SNACKBAR", {
        color: "success",
        text: "Updating provider account status..."
      });
      await this.manageProviderAccount(providerAccount)
      bus.$emit("SHOW_SNACKBAR", {
        color: "success",
        text: "Provider status updated."
      });
    },
    setCurrencyFilter(provider_id) {
      let provider = this.providers.filter(provider => provider.id == provider_id)
      if(provider.length != 0) {
        this.search.currency = provider[0].currency_id
      }
    },
    filterProviderAccounts() {
      let filterParams = [this.search.provider, this.search.currency]
      let filteredProviderAccounts = this.providerAccounts.filter(account => {
        if(filterParams.includes('all')) {
          if(this.search.provider && this.search.provider != 'all') {
            if(account.provider_id == this.search.provider) {
              return true
            } else {
              return false
            }
          }
          if(this.search.currency && this.search.currency != 'all') {
            if(account.currency_id == this.search.currency) {
              return true
            } else {
              return false
            }
          }
          return true
        } else {
          return this.search.provider == account.provider_id && this.search.currency == account.currency_id
        }
      })
      this.setFilteredProviderAccounts(filteredProviderAccounts)
    },
    clearFilters() {
      this.search.provider = 'all'
      this.search.currency = 'all'
      this.setFilteredProviderAccounts(this.providerAccounts)
    }
  },
  beforeRouteLeave(to, from, next) {
    this.setProviderAccounts([])
    this.setFilteredProviderAccounts([])
    next()
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
