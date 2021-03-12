<template>
    <v-card class="userForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Manage Bookmaker</v-toolbar-title
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
                label="Name"
                type="text"
                outlined
                dense
                :disabled="update"
                v-model="$v.provider.name.$model"
                :error-messages="nameErrors"
                @input="$v.provider.name.$touch()"
                @blur="$v.provider.name.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="12" class="formColumn">
              <v-text-field
                label="Alias"
                outlined
                dense
                v-model="$v.provider.alias.$model"
                :error-messages="aliasErrors"
                @input="$v.provider.alias.$touch()"
                @blur="$v.provider.alias.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="providerStatus"
                label="Status"
                outlined
                dense
                v-model="$v.provider.is_enabled.$model"
                :error-messages="statusErrors"
                @input="$v.provider.is_enabled.$touch()"
                @blur="$v.provider.is_enabled.$touch()"
              ></v-select>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Punter Percentage"
                type="text"
                outlined
                dense
                v-model="$v.provider.punter_percentage.$model"
                :error-messages="punterPercentageErrors"
                @input="$v.provider.punter_percentage.$touch()"
                @blur="$v.provider.punter_percentage.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="currencies"
                label="Currency"
                item-text="code"
                item-value="id"
                outlined
                dense
                v-model="$v.provider.currency_id.$model"
                :error-messages="currencyErrors"
                @input="$v.provider.currency_id.$touch()"
                @blur="$v.provider.currency_id.$touch()"
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
          v-if="providerToUpdate"
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
import { required, alphaNum, integer, minLength, maxLength } from 'vuelidate/lib/validators'
import { handleAPIErrors } from '../../../../helpers/errors'

export default {
  props: ["update", "providerToUpdate", "currencies"],
  data: () => ({
    provider: {
      id: null,
      name: '',
      alias: '',
      punter_percentage: '',
      is_enabled: true,
      currency_id: 1,
    }
  }),
  validations: {
    provider: {
      name: { required, alphaNum, minLength: minLength(3) },
      alias: { required, alphaNum, maxLength: maxLength(3) },
      punter_percentage: { required, integer },
      is_enabled: { required },
      currency_id: { required }
    }
  },
  computed: {
    ...mapState("providers", ["providerStatus"]),
    nameErrors() {
      let errors = []
      if(!this.$v.provider.name.$dirty) return errors
      !this.$v.provider.name.required && errors.push('Name is required.')
      !this.$v.provider.name.alphaNum && errors.push('Name must be alphanumeric.')
      !this.$v.provider.name.minLength && errors.push('Name should have at least 3 characters.')
      return errors
    },
    aliasErrors() {
      let errors = []
      if(!this.$v.provider.alias.$dirty) return errors
      !this.$v.provider.alias.required && errors.push('Alias is required.')
      !this.$v.provider.alias.alphaNum && errors.push('Alias must be alphanumeric.')
      !this.$v.provider.alias.maxLength && errors.push('Alias should only be up to 3 characters.')
      return errors
    },
    punterPercentageErrors() {
      let errors = []
      if(!this.$v.provider.punter_percentage.$dirty) return errors
      !this.$v.provider.punter_percentage.required && errors.push('Punter percentage is required.')
      !this.$v.provider.punter_percentage.integer && errors.push('Punter percentage must be an integer.')
      return errors
    },
    statusErrors() {
      let errors = []
      if(!this.$v.provider.is_enabled.$dirty) return errors
      !this.$v.provider.is_enabled.required && errors.push('Status is required.')
      return errors
    },
    currencyErrors() {
      let errors = []
      if(!this.$v.provider.currency_id.$dirty) return errors
      !this.$v.provider.currency_id.required && errors.push('Currency is required.')
      return errors
    }

  },
  mounted() {
    this.initializeProvider()
  },
  methods: {
    ...mapActions('providers', ['addProvider', 'updateProvider']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    initializeProvider() {
      this.resetFields()
      if(this.providerToUpdate) {
        let providerForm = { ...this.providerToUpdate }
        this.provider = providerForm
      }
    },
    async addBookmaker() {
      if(!this.$v.provider.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Adding provider..."
          });
          let response = await this.addProvider(this.provider)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: response
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: handleAPIErrors(err)
          });
        }
      } else {
        this.$v.provider.$touch()
      }
    },
    async updateBookmaker() {
      if(!this.$v.provider.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Updating provider..."
          });
          let response = await this.updateProvider(this.provider)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: response
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: handleAPIErrors(err)
          });
        }
      } else {
        this.$v.provider.$touch()
      }
    },
    submit() {
      if(this.update) {
        this.updateBookmaker()
      } else {
        this.addBookmaker()
      }
    },
    resetFields() {
      let fieldsToEmpty = [
        "name",
        "alias",
        "punter_percentage"
      ];
      Object.keys(this.provider).map(key => {
        if (fieldsToEmpty.includes(key)) {
          this.provider[key] = "";
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
