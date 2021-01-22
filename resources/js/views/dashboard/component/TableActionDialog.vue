<template>
  <v-dialog v-model="dialog" :width="width">
    <template v-slot:activator="{ on: dialog }">
      <v-tooltip bottom>
        <template v-slot:activator="{ on: tooltip }">
          <v-btn icon v-on="{ ...dialog, ...tooltip}" @click="clearFilters">
              <v-icon small>{{ icon }}</v-icon>
          </v-btn>
        </template>
        <span class="caption">{{tooltipText}}</span>
      </v-tooltip>
    </template>
    <slot v-if="dialog"></slot>
  </v-dialog>
</template>

<script>
import bus from '../../../eventBus'

export default {
  name: 'TableActionDialog',
  props: ['icon', 'width', 'tooltipText'],
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
