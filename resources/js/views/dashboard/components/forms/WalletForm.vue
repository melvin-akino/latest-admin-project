<template>
  <v-card class="userForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Wallet</v-toolbar-title
      >
      <v-spacer></v-spacer>
      <v-btn @click="closeDialog" icon>
        <v-icon dark>mdi-close-circle</v-icon>
      </v-btn>
    </v-toolbar>
      <v-form @submit.prevent="updateUserCredits">
        <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12" md="6" class="formColumn">
                  <v-select
                    :items="transactionTypes"
                    label="Trasanction Type"
                    outlined
                    dense
                    value="Deposit"
                    v-model="$v.wallet.transactionType.$model"
                    :error-messages="transactionTypeErrors"
                    @input="$v.wallet.transactionType.$touch()"
                    @blur="$v.wallet.transactionType.$touch()"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6" class="formColumn d-flex flex-column align-end text-uppercase">
                  <span class="subtitle-1">Remaining Credits</span>
                  <p class="headline">{{ userToUpdate.currency }} {{ userToUpdate.credits | moneyFormat }}</p>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12" md="6" class="formColumn">
                  <v-text-field
                    label="Credits"
                    type="text"
                    outlined
                    dense
                    v-model="$v.wallet.amount.$model"
                    :error-messages="creditsErrors"
                    @input="$v.wallet.amount.$touch()"
                    @blur="$v.wallet.amount.$touch()"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6" class="formColumn">
                  <v-select
                    :items="currencies"
                    item-text="code"
                    item-value="id"
                    label="Currency"
                    outlined
                    dense
                    disabled
                    v-model="$v.wallet.currency_id.$model"
                    :error-messages="currencyErrors"
                    @input="$v.wallet.currency_id.$touch()"
                    @blur="$v.wallet.currency_id.$touch()"
                  ></v-select>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12" md="12" class="formColumn">
                  <v-textarea
                    outlined
                    dense
                    label="Remarks"
                    v-model="$v.wallet.reason.$model"
                    :error-messages="reasonErrors"
                    @input="$v.wallet.reason.$touch()"
                    @blur="$v.wallet.reason.$touch()"
                  ></v-textarea>
                </v-col>
              </v-row>
            </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn dark right class="red darken-2" @click="closeDialog">Cancel</v-btn>
          <v-btn v-if="userToUpdate" type="submit" dark right class="success">Save</v-btn>
        </v-card-actions>
      </v-form>
  </v-card>
</template>

<script>
import { mapState, mapActions } from "vuex";
import bus from "../../../../eventBus";
import { required, decimal, minValue } from "vuelidate/lib/validators"
import { getWalletToken } from '../../../../helpers/token'
import { handleAPIErrors } from '../../../../helpers/errors'
import { moneyFormat } from '../../../../helpers/numberFormat'

function creditsWithdraw(value) {
  if(this.wallet.transactionType == 'Deposit') return true
  return this.wallet.transactionType == 'Withdraw' && Number(this.userToUpdate.credits) >= Number(value)
}

export default {
  name: "WalletForm",
  props: ['userToUpdate'],
  data: () => ({
    transactionTypes: ["Deposit", "Withdraw"],
    wallet: {
      transactionType: 'Deposit',
      amount: '',
      currency_id: '',
      reason: ''
    },
  }),
  validations: {
    wallet: {
      transactionType: { required },
      amount: { required, decimal, minValue: minValue(1), creditsWithdraw },
      currency_id: { required },
      reason: { required }
    }
  },
  computed: {
    ...mapState("currencies", ["currencies"]),
    transactionTypeErrors() {
      let errors = []
      if (!this.$v.wallet.transactionType.$dirty) return errors
      !this.$v.wallet.transactionType.required && errors.push('Transaction type is required.')
      return errors
    },
    creditsErrors() {
      let errors = []
      if (!this.$v.wallet.amount.$dirty) return errors
      !this.$v.wallet.amount.required && errors.push('Credits is required.')
      !this.$v.wallet.amount.decimal && errors.push('Credits should be numeric.')
      !this.$v.wallet.amount.minValue && errors.push('Credits should have at least a minimum value of 1.')
      !this.$v.wallet.amount.creditsWithdraw && errors.push('Unable to withdraw credits greater than current credits.')
      return errors
    },
    currencyErrors() {
      let errors = []
      if (!this.$v.wallet.currency_id.$dirty) return errors
      !this.$v.wallet.currency_id.required && errors.push('Currency is required.')
      return errors
    },
    reasonErrors() {
      let errors = []
      if (!this.$v.wallet.reason.$dirty) return errors
      !this.$v.wallet.reason.required && errors.push('Reason is required.')
      return errors
    }
  },
  mounted() {
    this.initializeUserCurrency()
  },
  methods: {
    ...mapActions('users', ['updateWallet']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    initializeUserCurrency() {
      this.wallet.currency_id = this.userToUpdate.currency_id
    },
    async updateUserCredits() {
      if(!this.$v.wallet.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: this.wallet.transactionType == 'Deposit' ? 'Crediting amount to user...' : 'Debiting amount from user...'
          });
          this.$set(this.wallet, 'wallet_token', getWalletToken())
          this.$set(this.wallet, 'uuid', this.userToUpdate.uuid)
          this.$set(this.wallet, 'currency', this.userToUpdate.currency)
          let response = await this.updateWallet(this.wallet)
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
      } else {
        this.$v.wallet.$touch()
      }
    }
  },
  filters: {
    moneyFormat
  }
};
</script>

<style scoped>
  .formColumn {
    padding: 0px 10px;
  }
</style>
