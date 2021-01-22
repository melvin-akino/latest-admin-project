<template>
  <div class="system-configurations pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">System Configurations</p>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
      </v-toolbar>
      <v-form @submit.prevent="updateSystemConfiguration">
        <v-data-table
          :headers="headers"
          :items="systemConfigurations"
          :items-per-page="10"
          :loading="isLoadingSystemConfigurations"
          loading-text="Loading System Configurations"
          :search="search"
        >
          <template v-slot:top>
            <v-toolbar flat color="primary" height="40px" dark>
              <p class="subtitle-1">Total System Configurations: {{ systemConfigurations.length }}</p>
            </v-toolbar>
          </template>
          <template v-slot:[`item.value`]="{ item }">
            <v-text-field
              v-model="systemConfiguration.value"
              :error-messages="valueErrors"
              @input="$v.systemConfiguration.value.$touch()"
              @blur="$v.systemConfiguration.value.$touch()"
              label="Value"
              v-if="toEditRow == item.id"
            ></v-text-field>
            <span v-else>{{item.value}}</span>
          </template>
          <template v-slot:[`item.module`]="{ item }">
            <v-text-field
              v-model="systemConfiguration.module"
              label="Module"
              v-if="toEditRow == item.id"
            ></v-text-field>
            <span v-else>{{item.module}}</span>
          </template>
          <template v-slot:[`item.actions`]="{ item }" class="actions">
            <v-btn v-if="toEditRow == item.id" icon small color="success" type="submit">
              <v-icon>mdi-check-bold</v-icon>
            </v-btn>
            <v-btn v-if="toEditRow == item.id" icon small @click="cancelUpdateSystemConfiguration">
              <v-icon>mdi-undo</v-icon>
            </v-btn>
            <v-tooltip v-if="toEditRow != item.id" bottom>
              <template v-slot:activator="{ on }">
                <v-btn icon @click="editSystemConfiguration(item.id)" v-on="on">
                  <v-icon small>mdi-pencil</v-icon>
                </v-btn>
              </template>
              <span class="caption">Edit</span>
            </v-tooltip>
          </template>
        </v-data-table>
      </v-form>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import bus from '../../../eventBus'
import { required } from 'vuelidate/lib/validators'

export default {
  data: () => ({
    headers: [
      { text: 'TYPE', value: 'type' },
      { text: 'VALUE', value: 'value' },
      { text: 'MODULE', value: 'module' },
      { text: 'ACTION', value: 'actions' }
    ],
    search: '',
    toEditRow: null,
    systemConfiguration: {
      id: '',
      type: '',
      value: '',
      module: ''
    }
  }),
  validations: {
    systemConfiguration: {
      value: { required }
    }
  },
  computed: {
    ...mapState('systemConfigurations', ['systemConfigurations', 'isLoadingSystemConfigurations']),
    configToUpdate() {
      return this.systemConfigurations.filter(config => config.id == this.toEditRow)[0]
    },
    valueErrors() {
      let errors = []
      if(!this.$v.systemConfiguration.value.$dirty) return errors
      !this.$v.systemConfiguration.value.required && errors.push('Value is required.')
      return errors
    }
  },
  mounted() {
    this.getSystemConfigurations()
  },
  methods: {
    ...mapMutations('systemConfigurations', { setSystemConfigurations: 'SET_SYSTEM_CONFIGURATIONS' }),
    ...mapActions('systemConfigurations', ['getSystemConfigurations', 'manageSystemConfiguration']),
    setSystemConfigurationValues() {
      if(this.configToUpdate) {
        Object.keys(this.configToUpdate).map(key => {
          this.$set(this.systemConfiguration, key, this.configToUpdate[key])
        })
      } else {
        Object.keys(this.systemConfiguration).map(key => {
          this.$set(this.systemConfiguration, key, '')
        })
      }
    },
    editSystemConfiguration(id) {
      this.toEditRow = id
      this.setSystemConfigurationValues()
    },
    cancelUpdateSystemConfiguration() {
      this.toEditRow = null
      this.setSystemConfigurationValues()
    },
    async updateSystemConfiguration() {
      if(!this.$v.systemConfiguration.$invalid) {
        try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Updating system configuration..."
          });
          await this.manageSystemConfiguration(this.systemConfiguration)
          this.toEditRow = null
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Updated system configuration!"
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.message
          });
        }
      } else {
        this.$v.systemConfiguration.$touch()
      }
    }
  },
  beforeRouteLeave(to, from, next) {
    this.setSystemConfigurations([])
    next()
  }
}
</script>

<style>
.system-configurations p {
  margin-bottom: 0;
}

.system-configurations .v-toolbar__content {
  padding: 16px;
}
</style>