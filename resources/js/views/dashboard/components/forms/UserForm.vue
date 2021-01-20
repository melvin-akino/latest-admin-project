<template>
  <v-card class="userForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Manage Account</v-toolbar-title
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
                label="Email"
                type="text"
                outlined
                dense
                :disabled="update"
                v-model="$v.user.email.$model"
                :error-messages="emailErrors"
                @input="$v.user.email.$touch()"
                @blur="$v.user.email.$touch()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Password"
                outlined
                dense
                v-model="$v.user.password.$model"
                :error-messages="passwordErrors"
                @input="$v.user.password.$touch()"
                @blur="$v.user.password.$touch()"
              >
                <template v-slot:append-outer>
                  <v-btn small class="randomPasswordBtn success" @click="randomizePassword">
                    <v-icon>mdi-refresh</v-icon>
                  </v-btn>
                </template>
              </v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="First Name"
                type="text"
                outlined
                dense
                v-model="$v.user.firstname.$model"
                :error-messages="firstNameErrors"
                @input="$v.user.firstname.$touch()"
                @blur="$v.user.firstname.$touch()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Last Name"
                type="text"
                outlined
                dense
                v-model="$v.user.lastname.$model"
                :error-messages="lastNameErrors"
                @input="$v.user.lastname.$touch()"
                @blur="$v.user.lastname.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="userStatus"
                label="Status"
                outlined
                dense
                v-model="$v.user.status.$model"
                :error-messages="statusErrors"
                @input="$v.user.status.$touch()"
                @blur="$v.user.status.$touch()"
              ></v-select>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      <v-toolbar color="primary" dark height="40px" v-if="!update">
        <v-toolbar-title class="text-uppercase subtitle-1"
          >Wallet Information</v-toolbar-title
        >
      </v-toolbar>
      <v-card-text v-if="!update">
        <v-container>
          <p class="caption mb-4" v-if="userToUpdate">Wallet credit can be updated by clicking on <v-icon small>mdi-currency-gbp</v-icon>icon.</p>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Credit"
                type="text"
                outlined
                dense
                :disabled="update"
                v-model="$v.user.balance.$model"
                :error-messages="creditsErrors"
                @input="$v.user.balance.$touch()"
                @blur="$v.user.balance.$touch()"
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
                value="CNY"
                v-model="$v.user.currency_id.$model"
                :error-messages="currencyErrors"
                @input="$v.user.currency_id.$touch()"
                @blur="$v.user.currency_id.$touch()"
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
          v-if="userToUpdate"
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
import { mapState, mapActions } from "vuex";
import bus from "../../../../eventBus";
import { required, requiredIf, email, minLength, decimal } from 'vuelidate/lib/validators'
import randomstring from 'randomstring'
import { getWalletToken } from '../../../../helpers/token'

function creditsValue(value) {
  if(this.update) return true
  return value > 0
}

export default {
  name: "UserForm",
  props: ["update", "userToUpdate", "currencies"],
  data: () => ({
    user: {
      id: null,
      email: "",
      password: "",
      firstname: "",
      lastname: "",
      status: 1,
      balance: "",
      currency_id: 1
    }
  }),
  validations: {
    user: {
      email: { required, email },
      password: {
        required: requiredIf(function() {
          return !this.update
        }),
        minLength: minLength(6)
      },
      firstname: { required },
      lastname: { required },
      status: { required },
      balance: { 
        required: requiredIf(function() {
          return !this.update
        }),
        creditsValue, 
        decimal 
      },
      currency_id: { 
        required: requiredIf(function() {
          return !this.update
        })
      }
    }
  },
  computed: {
    ...mapState("users", ["userStatus"]),
    emailErrors() {
      let errors = []
      if (!this.$v.user.email.$dirty) return errors
      !this.$v.user.email.required && errors.push('Email is required.')
      !this.$v.user.email.email && errors.push('Email must be valid.')
      return errors
    },
    passwordErrors() {
      let errors = []
      if (!this.$v.user.password.$dirty) return errors
      !this.$v.user.password.required && errors.push('Password is required.')
      !this.$v.user.password.minLength && errors.push('Password must have at least 6 characters.')
      return errors
    },
    firstNameErrors() {
      let errors = []
      if (!this.$v.user.firstname.$dirty) return errors
      !this.$v.user.firstname.required && errors.push('First name is required.')
      return errors
    },
    lastNameErrors() {
      let errors = []
      if (!this.$v.user.lastname.$dirty) return errors
      !this.$v.user.lastname.required && errors.push('Last name is required.')
      return errors
    },
    statusErrors() {
      let errors = []
      if (!this.$v.user.status.$dirty) return errors
      !this.$v.user.status.required && errors.push('Status is required.')
      return errors
    },
    creditsErrors() {
      let errors = []
      if (!this.$v.user.balance.$dirty) return errors
      !this.$v.user.balance.required && errors.push('Credits is required.')
      !this.$v.user.balance.decimal && errors.push('Credits should be numeric.')
      !this.$v.user.balance.creditsValue && errors.push('Credits should have at least a minimum value of 1.')
      return errors
    },
    currencyErrors() {
      let errors = []
      if (!this.$v.user.currency_id.$dirty) return errors
      !this.$v.user.currency_id.required && errors.push('Currency is required.')
      return errors
    }
  },
  mounted() {
    this.initializeUser();
  },
  methods: {
    ...mapActions('users', ['manageUser']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    initializeUser() {
      this.resetFields()
      if (this.userToUpdate) {
        let userForm = { ...this.userToUpdate }
        this.$set(userForm, 'password', '')
        this.user = userForm;
      }
    },
    async addUser() {
      if(!this.$v.user.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Creating a new account..."
          });
          this.$set(this.user, 'wallet_token', getWalletToken())
          await this.manageUser(this.user)
          this.closeDialog();
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "A new user has been created."
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors.email[0] : err.response.data.message
          });
        }
      } else {
        this.$v.user.$touch()
      }
    },
    async updateUser() {
      if(!this.$v.user.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Updating user account..."
          });
          await this.manageUser(this.user)
          this.closeDialog();
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "User account details were updated."
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors.email[0] : err.response.data.message
          });
        }
      } else {
        this.$v.user.$touch()
      }
    },
    submit() {
      if(this.update) {
        this.updateUser()
      } else {
        this.addUser()
      }
    },
    resetFields() {
      let fieldsToEmpty = [
        "email",
        "password",
        "firstname",
        "lastname",
        "balance"
      ];
      Object.keys(this.user).map(key => {
        if (fieldsToEmpty.includes(key)) {
          this.user[key] = "";
        }
      });
      this.user.status = 1
      this.user.currency_id = 1
    },
    randomizePassword() {
      this.user.password = randomstring.generate(6)
    }
  }
};
</script>

<style scoped>
.userForm .v-card__text {
  padding: 20px 24px 0px 24px;
}

.formColumn {
  padding: 0px 10px;
}

.randomPasswordBtn {
  height: 40px !important;
  margin: 0 !important;
}
</style>
