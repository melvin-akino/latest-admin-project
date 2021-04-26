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
        <button-dialog icon="mdi-plus" label="New Account" width="600" @clearFilters="clearFilters">
          <user-form :update="false" :currencies="currencies"></user-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="users"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingUsers"
        loading-text="Loading Users"
        :page="page"
        @pagination="getPage"
        @current-items="getWalletDataForCurrentItems"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Accounts: {{ users.length }}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.firstname`]="{ item }">
          <span>{{item.firstname}} {{item.lastname}}</span>   
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
          <span v-else>{{ item.credits ? item.credits : 0 | moneyFormat }}</span>    
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
          <span v-else>{{ item.currency ? item.currency : '-' }}</span>    
        </template>
        <template v-slot:[`item.open_bets`]="{ item }">
          <span>{{ item.open_bets ? item.open_bets : 0 | moneyFormat }}</span>    
        </template>
        <template v-slot:[`item.last_bet`]="{ item }">
          <span>{{ item.last_bet ? item.last_bet : '-' }}</span>    
        </template>
        <template v-slot:[`item.last_login`]="{ item }">
          <span>{{ item.last_login ? item.last_login : '-' }}</span>    
        </template>
        <template v-slot:[`item.status`]="{ item }">
          <v-select :items="userStatus" dense v-model="item.status" @change="updateUserStatus(item)"></v-select>
        </template>
        <template v-slot:[`item.actions`]="{ item }" class="actions">
          <table-action-dialog icon="mdi-pencil" width="600" tooltipText="Edit" style="z-index:1;">
            <user-form :update="true" :user-to-update="item" :currencies="currencies"></user-form>
          </table-action-dialog>
          <table-action-dialog icon="mdi-currency-gbp" width="600" tooltipText="Wallet Update" style="z-index:2;">
            <wallet-form :user-to-update="item"></wallet-form>
          </table-action-dialog>
          <v-menu style="z-index:3;" offset-y>
            <template v-slot:activator="{ on: menu, attrs }">
              <v-tooltip bottom>
                <template v-slot:activator="{ on: tooltip }">
                  <v-btn icon v-bind="attrs" v-on="{ ...menu, ...tooltip }">
                    <v-icon small>mdi-format-list-bulleted</v-icon>
                  </v-btn>
                </template>
                <span class="caption">Transactions</span>
              </v-tooltip>
            </template>
            <v-list>
              <v-list-item :to="`users/transactions/${item.id}`" target="_blank">
                <v-list-item-title class="caption">User Transactions</v-list-item-title>
              </v-list-item>
              <v-list-item :to="`wallet/transactions/user/${item.uuid}`" target="_blank">
                <v-list-item-title class="caption">Wallet Transactions</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import bus from '../../../../eventBus'
import { moneyFormat } from '../../../../helpers/numberFormat'

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
      { text: "FULL NAME", value: "firstname" },
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
    search: null,
    page: 1,
    currentItems: []
  }),
  computed: {
    ...mapState('users', ['users', 'userStatus', 'isLoadingUsers']),
    ...mapState('currencies', ['currencies']),
  },
  mounted() {
    this.getUsers()
    this.getCurrencies()
  },
  methods: {
    ...mapMutations('users', { setUsers: 'SET_USERS' }),
    ...mapActions('users', ['getUsers', 'manageUser', 'getUserWalletForCurrentItems']),
    ...mapActions('currencies', ['getCurrencies']),
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
    },
    clearFilters() {
      this.search = ''
      this.page = 1
    },
    getPage(pagination) {
      this.page = pagination.page

      if(this.currentItems.length != 0) {
        let noWalletData = []
        this.currentItems.map(user => {
          if(!user.hasOwnProperty('credits') && !user.hasOwnProperty('currency')) {
            noWalletData.push({ uuid: user.uuid, currency: user.currency_code })
          }
        })

        if(noWalletData.length != 0) {
          this.getUserWalletForCurrentItems(noWalletData)
        }
      }
    },
    getWalletDataForCurrentItems(users) {
      this.currentItems = users
    }
  },
  filters: {
    moneyFormat
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
