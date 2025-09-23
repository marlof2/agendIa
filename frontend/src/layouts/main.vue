<template>
  <v-app>
    <!-- Sidebar -->
    <v-navigation-drawer
      v-model="drawer"
      :rail="rail"
      :permanent="!isMobile"
      :temporary="isMobile"
      @click="rail = false"
      class="sidebar"
      :class="{ 'sidebar--collapsed': rail, 'sidebar--mobile': isMobile }"
    >
      <!-- Logo/Brand -->
      <div class="sidebar__header">
        <div class="sidebar__brand-container">
          <div class="sidebar__logo">
            <div class="logo-circle">
              <v-icon color="white" size="28">mdi-calendar-check</v-icon>
            </div>
            <div v-if="!rail" class="logo-text">
              <h2 class="logo-title">AgendIA</h2>
              <p class="logo-subtitle">Sistema Inteligente</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <div class="sidebar__menu">
        <v-list density="compact" nav class="sidebar__list">
          <v-list-item
            v-for="item in menuItems"
            :key="item.title"
            :value="item.value"
            :to="item.to"
            :active="isActiveRoute(item.to)"
            class="sidebar__item"
            @click="handleMenuClick"
          >
            <template v-slot:prepend>
              <div class="sidebar__icon-container">
                <v-icon
                  :color="
                    isActiveRoute(item.to) ? 'white' : 'rgba(255,255,255,0.7)'
                  "
                  size="20"
                >
                  {{ item.icon }}
                </v-icon>
              </div>
            </template>
            <v-list-item-title v-if="!rail" class="sidebar__item-title">
              {{ item.title }}
            </v-list-item-title>
            <div
              v-if="isActiveRoute(item.to)"
              class="sidebar__active-indicator"
            ></div>
          </v-list-item>
        </v-list>
      </div>

    </v-navigation-drawer>

    <!-- Mobile Overlay -->
    <div
      v-if="isMobile && drawer"
      class="mobile-overlay"
      @click="drawer = false"
    ></div>

    <!-- Main Content Area -->
    <v-app-bar
      :elevation="0"
      class="topbar"
      :class="{ 'topbar--sidebar-collapsed': rail }"
    >
      <div class="topbar__container">
        <!-- Left Section -->
        <div class="topbar__left">
          <!-- Menu Toggle Button -->
          <v-btn
            icon
            @click="toggleMenu"
            class="topbar__menu-toggle"
            variant="text"
          >
            <v-icon size="24">{{ getMenuIcon }}</v-icon>
          </v-btn>
        </div>

        <!-- Right Section -->
        <div class="topbar__right">
          <TopbarActions />
        </div>
      </div>
    </v-app-bar>

    <!-- Main Content -->
    <v-main class="main-content">
      <v-container fluid class="pa-6">
        <router-view />
      </v-container>
    </v-main>
  </v-app>
</template>

<script lang="ts" setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTheme } from 'vuetify'
import TopbarActions from '@/components/TopbarActions.vue'

// Composables
const route = useRoute();
const router = useRouter();
const theme = useTheme();

// Reactive state
const drawer = ref(true);
const rail = ref(false);
const windowWidth = ref(window.innerWidth);

// Mobile detection
const isMobile = computed(() => windowWidth.value < 960);

// Theme management
const isDark = computed(() => theme.global.current.value.dark);

// Menu items configuration
const menuItems = [
  {
    title: "Dashboard",
    icon: "mdi-view-dashboard",
    value: "dashboard",
    to: "/dashboard",
  },
  {
    title: "Agendamentos",
    icon: "mdi-calendar-clock",
    value: "appointments",
    to: "/appointments",
  },
  {
    title: "Clientes",
    icon: "mdi-account-group",
    value: "clients",
    to: "/clients",
  },
  {
    title: "Profissionais",
    icon: "mdi-account-tie",
    value: "professionals",
    to: "/professionals",
  },
  {
    title: "Serviços",
    icon: "mdi-briefcase",
    value: "services",
    to: "/services",
  },
  {
    title: "Relatórios",
    icon: "mdi-chart-line",
    value: "reports",
    to: "/reports",
  },
  {
    title: "Integrações",
    icon: "mdi-connection",
    value: "integrations",
    to: "/integrations",
  },
];

// Page title mapping
const pageTitles: Record<string, string> = {
  "/": "Início",
  "/dashboard": "Dashboard",
  "/appointments": "Agendamentos",
  "/clients": "Clientes",
  "/professionals": "Profissionais",
  "/services": "Serviços",
  "/reports": "Relatórios",
  "/integrations": "Integrações",
  "/settings": "Configurações",
};

// Computed properties
const currentPageTitle = computed(() => {
  return pageTitles[route.path] || "";
});

// Methods
const isActiveRoute = (path: string) => {
  return route.path === path;
};

