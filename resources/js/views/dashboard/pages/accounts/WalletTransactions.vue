<template>
  <div class="walletTransactions pa-6">
    <v-container>
      <p class="text-h4 text-uppercase" v-if="transactionUser">
        Wallet Transaction Report for {{transactionUser.email || transactionUser.username}}
        <span v-if="search.start_date && search.end_date">({{search.start_date}} to {{search.end_date}})</span>
      </p>
      <div class="my-6">
        <v-form @submit.prevent="getWalletTransactions">
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="From Date"
                type="date"
                outlined
                dense
                background-color="#fff"
                v-model="$v.search.start_date.$model"
                :error-messages="startDateErrors"
                @input="$v.search.start_date.$touch()"
                @blur="$v.search.start_date.$touch()"
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
                v-model="$v.search.end_date.$model"
                :error-messages="endDateErrors"
                @input="$v.search.end_date.$touch()"
                @blur="$v.search.end_date.$touch()"
                @keydown.prevent
              ></v-text-field>
            </v-col>
          </v-row>
          <v-btn type="submit" color="primary" depressed elevation="2" dark height="30">Apply Filters</v-btn>
        </v-form>
      </div>
      <v-data-table
        :headers="headers"
        :items="walletTransactions"
        :items-per-page="10"
        :loading="isLoadingWalletTransactions"
        loading-text="Loading wallet transactions"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Wallet Transactions: {{walletTransactions.length}}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.amount`]="{ item }">
          <span>{{ item.amount | moneyFormat }}</span>
        </template>
        <template v-slot:[`item.type`]="{ item }">
          <span class="text-capitalize">{{ item.type }}</span>
        </template>
        <template v-slot:[`item.timestamp`]="{ item }">
          <span>{{ item.timestamp | dateTimeFormat }}</span>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
import moment from 'moment'
import { requiredIf } from 'vuelidate/lib/validators'
import { getToken, getWalletToken } from '../../../../helpers/token'
import { moneyFormat } from '../../../../helpers/numberFormat'
import { handleAPIErrors } from '../../../../helpers/errors'
import bus from '../../../../eventBus'

function toDateValidation(value) {
  return value >= this.search.start_date
}

export default {
  name: 'WalletTransactions',
  data() {
    return {
      headers: [
        { text: 'AMOUNT', value: 'amount' },
        { text: 'TYPE', value: 'type' },
        { text: 'REASON', value: 'reason', width: "20%" },
        { text: 'CREATED DATE', value: 'timestamp' },
      ],
      search: {
        uuid: this.$route.params.uuid,
        start_date: moment().startOf('isoweek').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
      },
      transactionUser: '',
      walletTransactions: [],
      isLoadingWalletTransactions: false,
    }
  },
  validations: {
    search: {
      start_date: {
        required: requiredIf(function() {
          return this.search.end_date
        })
      },
      end_date: {
        required: requiredIf(function() {
          return this.search.date_from
        }),
        toDateValidation
      }
    }
  },
  computed: {
    startDateErrors() {
      let errors = []
      !this.$v.search.start_date.required && errors.push('From date is required.')
      return errors
    },
    endDateErrors() {
      let errors = []
      !this.$v.search.end_date.required && errors.push('To date is required.')
      !this.$v.search.end_date.toDateValidation && errors.push('To date value must be a later date.')
      return errors
    }
  },
  mounted() {
    this.loadWalletTransactions()
  },
  methods: {
    ...mapActions('auth', ['logoutOnError']),
    getTransactionUser() {
      return axios.get(`${this.$route.params.module}/uuid/${this.$route.params.uuid}`, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        this.transactionUser = response.data.data
      })
      .catch(err => {
        this.logoutOnError(err.response.status)
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      })
    },
    getWalletTransactions() {
      this.walletTransactions = []
      this.isLoadingWalletTransactions = true
      this.$set(this.search, 'currency', this.transactionUser.currency)
      this.$set(this.search, 'wallet_token', getWalletToken())
      return axios.get('wallet/transaction', { params: this.search, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        this.walletTransactions = response.data.data ? response.data.data : []
        this.isLoadingWalletTransactions = false
      })
      .catch(err => {
        this.logoutOnError(err.response.status)
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: handleAPIErrors(err)
        });
      })
    },
    async loadWalletTransactions() {
      await this.getTransactionUser()
      await this.getWalletTransactions()
    }
  },
  filters: {
    moneyFormat,
    dateTimeFormat(value) {
      return moment.utc(value).format('YYYY-MM-DD HH:mm:ss')
    }
  }
}
</script>

<style>
.walletTransactions p {
  margin-bottom: 0;
}

.walletTransactions .v-toolbar__content {
  padding: 16px;
}

.walletTransactions .theme--light.v-label {
  color: #000;
}

.formColumn {
  padding: 0px 10px;
}
</style>
