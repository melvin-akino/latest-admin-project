<template>
  <v-list-group
    :group="group"
    :prepend-icon="item.icon"
    :sub-group="subGroup"
    append-icon="mdi-menu-down"
    :color="barColor !== 'rgba(255, 255, 255, 1), rgba(255, 255, 255, 0.7)' ? 'white' : 'grey darken-1'"
    class="item-group"
  >
    <template v-slot:activator>
      <v-list-item-icon
        v-if="text"
        class="v-list-item__icon--text"
        v-text="computedText"
      />

      <v-list-item-avatar
        v-else-if="item.avatar"
        class="align-self-center"
        color="white"
        contain
      >
        <v-img src="https://demos.creative-tim.com/vuetify-material-dashboard/favicon.ico" />
      </v-list-item-avatar>

      <v-list-item-content>
        <v-list-item-title v-text="item.title" />
      </v-list-item-content>
    </template>

    <template v-for="(child, i) in children">
      <base-item-sub-group
        v-if="child.children"
        :key="`sub-group-${i}`"
        :item="child"
      />

      <base-item
        v-else
        :key="`item-${i}`"
        :item="child"
        :title="child.title"
        :hasClickEvent="child.hasClickEvent"
        text
      />
    </template>
    <button-dialog activator=".listClickedReprocess" :width="500">
      <v-card>
        <v-toolbar color="primary" dark height="40px">
          <v-toolbar-title class="text-uppercase subtitle-1"
            >Confirm Reprocess Data</v-toolbar-title
          >
          <v-spacer></v-spacer>
          <v-btn @click="closeDialog" icon>
            <v-icon dark>mdi-close-circle</v-icon>
          </v-btn>
        </v-toolbar>
        <v-card-text class="text-center">
          <v-progress-circular
            indeterminate
            color="success"
            :size="50"
            v-if="isSubmitting"
          ></v-progress-circular>
          <p class="font-weight-regular text-center mt-4">{{ isSubmitting ? 'Reprocessing data...' : 'Click "Confirm" to proceed data re-processing...' }}</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn dark right class="red darken-2" @click="closeDialog"
            >Cancel</v-btn
          >
          <v-btn dark right class="success" @click="reProcess">Confirm</v-btn>
        </v-card-actions>
      </v-card>
    </button-dialog>
    <button-dialog activator=".listClickedClearDuplicates" :width="500">
      <v-card>
        <v-toolbar color="primary" dark height="40px">
          <v-toolbar-title class="text-uppercase subtitle-1"
            >Confirm Clear Duplicates</v-toolbar-title
          >
          <v-spacer></v-spacer>
          <v-btn @click="closeDialog" icon>
            <v-icon dark>mdi-close-circle</v-icon>
          </v-btn>
        </v-toolbar>
        <v-card-text class="text-center">
          <v-progress-circular
            indeterminate
            color="success"
            :size="50"
            v-if="isSubmitting"
          ></v-progress-circular>
          <p class="font-weight-regular text-center mt-4">{{ isSubmitting ? 'Clearing duplicates...' : 'Click "Confirm" to proceed clearing duplicates...' }}</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn dark right class="red darken-2" @click="closeDialog"
            >Cancel</v-btn
          >
          <v-btn dark right class="success" @click="clearDuplicates">Confirm</v-btn>
        </v-card-actions>
      </v-card>
    </button-dialog>
  </v-list-group>
</template>

<script>
  // Utilities
  import kebabCase from 'lodash/kebabCase'
  import { mapState } from 'vuex'
  import bus from '../../eventBus'
  import { getToken } from '../../helpers/token'
  import { mapActions } from 'vuex'

  export default {
    name: 'ItemGroup',

    components: {
      ButtonDialog: () => import('../../views/dashboard/component/ButtonDialog')
    },
    inheritAttrs: false,

    props: {
      item: {
        type: Object,
        default: () => ({
          avatar: undefined,
          group: undefined,
          title: undefined,
          children: [],
        }),
      },
      subGroup: {
        type: Boolean,
        default: false,
      },
      text: {
        type: Boolean,
        default: false,
      },
    },

    data() {
      return {
        isSubmitting: false
      }
    },

    computed: {
      ...mapState(['barColor']),
      children () {
        return this.item.children.map(item => ({
          ...item,
          to: !item.to ? undefined : `${this.item.group}/${item.to}`,
        }))
      },
      computedText () {
        if (!this.item || !this.item.title) return ''

        let text = ''

        this.item.title.split(' ').forEach(val => {
          text += val.substring(0, 1)
        })

        return text
      },
      group () {
        return this.genGroup(this.item.children)
      },
    },

    methods: {
      ...mapActions('auth', ['logoutOnError']),
      closeDialog() {
        bus.$emit('CLOSE_DIALOG')
      },
      reProcess() {
        this.isSubmitting = true
        axios.get('matching/reprocess', { headers: { 'Authorization': `Bearer ${getToken()}` } })
        .then(response => {
          this.closeDialog()
          this.isSubmitting = false
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: response.data.message
          });
        })
        .catch(err => {
          if(!axios.isCancel(err)) {
            this.logoutOnError(err.response.status)
            bus.$emit("SHOW_SNACKBAR", {
              color: "error",
              text: err.response.data.message
            });
          }
        })
      },
      clearDuplicates() {
        this.isSubmitting = true
        axios.get('matching/clear-duplicates', { headers: { 'Authorization': `Bearer ${getToken()}` } })
        .then(response => {
          this.closeDialog()
          this.isSubmitting = false
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: response.data.message
          });
        })
        .catch(err => {
          if(!axios.isCancel(err)) {
            this.logoutOnError(err.response.status)
            bus.$emit("SHOW_SNACKBAR", {
              color: "error",
              text: err.response.data.message
            });
          }
        })
      },
      genGroup (children) {
        return children
          .filter(item => item.to)
          .map(item => {
            const parent = item.group || this.item.group
            let group = `${parent}/${kebabCase(item.to)}`

            if (item.children) {
              group = `${group}|${this.genGroup(item.children)}`
            }

            return group
          }).join('|')
      },
    },
  }
</script>

<style>
.v-list-group__activator p {
  margin-bottom: 0;
}
</style>
