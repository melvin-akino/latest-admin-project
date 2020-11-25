<template>
    <v-card class="userForm">
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
                label="First Name"
                type="text"
                outlined
                dense
                v-model="$v.admin.first_name.$model"
                :error-messages="firstNameErrors"
                @input="$v.admin.first_name.$touch()"
                @blur="$v.admin.first_name.$touch()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-text-field
                label="Last Name"
                type="text"
                outlined
                dense
                v-model="$v.admin.last_name.$model"
                :error-messages="lastNameErrors"
                @input="$v.admin.last_name.$touch()"
                @blur="$v.admin.last_name.$touch()"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="6" class="formColumn">
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
            </v-col>
            <v-col cols="12" md="6" class="formColumn">
              <v-select
                :items="adminStatus"
                label="Status"
                value="Active"
                outlined
                dense
                v-model="$v.admin.status.$model"
                :error-messages="statusErrors"
                @input="$v.admin.status.$touch()"
                @blur="$v.admin.status.$touch()"
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
import { mapState } from 'vuex'
import bus from '../../../../eventBus'
import moment from 'moment'
import { required, requiredIf, email, minLength } from 'vuelidate/lib/validators'

export default {
  props: ["update", "adminToUpdate"],
  data: () => ({
    admin: {
      email: '',
      password: '',
      first_name: '',
      last_name: '',
      role: 'admin',
      status: 'Active',
      created_date: moment().format('YYYY-MM-DD HH:mm:ss'),
      last_access_date: '-'
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
      first_name: { required },
      last_name: { required },
      role: { required },
      status: { required }
    }
  },
  computed: {
    ...mapState("admin", ["adminRoles", "adminStatus"]),
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
    firstNameErrors() {
      let errors = []
      if (!this.$v.admin.first_name.$dirty) return errors
      !this.$v.admin.first_name.required && errors.push('First name is required.')
      return errors
    },
    lastNameErrors() {
      let errors = []
      if (!this.$v.admin.last_name.$dirty) return errors
      !this.$v.admin.last_name.required && errors.push('Last name is required.')
      return errors
    },
    roleErrors() {
      let errors = []
      if (!this.$v.admin.role.$dirty) return errors
      !this.$v.admin.role.required && errors.push('Role is required.')
      return errors
    },
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
    addAdmin() {
      if(!this.$v.admin.$invalid) {
        this.$store.commit('admin/ADD_ADMIN', this.admin)
        this.closeDialog()
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "A new admin has been created."
        });
      } else {
        this.$v.admin.$touch()
      }
    },
    updateAdmin() {
      if(!this.$v.admin.$invalid) {
        this.$store.commit('admin/UPDATE_ADMIN', this.admin)
        this.closeDialog()
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Admin details were updated."
        });
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
        "first_name",
        "last_name",
      ];
      Object.keys(this.admin).map(key => {
        if (fieldsToEmpty.includes(key)) {
          this.admin[key] = "";
        }
      });
      this.admin.role = 'Admin'
      this.admin.status = 'Active'
    }
  }
}
</script>

<style>
.formColumn {
  padding: 0px 10px;
}
</style>
