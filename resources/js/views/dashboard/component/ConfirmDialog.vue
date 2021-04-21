<template>
  <v-dialog v-model="dialog" width="500" :activator="activator">
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
      <v-card-text v-if="message">
        {{message}}
      </v-card-text>
      <v-card-text v-else>
        <slot></slot>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn dark right class="red darken-2" @click="cancel"
          >Cancel</v-btn
        >
        <v-btn dark right class="success" @click="confirm">Confirm</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import bus from '../../../eventBus'

export default {
  name: 'ConfirmDialog',
  props: ['title', 'message', 'activator'],
  data: () => ({
    dialog: false
  }),
  mounted() {
    bus.$on('CLOSE_DIALOG', () => {
      this.dialog = false
    })
  },
  methods: {
    cancel() {
      this.dialog = false
    },
    confirm() {
      this.$emit('confirm')
    }
  }
}
</script>

<style>
.v-dialog > .v-card > .v-card__text {
  font-weight: 400;
  font-size: 15px;
}
</style>
