<template>
  <v-card class="walletClientForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Create Wallet Client</v-toolbar-title
      >
      <v-spacer></v-spacer>
      <v-btn @click="closeDialog" icon>
        <v-icon dark>mdi-close-circle</v-icon>
      </v-btn>
    </v-toolbar>
    <v-form @submit.prevent="addClient">
      <v-card-text>
        <v-container>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-text-field
                label="Name"
                type="text"
                outlined
                dense
                v-model="$v.walletClient.name.$model"
                :error-messages="nameErrors"
                @input="$v.walletClient.name.$touch()"
                @blur="$v.walletClient.name.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-text-field
                label="Client ID"
                type="text"
                outlined
                dense
                v-model="$v.walletClient.client_id.$model"
                :error-messages="clientIdErrors"
                @input="$v.walletClient.client_id.$touch()"
                @blur="$v.walletClient.client_id.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-text-field
                label="Client Secret"
                type="text"
                outlined
                dense
                v-model="$v.walletClient.client_secret.$model"
                :error-messages="clientSecretErrors"
                @input="$v.walletClient.client_secret.$touch()"
                @blur="$v.walletClient.client_secret.$touch()"
              ></v-text-field>
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
import { required, maxLength, alphaNum } from 'vuelidate/lib/validators'
import { mapActions } from 'vuex'
import { getWalletToken } from '../../../../helpers/token'
import { handleAPIErrors } from '../../../../helpers/errors'

export default {
  data: () => ({
    walletClient: {
      name: '',
      client_id: '',
      client_secret: ''
    }
  }),
  validations: {
    walletClient: {
      name: { required },
      client_id: { required, maxLength: maxLength(32), alphaNum },
      client_secret: { required, maxLength: maxLength(32), alphaNum },
    }
  },
  computed: {
    nameErrors() {
      let errors = []
      if(!this.$v.walletClient.name.$dirty) return errors
      !this.$v.walletClient.name.required && errors.push('Name is required.')
      return errors
    },
    clientIdErrors() {
      let errors = []
      if(!this.$v.walletClient.client_id.$dirty) return errors
      !this.$v.walletClient.client_id.required && errors.push('Client ID is required.')
      !this.$v.walletClient.client_id.alphaNum && errors.push('Client ID may only contain letters and numbers.')
      !this.$v.walletClient.client_id.maxLength && errors.push('Client ID is up to only 32 characters.')
      return errors
    },
    clientSecretErrors() {
      let errors = []
      if(!this.$v.walletClient.client_secret.$dirty) return errors
      !this.$v.walletClient.client_secret.required && errors.push('Client secret is required.')
      !this.$v.walletClient.client_secret.alphaNum && errors.push('Client secret may only contain letters and numbers.')
      !this.$v.walletClient.client_secret.maxLength && errors.push('Client secret is up to only 32 characters.')
      return errors
    }
  },
  mounted() {
    this.resetFields()
  },
  methods: {
    ...mapActions('wallet', ['createClient']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    async addClient() {
      try {
        if(!this.$v.walletClient.$invalid) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Adding new wallet client..."
          });
          this.$set(this.walletClient, 'wallet_token', getWalletToken())
          let response = await this.createClient(this.walletClient)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: response
          });
        } else {
          this.$v.walletClient.$touch()
        }
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: handleAPIErrors(err)
        });
      }
    },
    resetFields() {
      Object.keys(this.walletClient).map(key => {
        this.walletClient[key] = ''
      })
    }
  }
}
</script>

<style>
.formColumn {
  padding: 5px 10px;
}
</style>