const handleMenuClick = () => {
  // Close mobile drawer if needed
  if (isMobile.value) {
    drawer.value = false;
  }
};

// Toggle menu behavior
const toggleMenu = () => {
  if (isMobile.value) {
    // On mobile, toggle the drawer
    drawer.value = !drawer.value;
  } else {
    // On desktop, toggle the rail
    rail.value = !rail.value;
  }
};

// Get appropriate menu icon
const getMenuIcon = computed(() => {
  if (isMobile.value) {
    return drawer.value ? "mdi-close" : "mdi-menu";
  } else {
    return rail.value ? "mdi-menu" : "mdi-menu-open";
  }
});

// Handle window resize
const handleResize = () => {
  windowWidth.value = window.innerWidth;
  if (!isMobile.value) {
    drawer.value = true;
    rail.value = false;
  } else {
    drawer.value = false;
  }
};

// Add resize listener
window.addEventListener('resize', handleResize);

// Watch for route changes to update page title
watch(
  () => route.path,
  (newPath) => {
    // Update document title
    document.title = `${pageTitles[newPath] || "AgendIA"} - AgendIA`;
  },
  { immediate: true }
);

// Initialize theme from localStorage
const savedTheme = localStorage.getItem("agendia-theme");
if (savedTheme) {
  theme.global.name.value = savedTheme;
}

// Cleanup on unmount
onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});
</script>

<style scoped>
/* Sidebar Styles */
.sidebar {
  background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-right: none;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow: hidden;
}

.sidebar::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.1) 0%,
    rgba(255, 255, 255, 0.05) 100%
  );
  pointer-events: none;
}

.sidebar--collapsed {
  width: 80px !important;
}

.sidebar__header {
  padding: 24px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
  position: relative;
  z-index: 1;
}

.sidebar__brand-container {
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar__logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-circle {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.logo-text {
  opacity: 1;
  transition: opacity 0.3s ease;
}

.logo-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  color: white;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.logo-subtitle {
  font-size: 0.75rem;
  margin: 0;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
  letter-spacing: 0.5px;
}

.sidebar__menu {
  padding: 16px 0;
  position: relative;
  z-index: 1;
}

.sidebar__list {
  padding: 0 12px;
}

.sidebar__item {
  margin: 6px 0;
  border-radius: 12px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.sidebar__item::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.1) 0%,
    rgba(255, 255, 255, 0.05) 100%
  );
  opacity: 0;
  transition: opacity 0.3s ease;
}

.sidebar__item:hover::before {
  opacity: 1;
}

.sidebar__item:hover {
  background-color: rgba(255, 255, 255, 0.15);
  transform: translateX(4px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.sidebar__item.v-list-item--active {
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.25) 0%,
    rgba(255, 255, 255, 0.15) 100%
  );
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  transform: translateX(6px);
}

.sidebar__icon-container {
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.sidebar__item:hover .sidebar__icon-container {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.1);
}

.sidebar__item.v-list-item--active .sidebar__icon-container {
  background: rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.sidebar__item-title {
  color: white;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.3px;
}

.sidebar__active-indicator {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 24px;
  background: white;
  border-radius: 2px 0 0 2px;
  box-shadow: 0 0 8px rgba(255, 255, 255, 0.5);
}

.sidebar__footer {
  border-top: 1px solid rgba(255, 255, 255, 0.15);
  padding: 16px 12px;
  position: relative;
  z-index: 1;
}

.sidebar__footer-content {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 8px;
  backdrop-filter: blur(10px);
}

.sidebar__footer-item {
  border-radius: 8px;
  transition: all 0.3s ease;
}

.sidebar__footer-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

/* Topbar Styles */
.topbar {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  position: relative;
  z-index: 100;
}

.topbar--sidebar-collapsed {
  padding-left: 20px;
}

.topbar__container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  height: 64px;
  max-width: 100%;
  width: 100%;
}

.topbar__left {
  display: flex;
  align-items: center;
  gap: 24px;
  flex: 0 0 auto;
  min-width: 0;
}

.topbar__menu-toggle {
  transition: all 0.2s ease;
  border-radius: 8px;
  flex-shrink: 0;
  color: #64748b;
}

.topbar__menu-toggle:hover {
  background: rgba(0, 0, 0, 0.04);
  color: #334155;
}

.topbar__title-section {
  display: flex;
  align-items: center;
  min-width: 0;
}

.topbar__title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  white-space: nowrap;
}

.topbar__right {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  margin-left: auto;
}


.topbar__user-section {
  flex-shrink: 0;
  margin-left: 8px;
  order: 2;
}

.topbar__action-btn {
  transition: all 0.2s ease;
  border-radius: 8px;
  min-width: 40px;
  height: 40px;
  color: #64748b;
}

