<template>
    <v-card class="userForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Manage Provider Account</v-toolbar-title
      >
      <v-spacer></v-spacer>
      <v-btn @click="closeDialog" icon>
        <v-icon dark>mdi-close-circle</v-icon>
      </v-btn>
    </v-toolbar>
    <v-form @submit.prevent="submit">
      <v-card-text>
        <v-container>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Line"
                type="text"
                outlined
                dense
                v-model="$v.providerAccount.line.$model"
                :error-messages="lineErrors"
                @input="$v.providerAccount.line.$touch()"
                @blur="$v.providerAccount.line.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Username"
                type="text"
                outlined
                dense
                v-model="$v.providerAccount.username.$model"
                :error-messages="usernameErrors"
                @input="$v.providerAccount.username.$touch()"
                @blur="$v.providerAccount.username.$touch()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Password"
                outlined
                dense
                v-model="$v.providerAccount.password.$model"
                :error-messages="passwordErrors"
                @input="$v.providerAccount.password.$touch()"
                @blur="$v.providerAccount.password.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Punter Percentage"
                type="text"
                outlined
                dense
                v-model="$v.providerAccount.punter_percentage.$model"
                :error-messages="punterPercentageErrors"
                @input="$v.providerAccount.punter_percentage.$touch()"
                @blur="$v.providerAccount.punter_percentage.$touch()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="providerAccountTypes"
                label="Type"
                outlined
                dense
                v-model="$v.providerAccount.type.$model"
                :error-messages="typeErrors"
                @input="$v.providerAccount.type.$touch()"
                @blur="$v.providerAccount.type.$touch()"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="providerAccountStatus"
                label="Status"
                outlined
                dense
                v-model="$v.providerAccount.is_enabled.$model"
                :error-messages="statusErrors"
                @input="$v.providerAccount.is_enabled.$touch()"
                @blur="$v.providerAccount.is_enabled.$touch()"
              ></v-select>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="providerAccountIdleOptions"
                label="Idle"
                outlined
                dense
                v-model="$v.providerAccount.is_idle.$model"
                :error-messages="idleErrors"
                @input="$v.providerAccount.is_idle.$touch()"
                @blur="$v.providerAccount.is_idle.$touch()"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="providers"
                label="Provider"
                outlined
                dense
                item-text="alias"
                item-value="id"
                v-model="$v.providerAccount.provider_id.$model"
                :error-messages="providerErrors"
                @input="$v.providerAccount.provider_id.$touch()"
                @blur="$v.providerAccount.provider_id.$touch()"
              ></v-select>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="providerAccountUsages"
                label="Usage"
                outlined
                dense
                v-model="$v.providerAccount.usage.$model"
                :error-messages="usageErrors"
                @input="$v.providerAccount.usage.$touch()"
                @blur="$v.providerAccount.usage.$touch()"
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
        <v-btn
          v-if="providerAccountToUpdate"
          type="submit"
          dark
          right
          class="success"
          >Update</v-btn
        >
        <v-btn v-else type="submit" dark right class="success">Create</v-btn>
      </v-card-actions>
    </v-form>
  </v-card>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import bus from '../../../../eventBus'
import { required, requiredIf, alphaNum, integer, minLength } from 'vuelidate/lib/validators'
import { getWalletToken } from '../../../../helpers/token'

