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
                v-model="$v.user.first_name.$model"
                :error-messages="firstNameErrors"
                @input="$v.user.first_name.$touch()"
                @blur="$v.user.first_name.$touch()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Last Name"
                type="text"
                outlined
                dense
                v-model="$v.user.last_name.$model"
                :error-messages="lastNameErrors"
                @input="$v.user.last_name.$touch()"
                @blur="$v.user.last_name.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="userStatus"
                label="Status"
                value="Active"
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
                v-model="$v.user.credits.$model"
                :error-messages="creditsErrors"
                @input="$v.user.credits.$touch()"
                @blur="$v.user.credits.$touch()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="currencies"
                label="Currency"
                outlined
                dense
                value="CNY"
                v-model="$v.user.currency.$model"
                :error-messages="currencyErrors"
                @input="$v.user.currency.$touch()"
                @blur="$v.user.currency.$touch()"
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
import { mapState } from "vuex";
import bus from "../../../../eventBus";
import { required, requiredIf, email, minLength, decimal } from 'vuelidate/lib/validators'
import moment from 'moment'
import randomstring from 'randomstring'

function creditsValue(value) {
  if(this.update) return true
  return value > 0
}

export default {
  name: "UserForm",
  props: ["update", "userToUpdate"],
  data: () => ({
    user: {
      email: "",
      password: "",
      first_name: "",
      last_name: "",
      status: "Active",
      credits: "",
      currency: "CNY",
      open_bets: "-",
      last_bet: "-",
      last_login: "-",
      created_date: moment().format('YYYY-MM-DD HH:mm:ss')
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
      first_name: { required },
      last_name: { required },
      status: { required },
      credits: { required, creditsValue, decimal },
      currency: { required }
    }
  },
  computed: {
    ...mapState("users", ["userStatus", "currencies"]),
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
      if (!this.$v.user.first_name.$dirty) return errors
      !this.$v.user.first_name.required && errors.push('First name is required.')
      return errors
    },
    lastNameErrors() {
      let errors = []
      if (!this.$v.user.last_name.$dirty) return errors
      !this.$v.user.last_name.required && errors.push('Last name is required.')
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
      if (!this.$v.user.credits.$dirty) return errors
      !this.$v.user.credits.required && errors.push('Credits is required.')
      !this.$v.user.credits.decimal && errors.push('Credits should be numeric.')
      !this.$v.user.credits.creditsValue && errors.push('Credits should have at least a minimum value of 1.')
      return errors
    },
    currencyErrors() {
      let errors = []
      if (!this.$v.user.currency.$dirty) return errors
      !this.$v.user.currency.required && errors.push('Currency is required.')
      return errors
    }
  },
  mounted() {
    this.initializeUser();
  },
  methods: {
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
    addUser() {
      if(!this.$v.user.$invalid) {
        this.$store.commit("users/ADD_USER", this.user);
        this.closeDialog();
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "A new user has been created."
        });
      } else {
        this.$v.user.$touch()
      }
    },
    updateUser() {
      if(!this.$v.user.$invalid) {
        this.$store.commit("users/UPDATE_USER", this.user);
        this.closeDialog();
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "User account details were updated."
        });
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
        "first_name",
        "last_name",
        "credits"
      ];
      Object.keys(this.user).map(key => {
        if (fieldsToEmpty.includes(key)) {
          this.user[key] = "";
        }
      });
      this.user.status = 'Active'
      this.user.currency = 'CNY'
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
