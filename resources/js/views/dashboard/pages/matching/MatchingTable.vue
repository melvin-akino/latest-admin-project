<template>
  <v-card color="transparent" elevation="0">
    <v-card-title>
      <v-spacer></v-spacer>
      <v-text-field
        v-model="search"
        append-icon="mdi-magnify"
        :label="`Search ${dataType}`"
        hide-details
        class="subtitle-1"
        style="max-width: 200px;"
      ></v-text-field>
    </v-card-title>
    <v-data-table
      :headers="headers"
      :items="rawDataTable"
      :search="search"
      :items-per-page="10"
      :loading="isLoadingRawData"
      :loading-text="`Loading ${dataType}`"
      class="mt-4 matchingTable"
    > 
      <template v-slot:item="{ item }">
        <matching-table-row :dataType="dataType" :dataTypeSingular="dataTypeSingular" :item="item" :key="item.id" ></matching-table-row>
      </template>
    </v-data-table>
  </v-card>
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
        { text: 'DATA', value: 'data' },
        { text: 'MATCHES', value: 'matches', sortable: false },
        { text: '', value: 'alias', sortable: false },
        { text: '', value: 'add', sortable: false },
        { text: '', value: 'actions', sortable: false },
      ],
      search: ''
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
    },
    rawDataTable() {
      if(this.rawData.length != 0) {
        let rawDataTable = []
        this.rawData.map(raw => {
          let item = { ...raw }
          if(this.dataType != 'events') {
            this.$set(item, 'data', raw.name)
          } else {
            let event = `${raw.league_name} \n ${raw.team_home_name} \n ${raw.team_away_name} \n ${raw.ref_schedule}`
            this.$set(item, 'data', event)
          }
          rawDataTable.push(item)
        })
        return rawDataTable
      }
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