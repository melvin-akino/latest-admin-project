<template>
    <v-card class="adminForm">
    <v-toolbar color="primary" dark height="40px">
      <v-toolbar-title class="text-uppercase subtitle-1"
        >Manage Admin User</v-toolbar-title
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
                v-model="$v.admin.email.$model"
                :error-messages="emailErrors"
                @input="$v.admin.email.$touch()"
                @blur="$v.admin.email.$touch()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Password"
                type="password"
                outlined
                dense
                v-model="$v.admin.password.$model"
                :error-messages="passwordErrors"
                @input="$v.admin.password.$touch()"
                @blur="$v.admin.password.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Name"
                type="text"
                outlined
                dense
                v-model="$v.admin.name.$model"
                :error-messages="nameErrors"
                @input="$v.admin.name.$touch()"
                @blur="$v.admin.name.$touch()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="adminStatus"
                label="Status"
                outlined
                dense
                v-model="$v.admin.status.$model"
                :error-messages="statusErrors"
                @input="$v.admin.status.$touch()"
                @blur="$v.admin.status.$touch()"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <!--<v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="adminRoles"
                label="Role"
                value="Admin"
                item-text="text"
                item-value="value"
                outlined
                dense
                v-model="$v.admin.role.$model"
                :error-messages="roleErrors"
                @input="$v.admin.role.$touch()"
                @blur="$v.admin.role.$touch()"
              ></v-select>
            </v-col> -->
          </v-row>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn dark right class="red darken-2" @click="closeDialog"
          >Cancel</v-btn
        >
        <v-btn
          v-if="adminToUpdate"
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
import moment from 'moment'
import { required, requiredIf, email, minLength } from 'vuelidate/lib/validators'

export default {
  props: ["update", "adminToUpdate"],
  data: () => ({
    admin: {
      id: null,
      email: '',
      password: '',
      name: '',
      // role: 'admin',
      status: 1,
    }
  }),
  validations: {
    admin: {
      email: { required, email },
      password: {
        required: requiredIf(function() {
          return !this.update
        }),
        minLength: minLength(6)
      },
      name: { required },
      // role: { required },
      status: { required }
    }
  },
  computed: {
    ...mapState("admin", ["adminRoles", "adminStatus"]),
    adminUser() {
      let admin = { ...this.admin }
      if(this.adminToUpdate && !this.admin.password) {
        this.$delete(admin, 'password')
      }
      return admin
    },
    emailErrors() {
      let errors = []
      if(!this.$v.admin.email.$dirty) return errors
      !this.$v.admin.email.required && errors.push('Email is required.')
      !this.$v.admin.email.email && errors.push('Email must be valid.')
      return errors
    },
    passwordErrors() {
      let errors = []
      if(!this.$v.admin.password.$dirty) return errors
      !this.$v.admin.password.required && errors.push('Password is required.')
      !this.$v.admin.password.minLength && errors.push('Password should have at least 6 characters.')
      return errors
    },
    nameErrors() {
      let errors = []
      if (!this.$v.admin.name.$dirty) return errors
      !this.$v.admin.name.required && errors.push('Name is required.')
      return errors
    },
    // roleErrors() {
    //   let errors = []
    //   if (!this.$v.admin.role.$dirty) return errors
    //   !this.$v.admin.role.required && errors.push('Role is required.')
    //   return errors
    // },
    statusErrors() {
      let errors = []
      if (!this.$v.admin.status.$dirty) return errors
      !this.$v.admin.status.required && errors.push('Status is required.')
      return errors
    }
  },
  mounted() {
    this.initializeAdmin()
  },
  methods: {
    ...mapActions("admin", ["manageAdminUser"]),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG");
    },
    initializeAdmin() {
      this.resetFields()
      if(this.adminToUpdate) {
        let adminForm = { ...this.adminToUpdate }
        this.$set(adminForm, 'password', '')
        this.admin = adminForm
      }
    },
    async addAdmin() {
      if(!this.$v.admin.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Creating a new admin account..."
          });
          await this.manageAdminUser(this.adminUser)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "A new admin account has been created."
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors.email[0] : err.response.data.message
          });
        }
      } else {
        this.$v.admin.$touch()
      }
    },
    async updateAdmin() {
      if(!this.$v.admin.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Updating admin user account..."
          });
          await this.manageAdminUser(this.adminUser)
          this.closeDialog()
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Admin details were updated."
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors.email[0] : err.response.data.message
          });
        }
      } else {
        this.$v.admin.$touch()
      }
    },
    submit() {
      if(this.update) {
        this.updateAdmin()
      } else {
        this.addAdmin()
      }
    },
    resetFields() {
      let fieldsToEmpty = [
        "email",
        "password",
        "name"
      ];
      Object.keys(this.admin).map(key => {
        if (fieldsToEmpty.includes(key)) {
          this.admin[key] = "";
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
