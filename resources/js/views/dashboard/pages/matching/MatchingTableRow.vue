<template>
  <tr>
    <td class="text-start">
     <div class="data py-2">
        <span>{{item.data}}</span>
      </div>
    </td>
    <td class="text-start">
      <v-autocomplete
        :items="matchedDataSelection"
        item-value="id"
        item-text="data"
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
      </v-autocomplete>
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
    },
    matchedDataSelection() {
      if(this.matchedData.length != 0) {
        let selection = []
        this.matchedData.map(matched => {
          let item = { ...matched }
          if(this.dataType != 'events') {
            this.$set(item, 'data', matched.name)
          } else {
            let event = ''
            if(this.matchingForm.primary_provider_id == matched.id) {
              event = `${matched.league_name} - ${matched.team_home_name} vs ${matched.team_away_name} (${matched.ref_schedule})`
            } else {
              event = `${matched.league_name} \n ${matched.team_home_name} \n ${matched.team_away_name} \n ${matched.ref_schedule}`
            }
            this.$set(item, 'data', event)
          }
          selection.push(item)
        })
        return selection
      }
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
  .matchingTable .rawEvent {
    margin: 0;
    padding: 0;
  }

  .v-list-item__title, .data {
    white-space: pre-line;
  }
</style>