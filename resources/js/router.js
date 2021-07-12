import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

import { getToken } from './helpers/token'
import store from './store'

const router = new Router({
  mode: 'hash',
  base: process.env.BASE_URL,
  routes: [
    {
      path: '/login',
      component: () => import('./views/auth/Login'),
    },
    {
      path: '/',
      component: () => import('./views/dashboard/Index'),
      children: [
        // Dashboard
        {
          // name: 'Dashboard',
          path: '',
          redirect: 'accounts/users'
          // component: () => import('./views/dashboard/Dashboard'),
        },
        // Pages
        {
          name: 'Accounts',
          path: 'accounts',
          component: () => import('./views/dashboard/pages/Accounts'),
          children: [
            {
              name: 'Users',
              path: 'users',
              component: () => import('./views/dashboard/pages/accounts/Users'),
            },
            {
              name: 'User Transactions',
              path: 'users/transactions/:id',
              component: () => import('./views/dashboard/pages/accounts/UserTransactions'),
            },
            {
              name: 'Provider Accounts',
              path: 'providers',
              component: () => import('./views/dashboard/pages/accounts/Providers'),
            },
            {
              name: 'Admin',
              path: 'admin',
              component: () => import('./views/dashboard/pages/accounts/Admin'),
            },
            {
              name: 'Admin Logs',
              path: 'admin/logs/:id',
              component: () => import('./views/dashboard/pages/accounts/AdminLogs')
            },
            {
              name: 'Wallet Transactions',
              path: 'wallet/transactions/:module/:uuid',
              component: () => import('./views/dashboard/pages/accounts/WalletTransactions'),
            },
            {
              name: 'Provider Transactions',
              path: 'providers/transactions/:id',
              component: () => import('./views/dashboard/pages/accounts/ProviderTransactions'),
            },
            // {
            //   name: 'Admin Roles',
            //   path: 'roles',
            //   component: () => import('./views/dashboard/pages/accounts/AdminRoles')
            // }
          ]
        },
        {
          name: 'System Configurations',
          path: 'system_configurations',
          component: () => import('./views/dashboard/pages/SystemConfigurations')
        },
        {
          name: 'Errors',
          path: 'errors',
          component: () => import('./views/dashboard/pages/Errors'),
          children: [
            {
              name: 'General Errors',
              path: 'general',
              component: () => import('./views/dashboard/pages/errors/GeneralErrors')
            },
            {
              name: 'Provider Errors',
              path: 'provider',
              component: () => import('./views/dashboard/pages/errors/ProviderErrors')
            }
          ]
        },
        {
          name: 'Wallet Clients',
          path: 'wallet_clients',
          component: () => import('./views/dashboard/pages/WalletClients')
        },
        {
          name: 'Currencies',
          path: 'currencies',
          component: () => import('./views/dashboard/pages/Currencies')
        },
        {
          name: 'Providers',
          path: 'providers',
          component: () => import('./views/dashboard/pages/Providers')
        },
        {
          path: 'matching',
          component: () => import('./views/dashboard/pages/MasterlistMatching'),
          children: [
            {
              name: 'Leagues Matching',
              path: 'leagues',
              component: () => import('./views/dashboard/pages/matching/LeaguesMatching'),
              meta: {
                type: 'leagues'
              }
            },
            // {
            //   name: 'Team Matching',
            //   path: 'teams',
            //   component: () => import('./views/dashboard/pages/matching/MatchingTable')
            // },
            {
              name: 'Events Matching',
              path: 'events',
              component: () => import('./views/dashboard/pages/matching/EventsMatching'),
              meta: {
                type: 'events'
              }
            },
            {
              name: 'Matching History',
              path: 'history',
              component: () => import('./views/dashboard/pages/matching/MatchingHistory'),
            },
            {
              name: 'Aliases',
              path: 'aliases',
              component: () => import('./views/dashboard/pages/matching/Aliases'),
            },
          ]
        },
        {
          name: 'User Profile',
          path: 'pages/user',
          component: () => import('./views/dashboard/pages/UserProfile'),
        },
        {
          name: 'Notifications',
          path: 'components/notifications',
          component: () => import('./views/dashboard/component/Notifications'),
        },
        {
          name: 'Icons',
          path: 'components/icons',
          component: () => import('./views/dashboard/component/Icons'),
        },
        {
          name: 'Typography',
          path: 'components/typography',
          component: () => import('./views/dashboard/component/Typography'),
        },
        // Tables
        {
          name: 'Regular Tables',
          path: 'tables/regular-tables',
          component: () => import('./views/dashboard/tables/RegularTables'),
        },
        // Maps
        {
          name: 'Google Maps',
          path: 'maps/google-maps',
          component: () => import('./views/dashboard/maps/GoogleMaps'),
        },
        // Upgrade
        {
          name: 'Upgrade',
          path: 'upgrade',
          component: () => import('./views/dashboard/Upgrade'),
        },
      ],
    },
  ],
})

router.beforeEach((to, from, next) => {
  const authRoutes = ['/login']
  if (getToken()) {
    if(authRoutes.includes(to.matched[0].path)) {
        next('/accounts/users')
    } else {
        next()
    }
  } else {
    if(authRoutes.includes(to.matched[0].path)) {
        next()
    } else {
        next('/login')
    }
  }
  store.dispatch('cancelPendingRequests')

})

export default router
