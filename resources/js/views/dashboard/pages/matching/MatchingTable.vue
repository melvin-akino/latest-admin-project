<template>
  <v-data-table
    :headers="headers"
    :items="rawData"
    :items-per-page="10"
    :loading="isLoadingRawData"
    :loading-text="`Loading ${dataType}`"
    class="mt-4 matchingTable"
  > 
    <template v-slot:item="{ item, index }">
      <matching-table-row :dataType="dataType" :dataTypeSingular="dataTypeSingular" :item="item" :key="item.id" ></matching-table-row>
    </template>
  </v-data-table>
</template>

<script>
import { mapState } from 'vuex'

export default {
  name: 'MatchingTable',
  components: {
    MatchingTableRow: () => import("./MatchingTableRow"),
  },
  data() {
    return {
      headers: [
        { text: 'NAME', value: 'name' },
        { text: 'MATCHES', value: 'matches', sortable: false },
        { text: '', value: 'alias', sortable: false },
        { text: '', value: 'add', sortable: false },
        { text: '', value: 'actions', sortable: false },
      ]
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['rawData', 'isLoadingRawData']),
    dataType() {
      let path = this.$route.path.split('/')
      return path[2].charAt(0) + path[2].slice(1)
    },
    dataTypeSingular() {
      return this.dataType.slice(0, -1)
    }
  }
}
</script>

<style>
  .input {
    margin-top: 20px;
  }
  
  .matchingTable .v-label {
    font-size: 13px;
  }
</style>