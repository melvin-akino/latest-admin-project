<template>
  <div class="userTransactions pa-6">
    <v-container>
      <p class="text-h4 text-uppercase">Transaction Report for {{userEmail}}</p>
      <div class="my-6">
        <v-form @submit.prevent="filterUserTransactions">
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="From Date"
                type="date"
                outlined
                dense
                background-color="#fff"
                v-model="$v.search.fromDate.$model"
                :error-messages="fromDateErrors"
                @input="$v.search.fromDate.$touch()"
                @blur="$v.search.fromDate.$touch()"
                @change="resetPeriod"
                @focus="resetPeriod"
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
                v-model="$v.search.toDate.$model"
                :error-messages="toDateErrors"
                @input="$v.search.toDate.$touch()"
                @blur="$v.search.toDate.$touch()"
                @change="resetPeriod"
                @focus="resetPeriod"
                @keydown.prevent
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-select
                :items="periods"
                item-text="text"
                item-value="value"
                label="Period"
                outlined
                dense
                background-color="#fff"
                v-model="search.period"
                @change="setFilterDates"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-select
                :items="['All', 'HG', 'ISN', 'PIN', 'SB']"
                label="Bookmaker"
                outlined
                dense
                background-color="#fff"
                v-model="search.provider"
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
                background-color="#fff"
                v-model="search.currency"
              ></v-select>
            </v-col>
          </v-row>
          <v-btn type="submit" color="primary" depressed elevation="2" dark height="30">Generate</v-btn>
        </v-form>
      </div>
      <v-data-table
        :headers="headers"
        :items="filteredTransactionsTable"
        :items-per-page="10"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Bets: {{filteredTransactionsTable.length}}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.bet_selection`]="{ item }">
          <span v-html="item.bet_selection"></span>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import moment from 'moment'
import { required, requiredIf } from 'vuelidate/lib/validators'

function toDateValidation(value) {
  return value >= this.search.fromDate
}

export default {
  name: 'UserTransactions',
  data:() => ({
    headers: [
      { text: 'BET ID', value: 'bet_id' },
      { text: 'POST DATE', value: 'post_date' },
      { text: 'BET SELECTION', value: 'bet_selection', width: "20%" },
      { text: 'USERNAME', value: 'username', width: "10%" },
      { text: 'STAKE', value: 'stake' },
      { text: 'PRICE', value: 'price' },
      { text: 'TO WIN', value: 'towin' },
      { text: 'STATUS', value: 'status' },
      { text: 'VALID STAKE', value: 'valid_stake' },
      { text: 'P/L', value: 'pl' },
      { text: 'REMARKS', value: 'remarks' },
    ],
    search: {
      fromDate: '',
      toDate: '',
      period: 'all',
      provider: 'All',
      currency: 'All'
    },
    userEmail: '',
    userTransactions: [],
    filteredTransactionsTable: [],
    periods: [
      { text: 'All', value: 'all' },
      { text: 'Today', value: 'today' },
      { text: 'Yesterday', value: 'yesterday' },
      { text: 'This Week', value: 'this_week' },
      { text: 'Last Week', value: 'last_week' },
      { text: 'This Period', value: 'this_period' },
      { text: 'Last Period', value: 'last_period' },
    ]
  }),
  validations: {
    search: {
      fromDate: {
        required: requiredIf(function() {
          return this.search.toDate
        })
      },
      toDate: {
        required: requiredIf(function() {
          return this.search.fromDate
        }),
        toDateValidation
      }
    }
  },
  computed: {
    ...mapState('users', ['users']),
    fromDateErrors() {
      let errors = []
      !this.$v.search.fromDate.required && errors.push('From date is required.')
      return errors
    },
    toDateErrors() {
      let errors = []
      !this.$v.search.toDate.required && errors.push('To date is required.')
      !this.$v.search.toDate.toDateValidation && errors.push('To date value must be a later date.')
      return errors
    }
  },
  mounted() {
    this.getUserDetails()
  },
  methods: {
    getUserDetails() {
      let user = this.users.filter(user => user.id == this.$route.params.id)[0]
      this.userEmail = user.email
      let userTransactionsTable = []
      user.bets.map(transaction => {
        let transactionObject = { ...transaction }
        let formatPostDate = moment(transaction.post_date).format('YYYY-MM-DD')
        this.$set(transactionObject, 'date', new Date(formatPostDate))
        userTransactionsTable.push(transactionObject)
      })
      this.userTransactions = userTransactionsTable
      this.filteredTransactionsTable = userTransactionsTable
    },
    setFilterDates() {
      let fromToDate = {
        all: {
          fromDate: '',
          toDate: ''
        },
        today: {
          fromDate: moment().format('YYYY-MM-DD'),
          toDate: moment().format('YYYY-MM-DD')
        },
        yesterday: {
          fromDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
          toDate: moment().subtract(1, 'days').format('YYYY-MM-DD')
        },
        this_week: {
          fromDate: moment().startOf('week').format('YYYY-MM-DD'),
          toDate: moment().endOf('week').format('YYYY-MM-DD')
        },
        last_week: {
          fromDate: moment().subtract(1, 'week').startOf('week').format('YYYY-MM-DD'),
          toDate: moment().subtract(1, 'week').endOf('week').format('YYYY-MM-DD')
        },
        this_period: {
          fromDate: moment().startOf('month').format('YYYY-MM-DD'),
          toDate: moment().endOf('month').format('YYYY-MM-DD')
        },
        last_period: {
          fromDate: moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD'),
          toDate: moment().subtract(1, 'month').endOf('month').format('YYYY-MM-DD')
        }
      }
      Object.keys(fromToDate).map(key => {
        if(this.search.period == key) {
          this.search.fromDate = fromToDate[key].fromDate
          this.search.toDate = fromToDate[key].toDate
        }
      })
    },
    resetPeriod() {
      this.search.period = ''
    },
    filterUserTransactions() {
      let fromDate = this.search.fromDate ? new Date(this.search.fromDate) : ''
      let toDate = this.search.toDate ? new Date(this.search.toDate) : ''
      let filteredByDate = this.userTransactions.filter(transaction => {
        if(this.search.fromDate && fromDate > transaction.date) {
          return false
        }
        if(this.search.toDate && toDate < transaction.date) {
          return false
        }
        return true
      })
      let filteredByCurrencyAndProvider = filteredByDate.filter(transaction => {
        let filterParams = [this.search.provider, this.search.currency]
        if(filterParams.includes('All')) {
          if(this.search.provider && this.search.provider != 'All') {
            if(this.search.provider == transaction.provider) {
              return true
            } else {
              return false
            }
          }
          if(this.search.currency && this.search.currency != 'All') {
              if(this.search.currency == transaction.currency) {
                return true
              } else {
                return false
              }
              return true
          }
          return true
        } else {
          return this.search.provider == transaction.provider && this.search.currency == transaction.currency
        }
      })
      this.filteredTransactionsTable = filteredByCurrencyAndProvider
    }
  }
}
</script>

<style>
.userTransactions p {
  margin-bottom: 0;
}

.userTransactions .v-toolbar__content {
  padding: 16px;
}

.userTransactions .theme--light.v-label {
  color: #000;
}

.formColumn {
  padding: 0px 10px;
}
</style>
