<template>
  <div class="users pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Accounts</p>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Accounts"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
        <button-dialog icon="mdi-plus" label="New Account" width="600">
          <user-form :update="false"></user-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="usersTable"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingUsers"
        loading-text="Loading Users"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Accounts: {{ usersTable.length }}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.credits`]="{ item }">
          <span v-if="!item.hasOwnProperty('credits')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{item.credits}}</span>    
        </template>
        <template v-slot:[`item.currency`]="{ item }">
          <span v-if="!item.hasOwnProperty('currency')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{item.currency}}</span>    
        </template>
        <template v-slot:[`item.open_bets`]="{ item }">
          <span v-if="!item.hasOwnProperty('open_bets')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{item.open_bets}}</span>    
        </template>
        <template v-slot:[`item.last_bet`]="{ item }">
          <span v-if="!item.hasOwnProperty('last_bet')">
            <v-progress-circular
              indeterminate
              color="#5b5a58"
              :size="15"
              :width="1"
            ></v-progress-circular>
          </span>    
          <span v-else>{{item.last_bet}}</span>    
        </template>
        <template v-slot:[`item.status`]="{ item }">
          <v-select :items="userStatus" dense v-model="item.status" @change="updateUserStatus(item)"></v-select>
        </template>
        <template v-slot:[`item.actions`]="{ item }" class="actions">
          <table-action-dialog icon="mdi-pencil" width="600">
            <user-form :update="true" :user-to-update="item"></user-form>
          </table-action-dialog>
          <!-- <table-action-dialog icon="mdi-currency-gbp" width="600">
            <wallet-form :user-to-update="item"></wallet-form>
          </table-action-dialog> -->
          <v-btn icon :to="`users/transactions/${item.id}`" target="_blank">
            <v-icon small>mdi-format-list-bulleted</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapGetters, mapMutations, mapActions } from "vuex";
import bus from '../../../../eventBus'

export default {
  name: "Users",
  components: {
    ButtonDialog: () => import("../../component/ButtonDialog"),
    TableActionDialog: () => import("../../component/TableActionDialog"),
    UserForm: () => import("../../components/forms/UserForm"),
    WalletForm: () => import("../../components/forms/WalletForm")
  },
  data: () => ({
    headers: [
      { text: "USERNAME", value: "email" },
      { text: "FULL NAME", value: "full_name" },
      { text: "CREDITS", value: "credits" },
      { text: "CURRENCY", value: "currency" },
      { text: "OPEN BETS", value: "open_bets" },
      { text: "LAST BET", value: "last_bet" },
      { text: "LAST LOGIN", value: "last_login" },
      { text: "STATUS", value: "status" },
      { text: "CREATED DATE", value: "created_at" },
      {
        text: "OPTIONS",
        value: "actions",
        width: "10%",
        align: "center",
        sortable: false
      }
    ],
    search: ""
  }),
  computed: {
    ...mapState("users", ["userStatus", "isLoadingUsers"]),
    ...mapGetters("users", ["usersTable"]),
  },
  mounted() {
    this.getUsersList()
  },
  methods: {
    ...mapMutations('users', { setUsers: 'SET_USERS' }),
    ...mapActions('users', ['getUsersList', 'manageUser']),
    async updateUserStatus(user) {
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Updating user account status..."
        });
        await this.manageUser(user)
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "User account status updated."
        });
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    }
  },
  beforeRouteLeave(to, from, next) {
    this.setUsers([])
    next()
  }
};
</script>

<style>
.users p {
  margin-bottom: 0;
}

.users .v-toolbar__content {
  padding: 16px;
}
</style>