export default {
  props: ["update", "providerAccountToUpdate"],
  data: () => ({
    providerAccount: {
      id: null,
      line: null,
      username: '',
      password: '',
      punter_percentage: '',
      type: 'BET_NORMAL',
      is_enabled: true,
      is_idle: true,
      provider_id: 1,
      usage: 'OPEN'
    }
  }),
  validations: {
    providerAccount: {
      username: { required, alphaNum },
      line: { required },
      password: {
        required: requiredIf(function() {
          return !this.update
        }),
        minLength: minLength(6)
      },
      punter_percentage: { required, integer },
      type: { required },
      is_enabled: { required },
      is_idle: { required },
      provider_id: { required },
      usage: { required }
    }
  },
  computed: {
    ...mapState("providerAccounts", ["providerAccountStatus", "providerAccountTypes", "providerAccountIdleOptions", "providerAccountUsages"]),
    ...mapState("providers", ["providers"]),
    usernameErrors() {
      let errors = []
      if(!this.$v.providerAccount.username.$dirty) return errors
      !this.$v.providerAccount.username.required && errors.push('Username is required.')
      !this.$v.providerAccount.username.alphaNum && errors.push('Username must be alphanumeric.')
      return errors
    },
    lineErrors() {
      let errors = []
      if(!this.$v.providerAccount.line.$dirty) return errors
      !this.$v.providerAccount.line.required && errors.push('Line is required.')
      return errors
    },
    passwordErrors() {
      let errors = []
      if(!this.$v.providerAccount.password.$dirty) return errors
      !this.$v.providerAccount.password.required && errors.push('Password is required.')
      !this.$v.providerAccount.password.minLength && errors.push('Password should have at least 6 characters.')
      return errors
    },
    punterPercentageErrors() {
      let errors = []
      if(!this.$v.providerAccount.punter_percentage.$dirty) return errors
      !this.$v.providerAccount.punter_percentage.required && errors.push('Punter percentage is required.')
      !this.$v.providerAccount.punter_percentage.integer && errors.push('Punter percentage must be an integer.')
      return errors
    },
    typeErrors() {
      let errors = []
      if(!this.$v.providerAccount.type.$dirty) return errors
      !this.$v.providerAccount.type.required && errors.push('Type is required.')
      return errors
    },
    statusErrors() {
      let errors = []
      if(!this.$v.providerAccount.is_enabled.$dirty) return errors
      !this.$v.providerAccount.is_enabled.required && errors.push('Status is required.')
      return errors
    },
    idleErrors() {
      let errors = []
      if(!this.$v.providerAccount.is_idle.$dirty) return errors
      !this.$v.providerAccount.is_idle.required && errors.push('Idle is required.')
      return errors
    },
    providerErrors() {
      let errors = []
      if(!this.$v.providerAccount.provider_id.$dirty) return errors
      !this.$v.providerAccount.provider_id.required && errors.push('Provider is required.')
      return errors
    },
    usageErrors() {
      let errors = []
      if(!this.$v.providerAccount.usage.$dirty) return errors
      !this.$v.providerAccount.usage.required && errors.push('Usage is required.')
      return errors
    }
  },
  mounted() {
    this.initializeProviderAccount()
  },
  methods: {
    ...mapActions('providerAccounts', ['manageProviderAccount']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    initializeProviderAccount() {
      this.resetFields()
      if(this.providerAccountToUpdate) {
        let providerForm = { ...this.providerAccountToUpdate }
        this.providerAccount = providerForm
      }
    },
    async addProviderAccount() {
      if(!this.$v.providerAccount.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Adding provider account..."
          });
          let alias = this.providers.filter(provider => provider.id == this.providerAccount.provider_id)[0].alias
          this.$set(this.providerAccount, 'provider_alias', alias)
          this.$set(this.providerAccount, 'wallet_token', getWalletToken())
          await this.manageProviderAccount(this.providerAccount)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "A provider account has been added."
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.hasOwnProperty('errors') ?  err.response.data.errors[Object.keys(err.response.data.errors)[0]][0] : err.response.data.message
          });
        }
      } else {
        this.$v.providerAccount.$touch()
      }
    },
    async updateProviderAccount() {
      if(!this.$v.providerAccount.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Updating provider account..."
          });
          let alias = this.providers.filter(provider => provider.id == this.providerAccount.provider_id)[0].alias
          this.$set(this.providerAccount, 'provider_alias', alias)
          await this.manageProviderAccount(this.providerAccount)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Provider account details were updated."
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.hasOwnProperty('errors') ?  err.response.data.errors[Object.keys(err.response.data.errors)[0]][0] : err.response.data.errors.message
          });
        }
      } else {
        this.$v.providerAccount.$touch()
      }
    },
    submit() {
      if(this.update) {
        this.updateProviderAccount()
      } else {
        this.addProviderAccount()
      }
    },
    resetFields() {
      let fieldsToEmpty = [
        "username",
        "password",
        "punter_percentage"
      ];
      Object.keys(this.providerAccount).map(key => {
        if (fieldsToEmpty.includes(key)) {
          this.providerAccount[key] = "";
        }
      });
    }
  }
}
</script>

<style>
.formColumn {
  padding: 0px 10px;
}
</style>