.topbar__action-btn:hover {
  background: rgba(0, 0, 0, 0.04);
  color: #334155;
  transform: none;
}

.topbar__user-menu {
  padding: 4px;
  border-radius: 8px;
  transition: all 0.2s ease;
  background: transparent;
  min-width: auto;
  height: 48px;
}

.topbar__user-menu:hover {
  background: rgba(0, 0, 0, 0.04);
  transform: none;
  box-shadow: none;
}

.user-info {
  display: flex;
  align-items: center;
  position: relative;
}

.user-avatar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
}

.user-status-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #10b981;
  border: 2px solid white;
  border-radius: 50%;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
}

.user-menu-list {
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(0, 0, 0, 0.06);
  overflow: hidden;
  min-width: 200px;
}

.user-menu-header {
  padding: 16px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.user-avatar-large {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
}

.user-name-large {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
}

.user-role-large {
  color: #64748b;
  font-size: 0.8rem;
  font-weight: 500;
}

.pricing-item {
  background: rgba(59, 130, 246, 0.05);
  color: #2563eb;
  font-weight: 600;
}

.pricing-item :deep(.v-list-item-title) {
  color: #2563eb;
  font-weight: 600;
}

.logout-item {
  color: #ef4444;
}

.logout-item :deep(.v-list-item-title) {
  color: #ef4444;
  font-weight: 500;
}

/* Mobile Overlay */
.mobile-overlay {
  position: fixed;
  top: 0;
  left: 280px; /* Começa depois da sidebar */
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 998;
  backdrop-filter: blur(2px);
  pointer-events: auto;
}

/* Main Content */
.main-content {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
  position: relative;
}

.main-content::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
  opacity: 0.3;
  pointer-events: none;
}

/* Dark theme adjustments */
.v-theme--dark .topbar {
  background: rgba(30, 41, 59, 0.8);
  border-bottom-color: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
}

.v-theme--dark .topbar__title {
  color: #f1f5f9;
}

.v-theme--dark .topbar__menu-toggle {
  color: #94a3b8;
}

.v-theme--dark .topbar__menu-toggle:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #e2e8f0;
}

.v-theme--dark .topbar__action-btn {
  color: #94a3b8;
}

.v-theme--dark .topbar__action-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #e2e8f0;
}

.v-theme--dark .topbar__user-menu:hover {
  background: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .user-name-large {
  color: #f1f5f9;
}

.v-theme--dark .user-role-large {
  color: #94a3b8;
}

.v-theme--dark .main-content {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

.v-theme--dark .main-content::before {
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.02)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.02)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.02)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
  opacity: 0.2;
}

/* Dark theme sidebar adjustments */
.v-theme--dark .sidebar {
  background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
  border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.v-theme--dark .sidebar::before {
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.05) 0%,
    rgba(255, 255, 255, 0.02) 100%
  );
}

.v-theme--dark .sidebar__header {
  border-bottom-color: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .logo-circle {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
}

.v-theme--dark .logo-title {
  color: white;
}

.v-theme--dark .logo-subtitle {
  color: rgba(255, 255, 255, 0.7);
}

.v-theme--dark .sidebar__item:hover {
  background-color: rgba(255, 255, 255, 0.08);
}

.v-theme--dark .sidebar__item.v-list-item--active {
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.15) 0%,
    rgba(255, 255, 255, 0.08) 100%
  );
}

