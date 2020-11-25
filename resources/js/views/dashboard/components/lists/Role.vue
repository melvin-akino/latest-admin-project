<template>
  <fragment>
    <v-list-item-content v-if="!isEditing">
      <v-list-item-title>{{ role.text }}</v-list-item-title>
    </v-list-item-content>
    <v-list-item-content v-else>
      <v-form @submit.prevent="updateRole(role)">
        <v-text-field
          dense
          v-model="$v.updatedRole.$model"
          :error-messages="updatedRoleErrors"
          @input="$v.updatedRole.$touch()"
          @blur="$v.updatedRole.$touch()"
        >
          <template v-slot:append-outer>
            <v-btn icon small @click="isEditing = false">
              <v-icon>mdi-minus-circle</v-icon>
            </v-btn>
            <v-btn icon small color="success" type="submit">
              <v-icon>mdi-check-bold</v-icon>
            </v-btn>
          </template>
        </v-text-field>
      </v-form>
    </v-list-item-content>
    <v-list-item-action v-if="!isEditing">
      <v-btn icon small @click="isEditing = true">
        <v-icon>mdi-pencil</v-icon>
      </v-btn>
    </v-list-item-action>
  </fragment>
</template>

<script>
import { Fragment } from "vue-fragment";
import { required } from "vuelidate/lib/validators";

export default {
  name: "Role",
  components: {
    Fragment
  },
  props: ["role"],
  data: () => ({
    isEditing: false,
    updatedRole: ""
  }),
  validations: {
    updatedRole: { required }
  },
  computed: {
    updatedRoleErrors() {
      let errors = [];
      if (!this.$v.updatedRole.$dirty) return errors;
      !this.$v.updatedRole.required && errors.push("Role is required.");
      return errors;
    }
  },
  watch: {
    isEditing() {
      this.initializeRole();
    }
  },
  mounted() {
    this.initializeRole();
  },
  methods: {
    initializeRole() {
      this.updatedRole = this.role.text;
    },
    updateRole(role) {
      if(!this.$v.updatedRole.$invalid) {
        this.$store.commit("admin/UPDATE_ROLE", { role, updatedRole: this.updatedRole });
        this.isEditing = false
        this.$emit('updateTableRole', this.updatedRole.toLowerCase().split(' ').join('_'))
      } else {
        this.$v.updatedRole.$touch()
      }
    }
  }
};
</script>

<style></style>
