<template>
  <v-card class="adminSettlementForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Generate Settlement</v-toolbar-title
      >
      <v-spacer></v-spacer>
      <v-btn @click="closeDialog" icon>
        <v-icon dark>mdi-close-circle</v-icon>
      </v-btn>
    </v-toolbar>
    <v-form @submit.prevent="generateSettlement">
      <v-card-text>
        <v-container>
          <span class="body-1 font-weight-medium">{{order.bet_id}}</span>
          <v-row>
            <v-col cols="12" md="6" class="mb-2">
              <span class="betSelection">{{order.bet_selection}}</span>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="Stake"
                type="text"
                outlined
                dense
                disabled
                v-model="settlementForm.stake"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="Price"
                type="text"
                outlined
                dense
                disabled
                v-model="settlementForm.odds"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="To Win"
                type="text"
                outlined
                dense
                disabled
                v-model="settlementForm.to_win"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="4" class="formColumn">
              <v-select
                :items="orderStatus"
                label="Status"
                outlined
                dense
                v-model="$v.settlementForm.status.$model"
                :error-messages="statusErrors"
                @input="$v.settlementForm.status.$touch()"
                @blur="$v.settlementForm.status.$touch()"
              ></v-select>
            </v-col>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="Score"
                type="text"
                outlined
                dense
                placeholder="e.g. 6 - 9"
                v-model="$v.settlementForm.score.$model"
                :error-messages="scoreErrors"
                @input="$v.settlementForm.score.$touch()"
                @blur="$v.settlementForm.score.$touch()"
                :disabled="type == 'user'"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4" class="formColumn">
              <v-text-field
                label="PL"
                type="text"
                outlined
                dense
                v-model="$v.settlementForm.pl.$model"
                :error-messages="plErrors"
                @input="$v.settlementForm.pl.$touch()"
                @blur="$v.settlementForm.pl.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-textarea
                outlined
                dense
                label="Reason"
                v-model="$v.settlementForm.reason.$model"
                :error-messages="reasonErrors"
                @input="$v.settlementForm.reason.$touch()"
                @blur="$v.settlementForm.reason.$touch()"
              ></v-textarea>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn dark right class="red darken-2" @click="closeDialog"
          >Cancel</v-btn
        >
        <v-btn type="submit" dark right class="success">Generate Settlement</v-btn>
      </v-card-actions>
    </v-form>
  </v-card>
</template>

<script>
import { mapActions } from 'vuex'
import bus from '../../../../eventBus'
import { required, requiredIf, decimal } from 'vuelidate/lib/validators'
import { handleAPIErrors } from '../../../../helpers/errors'

function scoreValidation(value) {
  let regexPattern = /\b(0|[0-9]\d*) - (0|[0-9]\d*)\b/
  return regexPattern.test(value)
}

export default {
  props: ["order", "type"],
  data() {
    return {
      settlementForm: {
        provider: this.order.provider,
        sport: this.order.sport_id || null,
        username: this.order.username,
        bet_id: this.order.bet_id,
        stake: this.type == 'user' ? this.order.stake : this.order.actual_stake,
        odds: this.order.odds,
        to_win: this.type == 'user' ? this.order.to_win : this.order.actual_to_win,
        status: '',
        score: this.type == 'user' ? this.order.current_score : '',
        pl: '',
        reason: '',
        id: this.type == 'user'? this.order.id : null
      },
      orderStatus: ['WIN', 'LOSE', 'HALF WIN', 'HALF LOSE', 'PUSH', 'VOID', 'DRAW', 'CANCELLED', 'REJECTED', 'ABNORMAL BET', 'REFUNDED']
    }
  },
  validations: {
    settlementForm: {
      status: { required },
      score: { 
        required: requiredIf(function() {
          return this.type == 'providerAccount'
        }), 
        scoreValidation 
      },
      pl: { required, decimal },
      reason: { required }
    }
  },
  computed: {
    statusErrors() {
      let errors = []
      if (!this.$v.settlementForm.status.$dirty) return errors
      !this.$v.settlementForm.status.required && errors.push('Status is required.')
      return errors
    },
    scoreErrors() {
      let errors = []
      if (!this.$v.settlementForm.score.$dirty) return errors
      !this.$v.settlementForm.score.required && errors.push('Score is required.')
      !this.$v.settlementForm.score.scoreValidation && errors.push('Score must be in a proper format. e.g. 1 - 0')
      return errors
    },
    plErrors() {
      let errors = []
      if (!this.$v.settlementForm.pl.$dirty) return errors
      !this.$v.settlementForm.pl.required && errors.push('Profit/loss is required.')
      !this.$v.settlementForm.pl.decimal && errors.push('Profit/loss must be numeric.')
      return errors
    },
    reasonErrors() {
      let errors = []
      if (!this.$v.settlementForm.reason.$dirty) return errors
      !this.$v.settlementForm.reason.required && errors.push('Reason is required.')
      return errors
    }
  },
  mounted() {
    this.resetFields()
  },
  methods: {
    ...mapActions("providerAccounts", ["createSettlement"]),
    ...mapActions("users", ["adjustUserTransaction"]),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    async generateSettlement() {
      if(!this.$v.settlementForm.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Generating settlement..."
          });

          let response = ''
          if(this.type == 'user') {
            response = await this.adjustUserTransaction(this.settlementForm)
          } else {
            response = await this.createSettlement(this.settlementForm)
          }

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
        this.$v.settlementForm.$touch()
      }
    },
    resetFields() {
      let fieldsToEmpty = [
        "pl",
      ];
      Object.keys(this.settlementForm).map(key => {
        if (fieldsToEmpty.includes(key)) {
          this.settlementForm[key] = "";
        }
      });
      this.settlementForm.status = 'WIN'
    }
  }
}
</script>

<style>
.formColumn {
  padding: 0px 10px;
}

.betSelection {
  white-space: pre-line;
}
</style>