.v-theme--dark .sidebar__item:hover .sidebar__icon-container {
  background: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .sidebar__item.v-list-item--active .sidebar__icon-container {
  background: rgba(255, 255, 255, 0.2);
}

.v-theme--dark .sidebar__item-title {
  color: white;
}

.v-theme--dark .sidebar__active-indicator {
  background: white;
  box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
}

.v-theme--dark .sidebar__footer {
  border-top-color: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .sidebar__footer-content {
  background: rgba(255, 255, 255, 0.03);
}

.v-theme--dark .sidebar__footer-item:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .sidebar {
    position: fixed !important;
    z-index: 1000;
    width: 280px !important;
  }

  /* Garantir que a sidebar fique acima do overlay */
  .sidebar--mobile {
    z-index: 1001 !important;
  }

  .sidebar--collapsed {
    width: 80px !important;
  }

  /* Garantir que a sidebar seja completamente oculta no mobile quando fechada */
  .sidebar--mobile {
    transform: translateX(-100%) !important;
    transition: transform 0.3s ease !important;
  }

  .sidebar--mobile.v-navigation-drawer--active {
    transform: translateX(0) !important;
  }

  .topbar--sidebar-collapsed {
    margin-left: 0 !important;
  }

  .main-content {
    margin-left: 0 !important;
  }

  .main-content .v-container {
    padding: 16px !important;
  }

  .topbar__container {
    padding: 0 20px;
    height: 60px;
  }

  .topbar__left {
    gap: 20px;
  }

  .topbar__right {
    gap: 12px;
  }

  .topbar__title {
    font-size: 1.25rem;
  }

  /* Garantir que o botão do menu seja sempre visível no mobile */
  .topbar__menu-toggle {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 1001 !important;
    position: relative !important;
  }

  /* Ajustar logo em mobile */
  .sidebar__header {
    padding: 20px 16px;
  }

  .logo-circle {
    width: 40px;
    height: 40px;
  }

  .logo-title {
    font-size: 1.25rem;
  }

  .logo-subtitle {
    font-size: 0.7rem;
  }

  /* Ajustar itens do menu em mobile */
  .sidebar__list {
    padding: 0 8px;
  }

  .sidebar__item {
    margin: 4px 0;
  }

  .sidebar__icon-container {
    width: 32px;
    height: 32px;
  }

  /* Manter fonte legível no mobile */
  .sidebar__item-title {
    font-size: 0.9rem !important;
  }
}

@media (max-width: 600px) {
  .topbar__container {
    padding: 0 16px;
    height: 56px;
  }

  .topbar__breadcrumb {
    display: none;
  }

  .topbar__title-section {
    gap: 2px;
  }

  .topbar__title {
    font-size: 1.1rem;
  }

  .topbar__actions {
    gap: 2px;
  }

  .topbar__action-btn {
    min-width: 36px;
    height: 36px;
  }

  .topbar__user-menu {
    padding: 2px;
    height: 44px;
  }

  .user-avatar {
    width: 32px !important;
    height: 32px !important;
  }

  /* Ajustar sidebar para telas muito pequenas */
  .sidebar {
    width: 100vw !important;
    max-width: 320px;
  }

  .sidebar__header {
    padding: 16px 12px;
  }

  .logo-circle {
    width: 36px;
    height: 36px;
  }

  .logo-title {
    font-size: 1.1rem;
  }

  .logo-subtitle {
    font-size: 0.65rem;
  }

  .sidebar__list {
    padding: 0 6px;
  }

  .sidebar__item {
    margin: 3px 0;
    border-radius: 10px;
  }

  .sidebar__icon-container {
    width: 30px;
    height: 30px;
  }

  .sidebar__item-title {
    font-size: 0.9rem;
  }

  /* Ajustar container principal */
  .main-content .v-container {
    padding: 12px !important;
  }

  /* Ajustar overlay para telas menores */
  .mobile-overlay {
    left: 320px;
  }
}

@media (max-width: 480px) {
  .topbar__container {
    padding: 0 12px;
    height: 52px;
  }

  .topbar__left {
    gap: 12px;
  }

  .topbar__right {
    gap: 8px;
  }

  .topbar__title {
    font-size: 1rem;
  }

  .topbar__menu-toggle {
    min-width: 32px;
    height: 32px;
  }

  .topbar__action-btn {
    min-width: 32px;
    height: 32px;
  }

  .topbar__user-menu {
    height: 40px;
  }

  .user-avatar {
    width: 28px !important;
    height: 28px !important;
  }

  /* Sidebar ainda mais compacta */
  .sidebar__header {
    padding: 12px 8px;
  }

  .logo-circle {
    width: 32px;
    height: 32px;
  }

  .logo-title {
    font-size: 1rem;
  }

  .logo-subtitle {
    font-size: 0.6rem;
  }

  .sidebar__list {
    padding: 0 4px;
  }

  .sidebar__item {
    margin: 2px 0;
    border-radius: 8px;
  }

  .sidebar__icon-container {
    width: 28px;
    height: 28px;
  }

  .sidebar__item-title {
    font-size: 0.85rem;
  }

  /* Container principal mais compacto */
  .main-content .v-container {
    padding: 8px !important;
  }

  /* Ajustar overlay para telas muito pequenas */
  .mobile-overlay {
    left: 100vw;
    max-width: 100vw;
  }
}

/* Animation for smooth transitions */
.v-navigation-drawer {
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.v-list-item {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom scrollbar for sidebar */
.sidebar :deep(.v-navigation-drawer__content) {
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
}

.sidebar :deep(.v-navigation-drawer__content)::-webkit-scrollbar {
  width: 6px;
}

.sidebar :deep(.v-navigation-drawer__content)::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar :deep(.v-navigation-drawer__content)::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.3);
  border-radius: 3px;
}

.sidebar :deep(.v-navigation-drawer__content)::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.5);
}

/* Glassmorphism effects */
.glass-effect {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

/* Floating animations */
@keyframes float {
  0%,
  100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.floating {
  animation: float 6s ease-in-out infinite;
}

/* Pulse animation for active states */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
  }
}

.pulse {
  animation: pulse 2s infinite;
}
</style>
