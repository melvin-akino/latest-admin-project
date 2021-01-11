<template>
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
              <v-spacer></v-spacer>
              <v-btn type="submit" dark right class="primary" :disabled="isLoggingIn">Login</v-btn>
            </v-card-actions>
          </v-form>
        </base-material-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { mapActions } from "vuex";
import { required, email } from "vuelidate/lib/validators";
import Cookies from "js-cookie";
import bus from "../../eventBus";

export default {
  name: 'Login',
  data() {
    return {
      loginForm: {
        email: "",
        password: "",
        remember_me: false,
      },
      isLoggingIn: false,
    };
  },
  validations: {
    loginForm: {
      email: { required, email },
      password: { required },
    },
  },
  computed: {
    emailErrors() {
      let errors = [];
      if (!this.$v.loginForm.email.$dirty) return errors;
      !this.$v.loginForm.email.required &&
        errors.push("Please type your email.");
      !this.$v.loginForm.email.email &&
        errors.push("Please type a valid email.");
      return errors;
    },
    passwordErrors() {
      let errors = [];
      if (!this.$v.loginForm.password.$dirty) return errors;
      !this.$v.loginForm.password.required &&
        errors.push("Please type your password.");
      return errors;
    },
  },
  methods: {
    ...mapActions('auth', { adminLogin: 'login' }),
    async login() {
      if (!this.$v.loginForm.$invalid) {
        try {
          this.isLoggingIn = true;
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Logging in, please wait."
          });
          let response = await this.adminLogin(this.loginForm)
          this.isLoggingIn = false;
          Cookies.set("access_token", response.token);
          Cookies.set("wallet_token", response.wallet_token);
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Login successful."
          });
          this.$router.push('/accounts/users');
        } catch(err) {
          this.isLoggingIn = false;
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.message
          });
        }
      } else {
        this.$v.loginForm.$touch();
      }
    },
  },
};
</script>

<style>
.loginHeading {
  height: 81px;
}
</style>
