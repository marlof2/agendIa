<template>
  <div class="topbar-actions">
    <!-- Notifications -->
    <v-btn
      icon
      variant="text"
      class="topbar-actions__btn"
      size="large"
      @click="toggleNotifications"
      :title="`${notificationCount} notificações`"
    >
      <v-badge
        color="error"
        :content="notificationCount"
        :model-value="notificationCount > 0"
        :max="99"
      >
        <v-icon size="22">mdi-bell-outline</v-icon>
      </v-badge>
    </v-btn>

    <!-- Theme Toggle -->
    <v-btn
      icon
      @click="toggleTheme"
      class="topbar-actions__btn"
      variant="text"
      size="large"
      :title="isDark ? 'Ativar modo claro' : 'Ativar modo escuro'"
    >
      <v-icon size="22">{{
        isDark ? "mdi-weather-sunny" : "mdi-weather-night"
      }}</v-icon>
    </v-btn>

    <!-- User Menu -->
    <div class="topbar-actions__user-section">
      <v-menu
        offset-y
        :close-on-content-click="false"
        transition="slide-y-transition"
      >
        <template v-slot:activator="{ props }">
          <v-btn
            v-bind="props"
            class="topbar-actions__user-menu"
            variant="text"
            size="large"
            :title="`Menu do usuário: ${userName}`"
          >
            <div class="user-info">
              <v-avatar size="36" class="user-avatar">
                <v-img v-if="userAvatar" :src="userAvatar" :alt="userName" />
                <v-icon v-else color="white" size="20">mdi-account</v-icon>
              </v-avatar>
              <div class="user-status-dot" :class="statusClass"></div>
            </div>
          </v-btn>
        </template>
        <v-list class="user-menu-list">
          <v-list-item class="user-menu-header">
            <template v-slot:prepend>
              <v-avatar size="40" class="user-avatar-large">
                <v-img v-if="userAvatar" :src="userAvatar" :alt="userName" />
                <v-icon v-else color="white" size="20">mdi-account</v-icon>
              </v-avatar>
            </template>
            <v-list-item-title class="user-name-large">
              {{ userName }}
            </v-list-item-title>
            <v-list-item-subtitle class="user-role-large">
              {{ userRole }}
            </v-list-item-subtitle>
          </v-list-item>
          <v-divider />
          <v-list-item
            prepend-icon="mdi-account"
            title="Meu Perfil"
            @click="navigateToProfile"
            class="menu-item"
          />
          <v-list-item
            prepend-icon="mdi-cog"
            title="Configurações"
            @click="navigateToSettings"
            class="menu-item"
          />
          <v-list-item
            prepend-icon="mdi-currency-usd"
            title="Planos e Preços"
            class="pricing-item"
            @click="navigateToPricing"
          />
          <v-list-item
            prepend-icon="mdi-help-circle"
            title="Ajuda e Suporte"
            @click="navigateToHelp"
            class="menu-item"
          />
          <v-divider />
          <v-list-item
            prepend-icon="mdi-logout"
            title="Sair"
            class="logout-item"
            @click="logout()"
          />
        </v-list>
      </v-menu>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref } from "vue";
import { useTheme } from "vuetify";
import { useRouter } from "vue-router";
import { useAuth } from "@/composables/useAuth";

const theme = useTheme();
const router = useRouter();
const { logout, user } = useAuth();

// User data (em produção, isso viria de um store/API)
const userName = ref(user.value?.name);
const userRole = ref(user.value?.role);
const userAvatar = ref("");
const userStatus = ref("online"); // online, away, busy, offline
const notificationCount = ref(1);

// Theme management
const isDark = computed(() => theme.global.current.value.dark);

const toggleTheme = () => {
  const newTheme = theme.global.current.value.dark ? "light" : "dark";
  theme.global.name.value = newTheme;
  localStorage.setItem("agendia-theme", newTheme);
};

// Notifications
const toggleNotifications = () => {
  // Em produção, abriria um painel de notificações
  console.log("Abrir painel de notificações");
  notificationCount.value = 0; // Simular leitura das notificações
};

// User status
const statusClass = computed(() => {
  return {
    'status-online': userStatus.value === 'online',
    'status-away': userStatus.value === 'away',
    'status-busy': userStatus.value === 'busy',
    'status-offline': userStatus.value === 'offline'
  };
});


