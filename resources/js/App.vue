<template>
  <v-app>
    <v-content>
        <router-view />
        <snack-bar></snack-bar>
    </v-content>
  </v-app>
</template>

<script>
  export default {
    name: 'App',
    components: {
      SnackBar: () => import("./views/dashboard/component/Snackbar"),
    }
  }
</script>
