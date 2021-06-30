<template>
  <v-card class="providerErrorForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Manage Provider Error Message</v-toolbar-title
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
            <v-col cols="12" md="12" class="formColumn">
              <v-text-field
                label="Provider Message"
                type="text"
                outlined
                dense
                v-model="$v.providerError.message.$model"
                :error-messages="providerMessageErrors"
                @input="$v.providerError.message.$touch()"
                @blur="$v.providerError.message.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-select
                :items="generalErrors"
                label="Error Message"
                placeholder="Select Error Message"
                item-text="error"
                item-value="id"
                outlined
                dense
                v-model="$v.providerError.error_message_id.$model"
                :error-messages="errorMessageErrors"
                @input="$v.providerError.error_message_id.$touch()"
                @blur="$v.providerError.error_message_id.$touch()"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-select
                :items="retryTypes"
                label="Retry Type"
                placeholder="Select Retry Type"
                item-text="type"
                item-value="id"
                outlined
                clearable
                dense
                v-model="providerError.retry_type_id"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-checkbox
                label="Odds Have Changed"
                v-model="providerError.odds_have_changed"
              ></v-checkbox>
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
          v-if="errorToUpdate"
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
import bus from '../../../../eventBus'
import { required, minLength, maxLength } from 'vuelidate/lib/validators'
import { mapActions } from 'vuex'

export default {
  props: ["update", "errorToUpdate", "generalErrors", "retryTypes"],
  data: () => ({
    providerError: {
      id: null,
      message: '',
      error_message_id: null,
      retry_type_id: null,
      odds_have_changed: false
    }
  }),
  validations: {
    providerError: {
      message: { required, minLength: minLength(2), maxLength: maxLength(255) },
      error_message_id: { required }
    }
  },
  computed: {
    providerMessageErrors() {
      let errors = []
      if(!this.$v.providerError.message.$dirty) return errors
      !this.$v.providerError.message.required && errors.push('Provider message is required.')
      !this.$v.providerError.message.minLength && errors.push('Provider message must have at least 2 characters.')
      !this.$v.providerError.message.maxLength && errors.push('Provider message is up to only 255 characters.')
      return errors
    },
    errorMessageErrors() {
      let errors = []
      if(!this.$v.providerError.error_message_id.$dirty) return errors
      !this.$v.providerError.error_message_id.required && errors.push('Error message is required.')
      return errors
    }
  },
  mounted() {
    this.initializeError()
  },
  methods: {
    ...mapActions('providerErrors', ['manageProviderErrors']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    initializeError() {
      this.resetFields()
      if(this.errorToUpdate) {
        let errorForm = { ...this.errorToUpdate }
        let errorMessageId = this.generalErrors.filter(error => error.error === this.errorToUpdate.error).map(error => error.id)[0]
        this.$set(errorForm, 'error_message_id', errorMessageId)
        this.$delete(errorForm , 'error')
        this.providerError = errorForm
      }
    },
    async addProviderError() {
      try {
        if(!this.$v.providerError.$invalid) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Adding new provider error message..."
          });
          await this.manageProviderErrors(this.providerError)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "A new provider error message has been created."
          });
        } else {
          this.$v.providerError.$touch()
        }
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors.message[0] : err.response.data.message
        });
      }
    },
    async updateProviderError() {
      try {
        if(!this.$v.providerError.$invalid) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Updating provider error message..."
          });
          await this.manageProviderErrors(this.providerError)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Provider error message updated."
          });
        } else {
          this.$v.providerError.$touch()
        }
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors.message[0] : err.response.data.message
        });
      }
    },
    submit() {
      if(this.update) {
        this.updateProviderError()
      } else {
        this.addProviderError()
      }
    },
    resetFields() {
      this.providerError.message = ''
      this.providerError.error_message_id = null
    }
  }
}
</script>

<style>
.formColumn {
  padding: 5px 10px;
}

.providerErrorForm .formColumn .v-input--selection-controls {
  margin: 0;
  padding: 0;
}
</style>
