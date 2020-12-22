<template>
  <v-app>
    <v-main>
      <router-view />
      <snack-bar></snack-bar>
    </v-main>
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
