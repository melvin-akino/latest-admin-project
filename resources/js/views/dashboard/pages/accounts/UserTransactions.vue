<template>
  <div class="userTransactions pa-6">
    <v-container>
      <p class="text-h4 text-uppercase" v-if="user">
        Transaction Report for {{user}}
        <span v-if="search.date_from && search.date_to">({{search.date_from}} to {{search.date_to}})</span>
      </p>
      <div class="my-6">
        <v-form @submit.prevent="getUserTransactions">
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="From Date"
                type="date"
                outlined
                dense
                background-color="#fff"
                v-model="$v.search.date_from.$model"
                :error-messages="fromDateErrors"
                @input="$v.search.date_from.$touch()"
                @blur="$v.search.date_from.$touch()"
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
                v-model="$v.search.date_to.$model"
                :error-messages="toDateErrors"
                @input="$v.search.date_to.$touch()"
                @blur="$v.search.date_to.$touch()"
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
                label="Select Period"
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
                :items="providers"
                item-text="alias"
                item-value="id"
                label="Select Bookmaker"
                outlined
                dense
                background-color="#fff"
                v-model="search.provider_id"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-select
                :items="currencies"
                item-text="code"
                item-value="id"
                label="Select Currency"
                outlined
                dense
                background-color="#fff"
                v-model="search.currency_id"
              ></v-select>
            </v-col>
          </v-row>
          <v-btn type="submit" color="primary" depressed elevation="2" dark height="30">Generate</v-btn>
        </v-form>
      </div>
      <v-data-table
        :headers="headers"
        :items="userTransactions"
        :items-per-page="10"
        :loading="isLoadingUserTransactions"
        loading-text="Loading user transactions"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Bets: {{userTransactions.length}}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.bet_selection`]="{ item }">
          <span class="betSelection">{{item.bet_selection}}</span>
        </template>
        <template v-slot:[`item.valid_stake`]="{ item }">
          <span>{{Math.abs(Number(item.profit_loss))}}</span>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import moment from 'moment'
import { required, requiredIf } from 'vuelidate/lib/validators'
import { getToken } from '../../../../helpers/token'
import bus from '../../../../eventBus'

function toDateValidation(value) {
  return value >= this.search.date_from
}

export default {
  name: 'UserTransactions',
  data() {
    return {
      headers: [
        { text: 'BET ID', value: 'bet_id' },
        { text: 'POST DATE', value: 'created_at' },
        { text: 'BET SELECTION', value: 'bet_selection', width: "20%" },
        { text: 'USERNAME', value: 'username', width: "10%" },
        { text: 'STAKE', value: 'stake' },
        { text: 'PRICE', value: 'odds' },
        { text: 'TO WIN', value: 'to_win' },
        { text: 'STATUS', value: 'status' },
        { text: 'VALID STAKE', value: 'valid_stake' },
        { text: 'P/L', value: 'profit_loss' },
        { text: 'REMARKS', value: 'reason' },
      ],
      search: {
        user_id: this.$route.params.id,
        date_from: moment().startOf('isoweek').format('YYYY-MM-DD'),
        date_to: moment().add(1, 'week').startOf('isoweek').format('YYYY-MM-DD'),
        period: null,
        provider_id: null,
        currency_id: null
      },
      user: '',
      userTransactions: [],
      isLoadingUserTransactions: false,
      periods: [
        { text: 'All', value: 'all' },
        { text: 'Today', value: 'today' },
        { text: 'Yesterday', value: 'yesterday' },
        { text: 'This Week', value: 'this_week' },
        { text: 'Last Week', value: 'last_week' },
        { text: 'This Period', value: 'this_period' },
        { text: 'Last Period', value: 'last_period' },
      ]
    }
  },
  validations: {
    search: {
      date_from: {
        required: requiredIf(function() {
          return this.search.date_to
        })
      },
      date_to: {
        required: requiredIf(function() {
          return this.search.date_from
        }),
        toDateValidation
      }
    }
  },
  computed: {
    ...mapState('resources', ['providers', 'currencies']),
    fromDateErrors() {
      let errors = []
      !this.$v.search.date_from.required && errors.push('From date is required.')
      return errors
    },
    toDateErrors() {
      let errors = []
      !this.$v.search.date_to.required && errors.push('To date is required.')
      !this.$v.search.date_to.toDateValidation && errors.push('To date value must be a later date.')
      return errors
    }
  },
  mounted() {
    this.getProviders()
    this.getCurrencies()
    this.getUser()
    this.getUserTransactions()
  },
  methods: {
    ...mapActions('resources', ['getProviders', 'getCurrencies']),
    ...mapActions('auth', ['logoutOnError']),
    getUser() {
      axios.get(`user/${this.$route.params.id}`, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        this.user = `${response.data.firstname} ${response.data.lastname}`
      })
      .catch(err => {
        this.logoutOnError(err.response.status)
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      })
    },
    getUserTransactions() {
      this.userTransactions = []
      this.isLoadingUserTransactions = true
      axios.get('orders/user', { params: this.search, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        this.userTransactions = response.data
        this.isLoadingUserTransactions = false
      })
      .catch(err => {
        this.logoutOnError(err.response.status)
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      })
    },
    setFilterDates() {
      let fromToDate = {
        all: {
          date_from: null,
          date_to: null
        },
        today: {
          date_from: moment().format('YYYY-MM-DD'),
          date_to: moment().format('YYYY-MM-DD')
        },
        yesterday: {
          date_from: moment().subtract(1, 'days').format('YYYY-MM-DD'),
          date_to: moment().subtract(1, 'days').format('YYYY-MM-DD')
        },
        this_week: {
          date_from: moment().startOf('week').format('YYYY-MM-DD'),
          date_to: moment().endOf('week').format('YYYY-MM-DD')
        },
        last_week: {
          date_from: moment().subtract(1, 'week').startOf('week').format('YYYY-MM-DD'),
          date_to: moment().subtract(1, 'week').endOf('week').format('YYYY-MM-DD')
        },
        this_period: {
          date_from: moment().startOf('month').format('YYYY-MM-DD'),
          date_to: moment().endOf('month').format('YYYY-MM-DD')
        },
        last_period: {
          date_from: moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD'),
          date_to: moment().subtract(1, 'month').endOf('month').format('YYYY-MM-DD')
        }
      }
      Object.keys(fromToDate).map(key => {
        if(this.search.period == key) {
          this.search.date_from = fromToDate[key].date_from
          this.search.date_to = fromToDate[key].date_to
        }
      })
    },
    resetPeriod() {
      this.search.period = null
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

.betSelection {
  white-space: pre-line;
}
</style>
