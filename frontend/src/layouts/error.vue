<template>
  <v-app>
    <!-- Main Content - Layout minimalista para páginas de erro -->
    <v-main class="error-main">
      <v-container fluid class="fill-height">
        <v-row align="center" justify="center" class="fill-height">
          <v-col cols="12" sm="8" md="6" lg="4" xl="3">
            <router-view />
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script lang="ts" setup>
// Layout para páginas de erro (404, 500, unauthorized, etc.)
</script>

<style scoped>
.error-main {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Dark theme */
.v-theme--dark .error-main {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #020617 100%);
}
</style>
