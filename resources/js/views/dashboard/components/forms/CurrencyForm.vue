<template>
  <v-card class="currencyForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Manage Currency</v-toolbar-title
      >
      <v-spacer></v-spacer>
      <v-btn @click="closeDialog" icon>
        <v-icon dark>mdi-close-circle</v-icon>
      </v-btn>
    </v-toolbar>
    <v-form @submit.prevent="addCurrency">
      <v-card-text>
        <v-container>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-text-field
                label="Name"
                type="text"
                outlined
                dense
                v-model="$v.currency.name.$model"
                :error-messages="nameErrors"
                @input="$v.currency.name.$touch()"
                @blur="$v.currency.name.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-select
                :items="currencyOptions"
                label="Enabled"
                outlined
                dense
                v-model="$v.currency.is_enabled.$model"
                :error-messages="enabledErrors"
                @input="$v.currency.is_enabled.$touch()"
                @blur="$v.currency.is_enabled.$touch()"
              ></v-select>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn dark right class="red darken-2" @click="closeDialog"
          >Cancel</v-btn
        >
        <v-btn type="submit" dark right class="success">Create</v-btn>
      </v-card-actions>
    </v-form>
  </v-card>
</template>

<script>
import bus from '../../../../eventBus'
import { required, maxLength } from 'vuelidate/lib/validators'
import { mapState, mapActions } from 'vuex'
import { getWalletToken } from '../../../../helpers/token'
import { handleAPIErrors } from '../../../../helpers/errors'

function allUpperCase(value) {
  return value == value.toUpperCase()
}

export default {
  data: () => ({
    currency: {
      name: '',
      is_enabled: true
    }
  }),
  validations: {
    currency: {
      name: { required, maxLength: maxLength(3), allUpperCase },
      is_enabled: { required }
    }
  },
  computed: {
    ...mapState('wallet', ['currencyOptions']),
    nameErrors() {
      let errors = []
      if(!this.$v.currency.name.$dirty) return errors
      !this.$v.currency.name.required && errors.push('Name is required.')
      !this.$v.currency.name.maxLength && errors.push('Name should not be greater than 3 characters.')
      !this.$v.currency.name.allUpperCase && errors.push('Name should all have capital letters.')
      return errors
    },
    enabledErrors() {
      let errors = []
      if(!this.$v.currency.is_enabled.$dirty) return errors
      !this.$v.currency.is_enabled.required && errors.push('Enabled field is required.')
      return errors
    }
  },
  mounted() {
    this.resetFields()
  },
  methods: {
    ...mapActions('wallet', ['createCurrency']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    async addCurrency() {
      try {
        if(!this.$v.currency.$invalid) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Adding new currency..."
          });
          this.$set(this.currency, 'wallet_token', getWalletToken())
          await this.createCurrency(this.currency)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "A new currency has been created."
          });
        } else {
          this.$v.currency.$touch()
        }
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: handleAPIErrors(err)
        });
      }
    },
    resetFields() {
      this.currency.name = ''
      this.currency.is_enabled = true
    }
  }
}
</script>

<style>
.formColumn {
  padding: 5px 10px;
}
</style>
