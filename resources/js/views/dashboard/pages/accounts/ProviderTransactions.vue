<template>
  <div class="providerTransactions pa-6">
    <v-container>
      <p class="text-h4 text-uppercase" v-if="providerAccount">
        Provider Account Transaction Report for {{providerAccount.username}}
        <span v-if="search.created_from && search.created_to">({{search.created_from}} to {{search.created_to}})</span>
      </p>
      <div class="my-6">
        <v-form @submit.prevent="getProviderTransactions">
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="From Date"
                type="date"
                outlined
                dense
                background-color="#fff"
                v-model="$v.search.created_from.$model"
                :error-messages="createdFromErrors"
                @input="$v.search.created_from.$touch()"
                @blur="$v.search.created_from.$touch()"
                @keydown.prevent
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="To Date"
                type="date"
                outlined
                dense
                background-color="#fff"
                v-model="$v.search.created_to.$model"
                :error-messages="createdToErrors"
                @input="$v.search.created_to.$touch()"
                @blur="$v.search.created_to.$touch()"
                @keydown.prevent
              ></v-text-field>
            </v-col>
          </v-row>
          <v-btn type="submit" color="primary" depressed elevation="2" dark height="30">Apply Filters</v-btn>
        </v-form>
      </div>
      <v-data-table
        :headers="headers"
        :items="providerTransactions"
        :items-per-page="10"
        :loading="isLoadingProviderTransactions"
        loading-text="Loading provider transactions"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Provider Transactions: {{providerTransactions.length}}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.bet_selection`]="{ item }">
          <span class="betSelection">{{item.bet_selection}}</span>
        </template>
        <template v-slot:[`item.actual_stake`]="{ item }">
          <span>{{ item.actual_stake | moneyFormat }}</span>
        </template>
        <template v-slot:[`item.actual_to_win`]="{ item }">
          <span>{{ item.actual_to_win | moneyFormat }}</span>
        </template>
        <template v-slot:[`item.actions`]="{ item }">
          <table-action-dialog icon="mdi-pencil" width="600" tooltipText="Generate Settlement">
            <admin-settlement-form :order="item" type="providerAccount"></admin-settlement-form>
          </table-action-dialog>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
import moment from 'moment'
import { requiredIf } from 'vuelidate/lib/validators'
import { getToken } from '../../../../helpers/token'
import { moneyFormat } from '../../../../helpers/numberFormat'
import bus from '../../../../eventBus'

function toDateValidation(value) {
  return value >= this.search.created_from
}

export default {
  name: 'ProviderTransactions',
  components: {
    TableActionDialog: () => import("../../component/TableActionDialog"),
    AdminSettlementForm: () => import("../../components/forms/AdminSettlementForm"),
  },
  data() {
    return {
      headers: [
        { text: 'BET ID', value: 'bet_id' },
        { text: 'POST DATE', value: 'created_at' },
        { text: 'BET SELECTION', value: 'bet_selection', width: "20%" },
        { text: 'PROVIDER', value: 'provider' },
        { text: 'STAKE', value: 'actual_stake' },
        { text: 'PRICE', value: 'odds' },
        { text: 'TO WIN', value: 'actual_to_win' },
        {
          text: "",
          value: "actions",
          width: "10%",
          align: "center",
          sortable: false
        }
      ],
      search: {
        provider_account_id: this.$route.params.id,
        created_from: moment().startOf('isoweek').format('YYYY-MM-DD'),
        created_to: moment().format('YYYY-MM-DD'),
        status: 'open'
      },
      providerAccount: '',
      providerTransactions: [],
      isLoadingProviderTransactions: false,
    }
  },
  validations: {
    search: {
      created_from: {
        required: requiredIf(function() {
          return this.search.created_to
        })
      },
      created_to: {
        required: requiredIf(function() {
          return this.search.created_from
        }),
        toDateValidation
      }
    }
  },
  computed: {
    createdFromErrors() {
      let errors = []
      !this.$v.search.created_from.required && errors.push('From date is required.')
      return errors
    },
    createdToErrors() {
      let errors = []
      !this.$v.search.created_to.required && errors.push('To date is required.')
      !this.$v.search.created_to.toDateValidation && errors.push('To date value must be a later date.')
      return errors
    }
  },
  mounted() {
    this.getProviderAccount()
    this.getProviderTransactions()
  },
  methods: {
    ...mapActions('auth', ['logoutOnError']),
    getProviderAccount() {
      axios.get(`provider-account/${this.$route.params.id}`, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        this.providerAccount = response.data
      })
      .catch(err => {
        this.logoutOnError(err.response.status)
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      })
    },
    getProviderTransactions() {
      this.isLoadingProviderTransactions = true
      this.providerTransactions = []
      axios.get('provider-accounts/orders', { params: this.search, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        this.providerTransactions = response.data.data ? response.data.data : []
        this.isLoadingProviderTransactions = false
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
  filters: {
    moneyFormat
  }
}
</script>

<style>
.providerTransactions p {
  margin-bottom: 0;
}

.providerTransactions .v-toolbar__content {
  padding: 16px;
}

.providerTransactions .theme--light.v-label {
  color: #000;
}

.formColumn {
  padding: 0px 10px;
}

.betSelection {
  white-space: pre-line;
}
</style>
