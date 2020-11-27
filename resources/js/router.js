import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

import Cookies from 'js-cookie'

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
          name: 'Dashboard',
          path: '',
          component: () => import('./views/dashboard/Dashboard'),
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
              name: 'Providers',
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
              name: 'Admin Roles',
              path: 'roles',
              component: () => import('./views/dashboard/pages/accounts/AdminRoles')
            }
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
  const token = Cookies.get('access_token')
  if (token) {
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
})

export default router