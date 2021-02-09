<template>
  <div class="currencies pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Manage Currencies</p>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Currencies"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
        <button-dialog icon="mdi-plus" label="New Currency" width="600" @clearFilters="clearFilters">
          <currency-form></currency-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="currencies"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingCurrencies"
        loading-text="Loading Currencies"
        :page="page"
        @pagination="getPage"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Currencies: {{ currencies.length }}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.is_enabled`]="{ item }">
          <v-checkbox v-model="item.is_enabled" readonly :class="[`currencyCheckbox-${item.name}`]"></v-checkbox>
          <confirm-dialog
            :title="`Confirm ${ item.is_enabled ? 'Disable' : 'Enable'} Currency`"
            :message="`Are you sure you want to ${ item.is_enabled ? 'disable' : 'enable'} this currency? ${ item.is_enabled ? `Disabling it may cause issues for users using ${item.name} currency.` : ''}`"
            :activator="`.currencyCheckbox-${item.name}`"
            @confirm="updateWalletCurrency(item)"
          >
          </confirm-dialog>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import { getWalletToken } from '../../../helpers/token'
import { handleAPIErrors } from '../../../helpers/errors'
import bus from '../../../eventBus'

export default {
  name: 'Currencies',
  components: {
    ButtonDialog: () => import("../component/ButtonDialog"),
    ConfirmDialog: () => import("../component/ConfirmDialog"),
    CurrencyForm: () => import("../components/forms/CurrencyForm")
  },
  data: () => ({
    headers: [
      { text: 'NAME', value: 'name', width: '30%' },
      { text: 'ENABLED', value: 'is_enabled', width: '20%' },
      { text: 'CREATED DATE', value: 'created_at', width: '30%' },
    ],
    search: '',
    page: null
  }),
  computed:{
    ...mapState('wallet', ['currencies', 'isLoadingCurrencies', 'currencyOptions']),
  },
  mounted() {
    this.getCurrencies()
  },
  methods: {
    ...mapMutations('wallet', { setCurrencies: 'SET_CURRENCIES' }),
    ...mapActions('wallet', ['getCurrencies', 'updateCurrency']),
    clearFilters() {
      this.search = ''
      this.page = 1
    },
    getPage(pagination) {
      this.page = pagination.page
    },
    closeDialog() {
      bus.$emit('CLOSE_DIALOG')
    },
    async updateWalletCurrency(currency) {
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Updating currency..."
        });
        let currencyParams = { ...currency }
        this.$set(currencyParams, 'wallet_token', getWalletToken())
        this.$set(currencyParams, 'is_enabled', !currencyParams.is_enabled)
        let response = await this.updateCurrency(currencyParams)
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
    this.setCurrencies([])
    next()
  }
}
</script>

<style>
.currencies p {
  margin-bottom: 0;
}

.currencies .v-toolbar__content {
  padding: 16px;
}
</style>