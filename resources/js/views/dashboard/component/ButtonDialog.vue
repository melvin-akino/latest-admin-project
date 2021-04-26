<template>
  <v-dialog v-model="dialog" :width="width" :activator="activator ? activator : undefined">
    <template v-slot:activator="{ on }" v-if="!activator">
      <v-btn class="ml-3 mt-2" depressed elevation="2" color="primary" dark small v-on="on" @click="clearFilters">
        <v-icon left v-if="icon">{{icon}}</v-icon>
        <span class="caption" v-if="label">{{label}}</span>
      </v-btn>
    </template>
    <slot v-if="dialog"></slot>
  </v-dialog>
</template>

<script>
import bus from '../../../eventBus'

export default {
  name: 'ButtonDialog',
  props: ['icon', 'label', 'width', 'activator'],
  data: () => ({
    dialog: false
  }),
  mounted() {
    bus.$on('CLOSE_DIALOG', () => {
      this.dialog = false
    })
  },
  methods: {
    clearFilters() {
      this.$emit('clearFilters')
    }
  }
}
</script>
