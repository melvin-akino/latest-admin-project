<template>
  <v-app>
    <v-content>
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" sm="8" md="4">
            <base-material-card>
              <template v-slot:heading class="loginHeading">
                <div class="display-2 font-weight-light">
                  Multiline Login
                </div>
              </template>

              <v-form @submit.prevent="login">
                <v-text-field
                  label="Email"
                  type="text"
                  prepend-inner-icon="mdi-account"
                  v-model="$v.loginForm.email.$model"
                  :error-messages="emailErrors"
                  @input="$v.loginForm.email.$touch()"
                  @blur="$v.loginForm.email.$touch()"
                ></v-text-field>
                <v-text-field
                  label="Password"
                  type="password"
                  prepend-inner-icon="mdi-lock"
                  v-model="$v.loginForm.password.$model"
                  :error-messages="passwordErrors"
                  @input="$v.loginForm.password.$touch()"
                  @blur="$v.loginForm.password.$touch()"
                ></v-text-field>
                <!-- <v-checkbox
                  label="Remember Me"
                  v-model="loginForm.remember_me"
                ></v-checkbox> -->
                <v-card-actions>
                  <span class="caption red--text text--darken-2" v-if="loginError">{{loginError}}</span>
                  <v-spacer></v-spacer>
                  <v-btn type="submit" dark right class="primary">Login</v-btn>
                </v-card-actions>
              </v-form>
            </base-material-card>
          </v-col>
        </v-row>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
import { mapState } from "vuex"
import { required, email } from "vuelidate/lib/validators"
import Cookies from "js-cookie"

export default {
  data() {
    return {
      loginForm: {
        email: "",
        password: "",
        remember_me: false
      },
      loginError: ''
    };
  },
  validations: {
    loginForm: {
      email: { required, email },
      password: { required }
    }
  },
  computed: {
    ...mapState('admin', ['admin']),
    emailErrors() {
      let errors = []
      if(!this.$v.loginForm.email.$dirty) return errors
      !this.$v.loginForm.email.required && errors.push('Please type your email.')
      !this.$v.loginForm.email.email && errors.push('Please type a valid email.')
      return errors
    },
    passwordErrors() {
      let errors = []
      if(!this.$v.loginForm.password.$dirty) return errors
      !this.$v.loginForm.password.required && errors.push('Please type your password.')
      return errors
    }
  },
  methods: {
    login() {
      if(!this.$v.loginForm.$invalid) {
        let isUserValid = this.admin.some(admin => admin.email == this.loginForm.email && admin.password == this.loginForm.password)
        this.$store.commit('SET_AUTHENTICATED', isUserValid)
        if(isUserValid) {
          this.loginError = ''
          Cookies.set('authenticated', true)
          this.$router.push('/accounts/users')
        } else {
          this.loginError = 'Invalid user credentials.'
        }
      } else {
        this.$v.loginForm.$touch()
      }
    }
  }
};
</script>

<style>
  .loginHeading {
      height: 81px;
  }
</style>
