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
                type="password"
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
                value="BET NORMAL"
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
                :items="providerStatus"
                label="Status"
                value="Active"
                outlined
                dense
                v-model="$v.providerAccount.status.$model"
                :error-messages="statusErrors"
                @input="$v.providerAccount.status.$touch()"
                @blur="$v.providerAccount.status.$touch()"
              ></v-select>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="['Yes', 'No']"
                label="Idle"
                value="Yes"
                outlined
                dense
                v-model="$v.providerAccount.idle.$model"
                :error-messages="idleErrors"
                @input="$v.providerAccount.idle.$touch()"
                @blur="$v.providerAccount.idle.$touch()"
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
import { mapState } from 'vuex'
import bus from '../../../../eventBus'
import { required, requiredIf, alphaNum, integer, minLength } from 'vuelidate/lib/validators'

export default {
  props: ["update", "providerAccountToUpdate"],
  data: () => ({
    providerAccount: {
      username: '',
      password: '',
      punter_percentage: '',
      type: 'BET NORMAL',
      status: 'Active',
      idle: 'Yes',
      last_bet: '-',
      last_scrape: '-',
      last_sync: '-',
    }
  }),
  validations: {
    providerAccount: {
      username: { required, alphaNum },
      password: {
        required: requiredIf(function() {
          return !this.update
        }),
        minLength: minLength(6)
      },
      punter_percentage: { required, integer },
      type: { required },
      status: { required },
      idle: { required }
    }
  },
  computed: {
    ...mapState("providers", ["providerStatus", "providerAccountTypes"]),
    usernameErrors() {
      let errors = []
      if(!this.$v.providerAccount.username.$dirty) return errors
      !this.$v.providerAccount.username.required && errors.push('Username is required.')
      !this.$v.providerAccount.username.alphaNum && errors.push('Username must be alphanumeric.')
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
      if(!this.$v.providerAccount.status.$dirty) return errors
      !this.$v.providerAccount.status.required && errors.push('Status is required.')
      return errors
    },
    idleErrors() {
      let errors = []
      if(!this.$v.providerAccount.idle.$dirty) return errors
      !this.$v.providerAccount.idle.required && errors.push('Idle is required.')
      return errors
    },

  },
  mounted() {
    this.initializeProviderAccount()
  },
  methods: {
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    initializeProviderAccount() {
      this.resetFields()
      if(this.providerAccountToUpdate) {
        let providerAccountForm = { ...this.providerAccountToUpdate }
        this.$set(providerAccountForm, 'password', '')
        this.providerAccount = providerAccountForm
      }
    },
    addProviderAccount() {
      if(!this.$v.providerAccount.$invalid) {
        this.$store.commit('providers/ADD_PROVIDER_ACCOUNT', this.providerAccount)
        this.closeDialog()
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "A provider account has been added."
        });
      } else {
        this.$v.providerAccount.$touch()
      }
    },
    updateProviderAccount() {
      if(!this.$v.providerAccount.$invalid) {
        this.$store.commit('providers/UPDATE_PROVIDER_ACCOUNT', this.providerAccount)
        this.closeDialog()
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Provider account details were updated."
        });
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
      this.providerAccount.type = 'BET NORMAL'
      this.providerAccount.status = 'Active'
      this.providerAccount.idle = 'Yes'
    }
  }
}
</script>

<style>
.formColumn {
  padding: 0px 10px;
}
</style>