// Navigation methods
const navigateToProfile = () => {
  router.push('/profile');
};

const navigateToSettings = () => {
  router.push('/settings');
};

const navigateToPricing = () => {
  router.push('/pricing');
};

const navigateToHelp = () => {
  router.push('/help');
};

</script>

<style scoped>
.topbar-actions {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  margin-left: auto;
  gap: 8px;
}

.topbar-actions__btn {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 12px;
  color: #64748b;
  position: relative;
  overflow: hidden;
}

.topbar-actions__btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.topbar-actions__btn:hover {
  background: rgba(0, 0, 0, 0.04);
  color: #334155;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.topbar-actions__btn:hover::before {
  opacity: 1;
}

.topbar-actions__btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.topbar-actions__user-section {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.topbar-actions__user-menu {
  padding: 4px;
  border-radius: 12px;
  transition: all 0.2s ease;
  height: 48px;
}

.topbar-actions__user-menu:hover {
  background: rgba(0, 0, 0, 0.04);
}

.user-info {
  position: relative;
  display: flex;
  align-items: center;
}

.user-avatar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.user-status-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  border: 2px solid white;
  border-radius: 50%;
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.status-online {
  background: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.status-away {
  background: #f59e0b;
  box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
}

.status-busy {
  background: #ef4444;
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
}

.status-offline {
  background: #6b7280;
  box-shadow: 0 0 0 2px rgba(107, 114, 128, 0.2);
}

/* User Menu Styles */
.user-menu-list {
  min-width: 300px;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(0, 0, 0, 0.08);
  overflow: hidden;
  backdrop-filter: blur(20px);
}

.user-menu-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 24px 20px;
  position: relative;
  overflow: hidden;
}

.user-menu-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
  pointer-events: none;
}

.user-avatar-large {
  background: rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.user-name-large {
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.user-role-large {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.9rem;
  font-weight: 500;
}

.menu-item {
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 2px 8px;
}

.menu-item:hover {
  background: rgba(102, 126, 234, 0.08);
  transform: translateX(4px);
}

.pricing-item {
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
  color: white;
  margin: 4px 8px;
  border-radius: 12px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.pricing-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s ease;
}

.pricing-item:hover::before {
  left: 100%;
}

.pricing-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
}

.pricing-item :deep(.v-list-item-title) {
  color: white;
  font-weight: 600;
}

.logout-item {
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 2px 8px;
}

.logout-item:hover {
  background: rgba(239, 68, 68, 0.08);
  transform: translateX(4px);
}

.logout-item :deep(.v-list-item-title) {
  color: #ef4444;
  font-weight: 500;
}

/* Dark theme adjustments */
.v-theme--dark .topbar-actions__btn {
  color: #94a3b8;
}

.v-theme--dark .topbar-actions__btn:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
}

.v-theme--dark .topbar-actions__user-menu:hover {
  background: rgba(255, 255, 255, 0.05);
}

.v-theme--dark .user-role-large {
  color: #94a3b8;
}

/* Responsive */
@media (max-width: 768px) {
  .topbar-actions {
    gap: 8px;
  }

  .topbar-actions__btn {
    min-width: 40px;
    height: 40px;
  }

  .topbar-actions__user-menu {
    padding: 4px;
    height: 48px;
  }

  .user-avatar {
    width: 32px !important;
    height: 32px !important;
  }

  .user-menu-list {
    min-width: 280px;
    margin: 0 8px;
  }

  .user-menu-header {
    padding: 20px 16px;
  }

  .user-name-large {
    font-size: 1rem;
  }

  .user-role-large {
    font-size: 0.85rem;
  }
}

@media (max-width: 480px) {
  .topbar-actions {
    gap: 4px;
  }

  .topbar-actions__btn {
    min-width: 36px;
    height: 36px;
  }

  .topbar-actions__user-menu {
    padding: 2px;
    height: 44px;
  }

  .user-avatar {
    width: 28px !important;
    height: 28px !important;
  }

  .user-status-dot {
    width: 10px;
    height: 10px;
    bottom: 1px;
    right: 1px;
  }

  .user-menu-list {
    min-width: 260px;
    margin: 0 4px;
  }

  .user-menu-header {
    padding: 16px 12px;
  }

  .user-name-large {
    font-size: 0.95rem;
  }

  .user-role-large {
    font-size: 0.8rem;
  }
}
</style>
