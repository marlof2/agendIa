<template>
  <v-app>
    <!-- Main Content - Sem sidebar e app-bar -->
    <v-main class="public-main">
      <router-view />
    </v-main>
  </v-app>
</template>

<script lang="ts" setup>
// Layout para páginas públicas (login, unauthorized, etc.)
</script>

<style scoped>
.public-main {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
}

.public-main::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
  opacity: 0.3;
  pointer-events: none;
}

/* Dark theme */
.v-theme--dark .public-main {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.v-theme--dark .public-main::before {
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
  opacity: 0.2;
}
</style>
