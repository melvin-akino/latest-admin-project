<template>
  <tr>
    <td class="text-start">
      <div v-if="dataType != 'events'">
        <span>{{item.name}}</span>
      </div>
      <div v-else class="py-2">
        <p class="rawEvent">{{item.league_name}}</p>
        <p class="rawEvent">{{item.team_home_name}}</p>
        <p class="rawEvent">{{item.team_away_name}}</p>
        <p class="rawEvent">{{item.ref_schedule}}</p>
      </div>
    </td>
    <td class="text-start">
      <v-select
        :items="matchedData"
        item-value="id"
        :label="`Select matched ${dataType}`"
        dense
        clearable
        class="input"
        :disabled="matchingForm.add_master"
        @change="populateAlias"
        v-model="$v.matchingForm.primary_provider_id.$model"
        :error-messages="primaryProviderErrors"
        @input="$v.matchingForm.primary_provider_id.$touch()"
        @blur="$v.matchingForm.primary_provider_id.$touch()"
      >
        <template v-slot:selection="{ item }" v-if="dataType != 'events'">
          <span>{{item.name}}</span>
        </template>
        <template v-slot:selection="{ item }" v-else>
          <span>{{item.league_name}} - {{item.team_home_name}} vs {{item.team_away_name}} - {{item.ref_schedule}}</span>
        </template>
        <template v-slot:item="{ item }" v-if="dataType != 'events'">
          <span class="matchOption">{{item.name}}</span>
        </template>
        <template v-slot:item="{ item }" v-else>
          <div class="eventSelection py-2">
            <p class="matchedEvent matchOption">{{item.league_name}}</p>
            <p class="matchedEvent matchOption">{{item.team_home_name}}</p>
            <p class="matchedEvent matchOption">{{item.team_away_name}}</p>
            <p class="matchedEvent matchOption">{{item.ref_schedule}}</p>
          </div>
        </template>
      </v-select>
    </td>
    <td class="text-start">
        <v-text-field
        label="Alias"
        type="text"
        dense
        clearable
        class="input"
        v-if="dataType != 'events'"
        v-model="$v.matchingForm.master_alias.$model"
        :error-messages="aliasErrors"
        @input="$v.matchingForm.master_alias.$touch()"
        @blur="$v.matchingForm.master_alias.$touch()"
      >
      </v-text-field>
    </td>
    <td class="text-start">
      <v-checkbox label="ADD TO MASTER" class="input" v-if="dataType != 'events'" v-model="matchingForm.add_master"></v-checkbox>
    </td>
    <td class="text-start">
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-btn v-on="on" icon small @click="match">
            <v-icon>mdi-arrow-decision</v-icon>
          </v-btn>
        </template>
        <span class="caption">Match {{dataTypeSingular}}</span>
      </v-tooltip>
    </td>
  </tr>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import bus from '../../../../eventBus'
import { requiredIf } from 'vuelidate/lib/validators'

export default {
  props: ['dataType', 'dataTypeSingular', 'item'],
  name: 'MatchingTableRow',
  data() {
    return {
      matchingForm: {
        primary_provider_id: null,
        master_alias: '',
        add_master: false
      }
    }
  },
  validations: {
    matchingForm: {
      primary_provider_id: {
        required: requiredIf(function() {
          return !this.matchingForm.add_master
        })
      },
      master_alias: { 
        required: requiredIf(function() {
          return this.dataType != 'events'
        }) 
      }
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['matchedData']),
    primaryProviderErrors() {
      let errors = []
      if (!this.$v.matchingForm.primary_provider_id.$dirty) return errors
      !this.$v.matchingForm.primary_provider_id.required && errors.push(`Matched ${this.dataTypeSingular} is required.`)
      return errors
    },
    aliasErrors() {
      let errors = []
      if (!this.$v.matchingForm.master_alias.$dirty) return errors
      !this.$v.matchingForm.master_alias.required && errors.push('Alias is required.')
      return errors
    }
  },
  watch: {
    'matchingForm.add_master'(value) {
      if(value) {
        this.matchingForm.primary_provider_id = null
        this.matchingForm.master_alias = ''
      }
    }
  },
  methods: {
    ...mapMutations('providers', { updateProviderCount: 'UPDATE_PROVIDER_COUNT' }),
    ...mapActions('masterlistMatching', ['matchData']),
    initializeParams() {
      let params = {}
      this.$set(params, `primary_provider_${this.dataTypeSingular}_id`, this.matchingForm.primary_provider_id)
      this.$set(params, `match_${this.dataTypeSingular}_id`, this.item.id)
      this.$set(params, `master_${this.dataTypeSingular}_alias`, this.matchingForm.master_alias)
      this.$set(params, `add_master_${this.dataTypeSingular}`, this.matchingForm.add_master)
      return params
    },
    populateAlias() {
      if(this.matchingForm.primary_provider_id) {
        let alias = this.matchedData.filter(data => data.id == this.matchingForm.primary_provider_id)[0].name
        this.matchingForm.master_alias = alias
      }
    },
    async match() {
      try {
        if(!this.$v.matchingForm.$invalid) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: `Matching ${this.dataTypeSingular}...`
          });
          let payload = {
            data: this.initializeParams(),
            type: this.dataType,
            id: this.item.id
          }
          await this.matchData(payload)
          this.updateProviderCount({ type: this.dataType, provider_id: this.item.provider_id })
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: `Matched ${this.dataTypeSingular} successfully.`
          });
        } else {
          this.$v.matchingForm.$touch()
        }

      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.errors[0] || err.response.data.message
        });
      }
    }
  }
}
</script>

<style>
  .matchingTable .rawEvent, .eventSelection .matchedEvent {
    margin: 0;
    padding: 0;
  }

  .matchOption {
    font-size: 13px !important;
  }

  .eventSelection {
    flex-direction: column !important;
    align-items: flex-start !important;
  }
</style>