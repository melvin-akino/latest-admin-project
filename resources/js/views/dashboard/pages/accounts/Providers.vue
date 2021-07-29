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
                class="filter"
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
                class="filter"
              ></v-select>
            </v-col>
          </v-row>
          <v-btn type="submit" color="primary" depressed elevation="2" dark height="30">Apply Filters</v-btn>
        </v-form>
      </div>
      <v-toolbar flat color="transparent">
        <v-spacer></v-spacer>
        <v-text-field
          v-model="searchKey"
          append-icon="mdi-magnify"
          label="Search Accounts"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
        <button-dialog icon="mdi-plus" label="New Account" width="600" @clearFilters="clearFilters">
          <provider-account-form :update="false"></provider-account-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="filteredProviderAccounts"
        :items-per-page="10"
        group-by="line"
        :search="searchKey"
        :loading="isLoadingProviderAccounts"
        loading-text="Loading Provider Accounts"
        :page="page"
        @pagination="getPage"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Accounts: {{ filteredProviderAccounts.length }}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`group.header`]="{ headers, toggle, group }">
          <td :colspan="headers.length" @click="toggle" id="providerGroupHeader">Line: {{group}}</td>
        </template>
        <template v-slot:[`item.credits`]="{ item }">
          <span v-if="!item.hasOwnProperty('credits')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{ item.credits | moneyFormat }}</span>    
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
          <span v-else>{{ item.pl | moneyFormat }}</span>    
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
          <span v-else>{{ item.open_orders | moneyFormat }}</span>    
        </template>
        <template v-slot:[`item.status`]="{ item }">
          <v-select :items="providerAccountStatus" dense v-model="item.is_enabled" @change="updateProviderAccount(item)"></v-select>
        </template>
        <template v-slot:[`item.usage`]="{ item }">
          <v-select :items="providerAccountUsages" dense v-model="item.usage" @change="updateProviderAccount(item)"></v-select>
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
          <table-action-dialog icon="mdi-pencil" width="600" tooltipText="Edit" style="z-index:1;">
            <provider-account-form :update="true" :provider-account-to-update="item"></provider-account-form>
          </table-action-dialog>
          <v-menu style="z-index:2;" offset-y>
            <template v-slot:activator="{ on: menu, attrs }">
              <v-tooltip bottom>
                <template v-slot:activator="{ on: tooltip }">
                  <v-btn icon v-bind="attrs" v-on="{ ...menu, ...tooltip }">
                    <v-icon small>mdi-format-list-bulleted</v-icon>
                  </v-btn>
                </template>
                <span class="caption">Transactions</span>
              </v-tooltip>
            </template>
            <v-list>
              <v-list-item :to="`providers/transactions/${item.id}`" target="_blank">
                <v-list-item-title class="caption">Provider Transactions</v-list-item-title>
              </v-list-item>
              <v-list-item :to="`wallet/transactions/provider-account/${item.uuid}`" target="_blank">
                <v-list-item-title class="caption">Wallet Transactions</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapGetters, mapMutations, mapActions } from "vuex";
import bus from '../../../../eventBus'
import { moneyFormat } from '../../../../helpers/numberFormat'

export default {
  name: "ProviderAccounts",
  components: {
    ButtonDialog: () => import("../../component/ButtonDialog"),
    TableActionDialog: () => import("../../component/TableActionDialog"),
    ProviderAccountForm: () => import("../../components/forms/ProviderAccountForm")
  },
  data: () => ({
    headers: [
      { text: "PROVIDER", value: "provider" },
      { text: "USERNAME", value: "username" },
      { text: "CREDITS", value: "credits" },
      { text: "P/L", value: "pl" },
      { text: "OPEN ORDERS", value: "open_orders" },
      { text: "TYPE", value: "type" },
      { text: "STATUS", value: "status", width: "15%" },
      { text: "USAGE", value: "usage", width: "15%" },
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
    page: null,
    searchKey: null
  }),
  computed: {
    ...mapState("providerAccounts", ["providerAccounts", "filteredProviderAccounts", "isLoadingProviderAccounts", "providerAccountStatus", "providerAccountUsages"]),
    ...mapState("providers", ["providers"]),
    ...mapGetters("providers", ["providerFilters"]),
    ...mapGetters("currencies", ["currencyFilters"])
  },
  mounted() {
    this.getProviders()
    this.getCurrencies()
    this.getProviderAccountsList()
  },
  methods: {
    ...mapMutations("providerAccounts", { setProviderAccounts: "SET_PROVIDER_ACCOUNTS", setFilteredProviderAccounts: "SET_FILTERED_PROVIDER_ACCOUNTS" }),
    ...mapActions("providerAccounts", ["getProviderAccountsList", "manageProviderAccount"]),
    ...mapActions('providers', ['getProviders']),
    ...mapActions('currencies', ['getCurrencies']),
    async updateProviderAccount(providerAccount) {
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Updating provider account status..."
        });
        await this.manageProviderAccount(providerAccount)
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Provider status updated."
        });
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.hasOwnProperty('errors') ?  err.response.data.errors[Object.keys(err.response.data.errors)[0]][0] : err.response.data.message
        });
      }
    },
    setCurrencyFilter(provider_id) {
      let provider = this.providers.filter(provider => provider.id == provider_id)
      if(provider.length != 0) {
        this.search.currency = provider[0].currency_id
      }
    },
    filterProviderAccounts() {
      this.page = 1
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
      this.page = 1
      this.setFilteredProviderAccounts(this.providerAccounts)
    },
    getPage(pagination) {
      this.page = pagination.page
    },
  },
  filters: {
    moneyFormat
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

.filter .theme--light.v-label {
  color: #000;
}

.formColumn {
  padding: 0px 10px;
}

#providerGroupHeader {
  cursor: pointer;
  background-color: #EDEDED;
}
</style>
