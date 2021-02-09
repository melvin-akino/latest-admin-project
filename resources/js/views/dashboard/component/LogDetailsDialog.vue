<template>
  <v-dialog v-model="dialog" width="500">
    <template v-slot:activator="{ on: dialog }">
      <v-tooltip bottom>
        <template v-slot:activator="{ on: tooltip }">
          <span span class="logDetailsText" v-on="{...dialog, ...tooltip }">{{description}}</span>
        </template>
        <span class="caption">View Log Details</span>
      </v-tooltip>
    </template>
    <v-card>
      <v-toolbar color="primary" dark height="40px">
        <v-toolbar-title class="text-uppercase subtitle-1"
          >{{title}}</v-toolbar-title
        >
        <v-spacer></v-spacer>
        <v-btn @click="cancel" icon>
          <v-icon dark>mdi-close-circle</v-icon>
        </v-btn>
      </v-toolbar>
      <v-card-text>
        {{description}}
        <ul>
          <li v-for="(key, index) in keys" :key="index">
            {{key}}: <span v-if="log.old_data && log.old_data[key]">{{log.old_data[key]}} <v-icon color="success" small>mdi-arrow-right-bold</v-icon></span> {{log.new_data[key]}}
          </li>
        </ul>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import bus from '../../../eventBus'

export default {
  name: 'LogDetailsDialog',
  props: ['title', 'description', 'log'],
  data: () => ({
    dialog: false
  }),
  mounted() {
    bus.$on('CLOSE_DIALOG', () => {
      this.dialog = false
    })
  },
  computed: {
    keys() {
      if(this.log.old_data && this.log.new_data) {
        let objectKeys = Object.keys(this.log.old_data).concat(Object.keys(this.log.new_data))
        return objectKeys.filter((value, index, self) => self.indexOf(value) === index)
      } else {
        return Object.keys(this.log.new_data)
      }
    }
  },
  methods: {
    cancel() {
      this.dialog = false
    }
  }
}
</script>

<style>
.v-dialog > .v-card > .v-card__text {
  font-weight: 400;
  font-size: 15px;
  padding: 10px;
}

.logDetailsText {
  cursor: pointer;
}

.logDetailsText:hover {
  text-decoration: underline;
}
</style>
