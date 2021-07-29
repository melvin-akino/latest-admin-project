<template>
  <v-navigation-drawer
    id="core-navigation-drawer"
    v-model="drawer"
    :expand-on-hover="expandOnHover"
    :right="$vuetify.rtl"
    mobile-breakpoint="960"
    app
    width="260"
    v-bind="$attrs"
    color="light"
  >
    <v-divider class="mb-1" />

    <v-list dense nav>
      <v-list-item>
        <v-list-item-avatar class="align-self-center" color="white" contain>
          <v-img :src="logo" max-height="30" />
        </v-list-item-avatar>

        <v-list-item-content>
          <v-list-item-title class="display-1" v-text="'Multiline'" />
        </v-list-item-content>
      </v-list-item>
    </v-list>

    <v-divider class="mb-2" />

    <v-list expand nav>
      <template v-for="(item, i) in computedItems">
        <base-item-group v-if="item.children" :key="`group-${i}`" :item="item">
        </base-item-group>

        <base-item v-else :key="`item-${i}`" :item="item" />
      </template>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
import logo from "../../../../../assets/images/logo.png";

export default {
  name: "DashboardCoreDrawer",

  props: {
    expandOnHover: {
      type: Boolean,
      default: false
    }
  },

  data: () => ({
    logo: logo,
    items: [
      // {
      //   icon: "mdi-view-dashboard",
      //   title: "dashboard",
      //   to: "/"
      // },
      {
        icon: "mdi-account",
        title: "Accounts",
        to: "/accounts/users"
      },
      {
        icon: "mdi-account-cash",
        title: "Provider Accounts",
        to: "/accounts/providers"
      },
      {
        icon: "mdi-account-star",
        title: "Admin Users",
        to: "/accounts/admin"
      },
      // {
      //   icon: "mdi-account-details",
      //   title: "Admin Roles",
      //   to: "/accounts/roles"
      // },
      {
        icon: "mdi-cog-box",
        title: "System Configurations",
        to: "/system_configurations"
      },
      {
        icon: "mdi-alert-circle",
        title: "General Errors",
        to: "/errors/general"
      },
      {
        icon: "mdi-badge-account-alert",
        title: "Provider Errors",
        to: "/errors/provider"
      },
      {
        icon: "mdi-wallet",
        title: "Wallet Clients",
        to: "/wallet_clients"
      },
      {
        icon: "mdi-cash-usd",
        title: "Currencies",
        to: "/currencies"
      },
      {
        icon: "mdi-cash-multiple",
        title: "Providers",
        to: "/providers"
      },
      {
        icon: "mdi-arrow-decision",
        title: "Matching",
        group: "/matching",
        children: [
          {
            icon: "mdi-trophy",
            title: "Leagues",
            to: "leagues",
            hasClickEvent: false
          },
          {
            icon: "mdi-calendar-blank",
            title: "Events",
            to: "events",
            hasClickEvent: false
          },
          {
            icon: "mdi-history",
            title: "History",
            to: "history",
            hasClickEvent: false
          },
          {
            icon: "mdi-alpha-a-box-outline",
            title: "Aliases",
            to: "aliases",
            hasClickEvent: false
          },
          {
            icon: "mdi-refresh-circle",
            title: "Reprocess Data",
            hasClickEvent: true
          },
          {
            icon: "mdi-delete-variant",
            title: "Clear Duplicates",
            hasClickEvent: true
          }
        ]
      }
      // {
      //   title: "icons",
      //   icon: "mdi-chart-bubble",
      //   to: "/components/icons"
      // },
      // {
      //   title: "notifications",
      //   icon: "mdi-bell",
      //   to: "/components/notifications"
      // }
    ]
  }),

  computed: {
    drawer: {
      get() {
        return this.$store.state.drawer;
      },
      set(val) {
        this.$store.commit("SET_DRAWER", val);
      }
    },
    computedItems() {
      return this.items.map(this.mapItem);
    }
  },

  methods: {
    mapItem(item) {
      return {
        ...item,
        children: item.children ? item.children.map(this.mapItem) : undefined,
        title: item.title
      };
    }
  }
};
</script>

<style lang="scss">
@import '~vuetify/src/styles/tools/_rtl.sass';

.item.v-list-item--active {
  color: #ffffff !important; 
}

.item-group .v-list-group__header.v-list-item--active {
  color: rgba(0, 0, 0, 0.87) !important;
}

#core-navigation-drawer {
  .v-list-group__header.v-list-item--active:before {
    opacity: 0.24;
  }

  .v-list-item {
    &__title.display-1 {
      font-size: 1.125rem !important;
      font-weight:400;
    }

    &__icon--text,
    &__icon:first-child {
      justify-content: center;
      text-align: center;
      width: 20px;

      @include ltr {
        margin-right: 24px;
        margin-left: 12px !important;
      }


      @include rtl {
        margin-left: 24px;
        margin-right: 12px !important;
      }
    }
  }

  .v-list--dense {
    .v-list-item {
      &__icon--text,
      &__icon:first-child {
        margin-top: 10px;
      }
    }
  }

  .v-list-group--sub-group {
    .v-list-item {
      @include ltr {
        padding-left: 8px;
      }


      @include rtl {
        padding-right: 8px;
      }
    }

    .v-list-group__header {
      @include ltr {
        padding-right: 0;
      }


      @include rtl {
        padding-right: 0;
      }


      .v-list-item__icon--text {
        margin-top: 19px;
        order: 0;
      }

      .v-list-group__header__prepend-icon {
        order: 2;

        @include ltr {
          margin-right: 8px;
        }


        @include rtl {
          margin-left: 8px;
        }
      }
    }
  }
}
</style>
