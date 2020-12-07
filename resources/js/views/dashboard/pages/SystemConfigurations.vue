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
            <v-btn v-if="toEditRow != item.id" icon @click="editSystemConfiguration(item.id)">
              <v-icon small>mdi-pencil</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-form>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import bus from '../../../eventBus'

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
  computed: {
    ...mapState('systemConfigurations', ['systemConfigurations', 'isLoadingSystemConfigurations']),
    configToUpdate() {
      return this.systemConfigurations.filter(config => config.id == this.toEditRow)[0]
    }
  },
  mounted() {
    this.getSystemConfigurations()
  },
  methods: {
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
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Updating system configuration..."
        });
        await this.manageSystemConfiguration(this.systemConfiguration)
        this.toEditRow = null
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Updating system configuration!"
        });
      } catch(err) {
        console.log(err)
      }
    }
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