<template>
  <div class="masterlistMatching pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Masterlist Matching</p>
      </v-toolbar>
      <v-tabs
        hide-slider
        class="mt-4"
      >
        <v-tab to="leagues" @change="changeType('leagues')">
          <v-badge color="error" content="!" :value="hasRawLeagues">
            Leagues
          </v-badge>
        </v-tab>
        <v-tab to="teams" @change="changeType('teams')">
          <v-badge color="error" content="!" :value="hasRawTeams">
            Teams
          </v-badge>
        </v-tab>
        <v-tab to="events" @change="changeType('events')">
          <v-badge color="error" content="!" :value="hasRawEvents">
            Events
          </v-badge>
        </v-tab>
      </v-tabs>
      <v-tabs
        hide-slider
        class="mt-4"
      >
        <v-tab v-for="provider in providers" :key="provider.id" @change="changeProvider(provider.id, provider.alias)">
          <v-badge color="error" :content="provider[`raw_${matchingParams.type}`]" :value="provider[`raw_${matchingParams.type}`]">
            {{provider.alias}}
          </v-badge>
        </v-tab>
      </v-tabs>
      <router-view></router-view>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'

export default {
  name: 'MasterlistMatching',
  computed: {
    ...mapState('providers', ['providers']),
    ...mapState('masterlistMatching', ['options']),
    matchingParams() {
      return { ...this.options }
    },
    hasRawLeagues() {
      return this.checkIfHasRawData('leagues')
    },
    hasRawTeams() {
      return this.checkIfHasRawData('teams')
    },
    hasRawEvents() {
      return this.checkIfHasRawData('events')
    }
  },
  watch: {
    providers(value) {
      if(value.length != 0 && !this.matchingParams.providerId) {
        this.changeProvider(value[0].id, value[0].alias)
      }
    },
    matchingParams: {
      deep: true,
      handler(value) {
        if(value.type && value.providerId) {
          this.getRawData()
        }
      }
    },
    'matchingParams.type'(value, oldValue) {
      if(value) {
        this.getMatchedData()
        this.resetTable(value, oldValue)
      }
    },
    'matchingParams.providerId'(value, oldValue) {
      if(value) {
        this.resetTable(value, oldValue)
      }
    }
  },
  methods: {
    ...mapMutations('masterlistMatching', { setRawData: 'SET_RAW_DATA', setIsLoadingRawData: 'SET_IS_LOADING_RAW_DATA', setMatchedData: 'SET_MATCHED_DATA', setOptions: 'SET_OPTIONS' }),
    ...mapMutations('providers', { setProviders: 'SET_PROVIDERS' }),
    ...mapActions('providers', ['getProviders']),
    ...mapActions('masterlistMatching', ['getRawData', 'getMatchedData']),
    changeType(type) {
      this.setOptions({ option: 'type', data: type})
    },
    changeProvider(id, alias) {
      this.setOptions({ option: 'providerId', data: id})
      this.setOptions({ option: 'provider_alias', data: alias})
    },
    checkIfHasRawData(type) {
      if(this.providers.length != 0) {
        let raw = this.providers.map(provider => provider[`raw_${type}`])
        return raw.some(count => count != 0)
      }
    },
    resetTable(value, oldValue) {
      this.getProviders(true)
      if(value != oldValue) {
        this.setIsLoadingRawData(true)
        this.setRawData([])
      }
    }
  },
  beforeRouteLeave(to, from, next) {
    this.setRawData([])
    this.setMatchedData([])
    this.setProviders([])
    this.setOptions({ option: 'type', data: ''})
    this.setOptions({ option: 'providerId', data: null})
    this.setOptions({ option: 'provider_alias', data: ''})
    this.setOptions({ option: 'page', data: null})
    this.setOptions({ option: 'alias', data: null})
    next()
  }
}
</script>

<style>
  .v-tab--active {
    background-color: #e9954b;
    color: #ffffff !important;
  }
</style>