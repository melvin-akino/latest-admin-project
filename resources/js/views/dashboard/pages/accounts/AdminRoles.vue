<template>
  <div class="adminRoles pa-6">
    <v-container>
      <p class="text-h4 text-uppercase">Manage Roles for {{ role }}</p>
      <v-row>
        <v-col cols="4">
          <v-card tile>
            <v-card-text>
              <v-list>
                <v-subheader class="subtitle-1 text-uppercase"
                  >Roles</v-subheader
                >
                <v-list-item-group v-model="role" color="primary" mandatory>
                  <v-list-item
                    v-for="(role, index) in adminRoles"
                    :key="index"
                    :value="role.value"
                  >
                    <role :role="role" @updateTableRole="updateTableRole"></role>
                  </v-list-item>
                </v-list-item-group>
                <v-list-item v-if="addRole" class="addRole">
                  <v-form @submit.prevent="addNewRole">
                    <v-text-field
                      label="Add New Role"
                      dense
                      v-model="$v.newRole.$model"
                      :error-messages="newRoleErrors"
                      @input="$v.newRole.$touch()"
                      @blur="$v.newRole.$touch()"
                    >
                      <template v-slot:append-outer>
                        <v-btn icon small @click="cancelAddRole">
                          <v-icon>mdi-undo</v-icon>
                        </v-btn>
                        <v-btn icon small color="success" type="submit">
                          <v-icon>mdi-check-bold</v-icon>
                        </v-btn>
                      </template>
                    </v-text-field>
                  </v-form>
                </v-list-item>
                <v-list-item v-if="!addRole">
                  <v-list-item-icon>
                    <v-btn icon small @click="addRole = true">
                      <v-icon>mdi-plus-circle-outline</v-icon>
                    </v-btn>
                  </v-list-item-icon>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="8">
          <v-simple-table class="roleTable">
            <template v-slot:default>
              <thead>
                <tr>
                  <td>Feature</td>
                  <td>Create</td>
                  <td>Modify</td>
                  <td>View</td>
                  <td>Delete</td>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(feature, index) in rolesSettings[role]"
                  :key="index"
                >
                  <td class="text-capitalize">{{ index | formatFeature }}</td>
                  <td>
                    <v-checkbox
                      v-model="feature.create"
                      @change="
                        updateRoleAction(role, index, 'create', feature.create)
                      "
                    ></v-checkbox>
                  </td>
                  <td>
                    <v-checkbox
                      v-model="feature.modify"
                      @change="
                        updateRoleAction(role, index, 'modify', feature.modify)
                      "
                    ></v-checkbox>
                  </td>
                  <td>
                    <v-checkbox
                      v-model="feature.view"
                      @change="
                        updateRoleAction(role, index, 'view', feature.view)
                      "
                    ></v-checkbox>
                  </td>
                  <td>
                    <v-checkbox
                      v-model="feature.delete"
                      @change="
                        updateRoleAction(role, index, 'delete', feature.delete)
                      "
                    ></v-checkbox>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import { mapState } from "vuex";
import { required } from "vuelidate/lib/validators";

export default {
  name: "AdminRoles",
  components: {
    Role: () => import("../../components/lists/Role")
  },
  data: () => ({
    role: "admin",
    addRole: false,
    newRole: ""
  }),
  validations: {
    newRole: { required }
  },
  computed: {
    ...mapState("admin", ["adminRoles", "rolesSettings"]),
    newRoleErrors() {
      let errors = [];
      if (!this.$v.newRole.$dirty) return errors;
      !this.$v.newRole.required && errors.push("New role is required.");
      return errors;
    }
  },
  methods: {
    updateTableRole(role) {
      this.role = role
    },
    addNewRole() {
      if (!this.$v.newRole.$invalid) {
        this.$store.commit("admin/ADD_NEW_ROLE", this.newRole);
        this.addRole = false;
        this.newRole = "";
        this.$v.$reset();
      } else {
        this.$v.newRole.$touch();
      }
    },
    updateRoleAction(role, feature, action, value) {
      this.$store.commit("admin/UPDATE_ROLE_ACTION", {
        role,
        feature,
        action,
        value
      });
    },
    cancelAddRole() {
      this.$v.$reset();
      this.addRole = false;
      this.newRole = "";
    }
  },
  filters: {
    formatFeature(value) {
      value = value.split("_");
      value = value.join(" ");
      return value;
    }
  }
};
</script>

<style>
.adminRoles p {
  margin-bottom: 0;
}

.adminRoles .v-card__text,
.adminRoles .v-list {
  padding: 0;
}

.roleTable {
  margin-top: 30px;
}

.addRole {
  margin-top: 10px;
}
</style>
