<template>
  <v-dialog v-model="dialog" :width="width ? width: 500" :activator="activator" @click:outside="cancel">
    <v-card>
      <v-toolbar color="primary" dark height="40px">
        <v-toolbar-title class="text-uppercase subtitle-1"
          >{{title ? title : confirmMessage}}</v-toolbar-title
        >
        <v-spacer></v-spacer>
        <v-btn @click="cancel" icon>
          <v-icon dark>mdi-close-circle</v-icon>
        </v-btn>
      </v-toolbar>
      <v-card-text v-if="message">
        {{message}}
      </v-card-text>
      <v-card-text v-else-if="matching && secondaryProvider && primaryProvider">
        <div class="matchSummary">
          <p class="mb-2">Are you sure you want to <span>{{ matchingType == 'match' ? 'match' : 'unmatch' }}</span> these {{type}}?</p>
          <div v-if="type=='leagues'">
            <table class="matchConfirmDetails">
              <tr>
                <td width="40%" class="primaryData"><p><span>{{primaryProvider.provider}}</span></p></td>
                <td width="20%" class="label"><p>provider</p></td>
                <td width="40%" class="secondaryData"><p><span>{{secondaryProvider.provider}}</span></p></td>
              </tr>
              <tr>
                <td width="40%" class="primaryData"><p><span>{{primaryProvider.name}}</span></p></td>
                <td width="20%" class="label"><p>name</p></td>
                <td width="40%" class="secondaryData"><p><span>{{secondaryProvider.name}}</span></p></td>
              </tr>
            </table>
          </div>
          <div v-else>
            <table class="matchConfirmDetails">
              <tr>
                <td width="40%" class="primaryData"><p><span>{{primaryProvider.provider}}</span></p></td>
                <td width="20%" class="label"><p>provider</p></td>
                <td width="40%" class="secondaryData"><p><span>{{secondaryProvider.provider}}</span></p></td>
              </tr>
              <tr>
                <td width="40%" class="primaryData"><p><span>{{primaryProvider.event_identifier}}</span></p></td>
                <td width="20%" class="label"><p>event id</p></td>
                <td width="40%" class="secondaryData"><p><span>{{secondaryProvider.event_identifier}}</span></p></td>
              </tr>
              <tr>
                <td width="40%" class="primaryData"><p><span>{{primaryProvider.league_name}}</span></p></td>
                <td width="20%" class="label"><p>league</p></td>
                <td width="40%" class="secondaryData"><p><span>{{secondaryProvider.league_name}}</span></p></td>
              </tr>
              <tr>
                <td width="40%" class="primaryData"><p><span>{{primaryProvider.team_home_name}}</span></p></td>
                <td width="20%" class="label"><p>home team</p></td>
                <td width="40%" class="secondaryData"><p><span>{{secondaryProvider.team_home_name}}</span></p></td>
              </tr>
              <tr>
                <td width="40%" class="primaryData"><p><span>{{primaryProvider.team_away_name}}</span></p></td>
                <td width="20%" class="label"><p>away team</p></td>
                <td width="40%" class="secondaryData"><p><span>{{secondaryProvider.team_away_name}}</span></p></td>
              </tr>
              <tr>
                <td width="40%" class="primaryData"><p><span>{{primaryProvider.ref_schedule}}</span></p></td>
                <td width="20%" class="label"><p>ref schedule</p></td>
                <td width="40%" class="secondaryData"><p><span>{{secondaryProvider.ref_schedule}}</span></p></td>
              </tr>
            </table>
          </div>
        </div>
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
  props: ['title', 'message', 'activator', 'matching', 'type', 'width'],
  data: () => ({
    dialog: false,
    secondaryProvider: null,
    primaryProvider: null,
    confirmMessage: '',
    matchingType: ''
  }),
  mounted() {
    bus.$on('OPEN_MATCHING_DIALOG', ({ secondaryProvider, primaryProvider, confirmMessage, matchingType }) => {
      this.dialog = true
      this.secondaryProvider = secondaryProvider
      this.primaryProvider = primaryProvider
      this.confirmMessage = confirmMessage
      this.matchingType = matchingType
    })

    bus.$on('CLOSE_DIALOG', () => {
      this.dialog = false
    })
  },
  methods: {
    cancel() {
      this.dialog = false
      this.$emit('close')
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

.matchConfirmDetails {
  table-layout: fixed;
  width: 100%;
}

.matchConfirmDetails td {
  word-wrap: break-word;
}

.primaryData {
  text-align: right;
}

.label {
  text-align: center;
}

.label p {
  text-transform: uppercase;
  color:  #C6C6C6;
}

.secondaryData {
  text-align: left;
}
</style>
