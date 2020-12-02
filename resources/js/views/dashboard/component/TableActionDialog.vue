<template>
  <v-dialog v-model="dialog" :width="width">
    <template v-slot:activator="{ on }">
      <v-btn icon v-on="on" @click="clearFilters">
          <v-icon small>{{ icon }}</v-icon>
      </v-btn>
    </template>
    <slot v-if="dialog"></slot>
  </v-dialog>
</template>

<script>
import bus from '../../../eventBus'

export default {
  name: 'TableActionDialog',
  props: ['icon', 'width'],
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
