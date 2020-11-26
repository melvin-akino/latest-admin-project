<template>
  <div class="wrapper">
    <router-view />
    <snack-bar></snack-bar>
  </div>
</template>

<script>
  export default {
    name: 'App',
    components: {
      SnackBar: () => import("./views/dashboard/component/Snackbar"),
    }
  }
</script>
