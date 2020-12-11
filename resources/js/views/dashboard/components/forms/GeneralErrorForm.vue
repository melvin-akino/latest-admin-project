<template>
  <v-card class="generalErrorForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Manage Error Message</v-toolbar-title
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
                label="Message"
                type="text"
                outlined
                dense
                v-model="$v.error.error.$model"
                :error-messages="errorErrors"
                @input="$v.error.error.$touch()"
                @blur="$v.error.error.$touch()"
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
  props: ["update", "errorToUpdate"],
  data: () => ({
    error: {
      id: null,
      error: ''
    }
  }),
  validations: {
    error: {
      error: { required, minLength: minLength(2), maxLength: maxLength(255) }
    }
  },
  computed: {
    errorErrors() {
      let errors = []
      if(!this.$v.error.error.$dirty) return errors
      !this.$v.error.error.required && errors.push('Message is required.')
      !this.$v.error.error.minLength && errors.push('Message must have at least 2 characters.')
      !this.$v.error.error.maxLength && errors.push('Message is up to only 255 characters.')
      return errors
    },
  },
  mounted() {
    this.initializeError()
  },
  methods: {
    ...mapActions('generalErrors', ['manageGeneralErrors']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    initializeError() {
      this.resetFields()
      if(this.errorToUpdate) {
        let errorForm = { ...this.errorToUpdate }
        this.error = errorForm
      }
    },
    async addError() {
      try {
        if(!this.$v.error.$invalid) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Adding new error message..."
          });
          await this.manageGeneralErrors(this.error)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "A new error message has been created."
          });
        } else {
          this.$v.error.$touch()
        }
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors.error[0] : err.response.data.message
        });
      }
    },
    async updateError() {
      try {
        if(!this.$v.error.$invalid) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Updating error message..."
          });
          await this.manageGeneralErrors(this.error)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Error message updated."
          });
        } else {
          this.$v.error.$touch()
        }
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors.error[0] : err.response.data.message
        });
      }
    },
    submit() {
      if(this.update) {
        this.updateError()
      } else {
        this.addError()
      }
    },
    resetFields() {
      this.error.error = ''
    }
  }
}
</script>

<style>
.formColumn {
  padding: 0px 10px;
}
</style>
