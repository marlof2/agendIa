<template>
  <div class="topbar-actions">
    <!-- Current Tenant Display -->
    <v-chip
      v-if="currentTenant"
      class="current-tenant-chip"
      color="primary"
      variant="tonal"
      size="default"
      prepend-icon="mdi-office-building"
    >
      {{ currentTenant.name }}
    </v-chip>

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
          <!-- Header do usuário -->
          <v-list-item class="user-menu-header">
            <template v-slot:prepend>
              <v-avatar size="48" class="user-avatar-large">
                <v-img v-if="userAvatar" :src="userAvatar" :alt="userName" />
                <v-icon v-else color="white" size="24">mdi-account</v-icon>
              </v-avatar>
            </template>
            <div class="user-info-container">
              <v-list-item-title class="user-name-large">
                {{ userName }}
              </v-list-item-title>
              <v-list-item-subtitle class="user-role-large">
                {{ userRole }}
              </v-list-item-subtitle>
              <div class="user-status-container">
                <v-chip
                  size="small"
                  :color="getStatusColor(userStatus)"
                  variant="flat"
                  class="status-chip"
                >
                  <v-icon size="12" class="mr-1">{{ getStatusIcon(userStatus) }}</v-icon>
                  {{ getStatusText(userStatus) }}
                </v-chip>
              </div>
            </div>
          </v-list-item>

          <!-- Empresa Atual -->
          <v-list-item v-if="currentTenant" class="current-tenant-item">
            <template v-slot:prepend>
              <v-icon color="primary">mdi-office-building</v-icon>
            </template>
            <v-list-item-title class="tenant-name-display">
              {{ currentTenant.name }}
            </v-list-item-title>
            <v-list-item-subtitle class="tenant-subtitle">
              Empresa atual
            </v-list-item-subtitle>
          </v-list-item>

          <v-divider class="menu-divider" />

          <!-- Menu Simplificado -->
          <div class="menu-section">
            <v-list-item
              prepend-icon="mdi-account-edit"
              title="Editar Perfil"
              subtitle="Atualize suas informações"
              @click="navigateToProfile"
              class="menu-item"
            />
            <v-list-item
              prepend-icon="mdi-bell"
              title="Notificações"
              subtitle="Preferências de alertas"
              @click="navigateToNotifications"
              class="menu-item"
            />
            <v-list-item
              prepend-icon="mdi-help-circle"
              title="Central de Ajuda"
              subtitle="FAQ e documentação"
              @click="navigateToHelp"
              class="menu-item"
            />
            <v-list-item
              prepend-icon="mdi-headset"
              title="Suporte Técnico"
              subtitle="Fale conosco"
              @click="navigateToSupport"
              class="menu-item"
            />
            <v-list-item
              v-if="hasMultipleTenants"
              prepend-icon="mdi-swap-horizontal"
              title="Trocar Empresa"
              subtitle="Alterar empresa ativa"
              @click="navigateToSelectTenant"
              class="menu-item tenant-switch-item"
            />
          </div>

          <v-divider class="menu-divider" />

          <!-- Theme Toggle -->
          <v-list-item
            @click="toggleTheme"
            class="menu-item theme-item"
          >
            <template v-slot:prepend>
              <div class="action-icon-wrapper theme-icon">
                <v-icon size="18">{{ isDark ? 'mdi-weather-sunny' : 'mdi-weather-night' }}</v-icon>
              </div>
            </template>
            <v-list-item-title class="action-title">{{ isDark ? 'Modo Claro' : 'Modo Escuro' }}</v-list-item-title>
            <v-list-item-subtitle class="action-subtitle">
              {{ isDark ? 'Ativar tema claro' : 'Ativar tema escuro' }}
            </v-list-item-subtitle>
          </v-list-item>

          <v-divider class="menu-divider" />

          <!-- Logout -->
          <v-list-item
            prepend-icon="mdi-logout"
            title="Sair da Conta"
            subtitle="Encerrar sessão"
            class="logout-item"
            @click="logout"
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
import { useTenant } from "@/composables/useTenant";

const theme = useTheme();
const router = useRouter();
const { logout, user } = useAuth();
const { hasMultipleTenants, currentTenant } = useTenant();

// User data (em produção, isso viria de um store/API)
const userName = ref(user.value?.name || "Usuário");
const userRole = ref(user.value?.profile?.display_name || "Perfil");
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


// Status helper methods
const getStatusColor = (status: string) => {
  const colors = {
    online: 'success',
    away: 'warning',
    busy: 'error',
    offline: 'grey'
  };
  return colors[status as keyof typeof colors] || 'grey';
};

const getStatusIcon = (status: string) => {
  const icons = {
    online: 'mdi-circle',
    away: 'mdi-circle-half-full',
    busy: 'mdi-minus-circle',
    offline: 'mdi-circle-outline'
  };
  return icons[status as keyof typeof icons] || 'mdi-circle-outline';
};

const getStatusText = (status: string) => {
  const texts = {
    online: 'Online',
    away: 'Ausente',
    busy: 'Ocupado',
    offline: 'Offline'
  };
  return texts[status as keyof typeof texts] || 'Desconhecido';
};

// Navigation methods
const navigateToProfile = () => {
  router.push('/profile');
};

const navigateToNotifications = () => {
  router.push('/notifications');
};

const navigateToHelp = () => {
  router.push('/help');
};

const navigateToSupport = () => {
  router.push('/support');
};

const navigateToSelectTenant = () => {
  router.push('/select-tenant');
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

.current-tenant-chip {
  font-weight: 600;
  font-size: 0.875rem;
  margin-right: 8px;
  box-shadow: 0 2px 8px rgba(30, 41, 59, 0.1);
  transition: all 0.2s ease;
}

.current-tenant-chip:hover {
  box-shadow: 0 4px 12px rgba(30, 41, 59, 0.15);
  transform: translateY(-1px);
}

.topbar-actions__btn {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 12px;
  color: #475569;
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
  color: #1e293b;
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
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
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
  background: #059669;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.status-away {
  background: #d97706;
  box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
}

.status-busy {
  background: #dc2626;
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
}

.status-offline {
  background: #6b7280;
  box-shadow: 0 0 0 2px rgba(107, 114, 128, 0.2);
}

/* User Menu Styles */
.user-menu-list {
  min-width: 320px;
  max-width: 360px;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(0, 0, 0, 0.08);
  overflow: hidden;
  backdrop-filter: blur(20px);
}

.user-menu-header {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  color: white;
  padding: 20px;
  position: relative;
  overflow: hidden;
}

.user-info-container {
  flex: 1;
}

.user-status-container {
  margin-top: 8px;
}

.status-chip {
  font-size: 0.75rem;
  height: 20px;
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

.menu-divider {
  margin: 8px 0;
  opacity: 0.1;
}

/* Current Tenant Display */
.current-tenant-item {
  background: rgba(30, 41, 59, 0.05);
  padding: 12px 16px;
  margin: 4px 8px;
  border-radius: 8px;
  border-left: 3px solid #1e293b;
}

.tenant-name-display {
  font-weight: 600;
  font-size: 0.9rem;
  color: #1e293b;
}

.tenant-subtitle {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.menu-section {
  padding: 8px 0;
}

.menu-item {
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 2px 8px;
}

.menu-item:hover {
  background: rgba(51, 65, 85, 0.08);
  transform: translateX(4px);
}

.menu-item :deep(.v-list-item-title) {
  font-weight: 500;
  font-size: 0.9rem;
}

.menu-item :deep(.v-list-item-subtitle) {
  font-size: 0.8rem;
  opacity: 0.7;
}

.tenant-switch-item {
  background: rgba(30, 41, 59, 0.03);
  border-left: 3px solid #1e293b;
}

.tenant-switch-item:hover {
  background: rgba(30, 41, 59, 0.08);
}

/* Theme Toggle Item */
.theme-item {
  border-radius: 12px !important;
  margin: 4px 8px !important;
  padding: 12px 16px !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.theme-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 3px;
  height: 100%;
  background: transparent;
  transition: all 0.3s ease;
}

.theme-item:hover {
  background: rgba(103, 58, 183, 0.08);
  transform: translateX(4px);
}

.theme-item:hover::before {
  background: #673AB7;
}

.action-icon-wrapper {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.theme-item:hover .action-icon-wrapper {
  transform: scale(1.1) rotate(5deg);
}

.theme-icon {
  background: rgba(103, 58, 183, 0.1);
  color: #673AB7;
}

.theme-item:hover .theme-icon {
  background: rgba(103, 58, 183, 0.2);
  box-shadow: 0 0 0 4px rgba(103, 58, 183, 0.1);
}

.action-title {
  font-weight: 600 !important;
  font-size: 0.9375rem !important;
  color: #1e293b;
  line-height: 1.4 !important;
}

.action-subtitle {
  font-size: 0.75rem !important;
  color: #64748b !important;
  margin-top: 2px !important;
  line-height: 1.3 !important;
}

.logout-item {
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 2px 8px;
  border-top: 1px solid rgba(239, 68, 68, 0.1);
}

.logout-item:hover {
  background: rgba(239, 68, 68, 0.08);
  transform: translateX(4px);
}

.logout-item :deep(.v-list-item-title) {
  color: #dc2626;
  font-weight: 500;
  font-size: 0.9rem;
}

.logout-item :deep(.v-list-item-subtitle) {
  color: rgba(220, 38, 38, 0.7);
  font-size: 0.8rem;
}

/* Dark theme adjustments */
.v-theme--dark .topbar-actions__btn {
  color: #64748b;
}

.v-theme--dark .topbar-actions__btn:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #f1f5f9;
}

.v-theme--dark .topbar-actions__user-menu:hover {
  background: rgba(255, 255, 255, 0.05);
}

.v-theme--dark .user-role-large {
  color: #64748b;
}

.v-theme--dark .current-tenant-item {
  background: rgba(51, 65, 85, 0.3);
  border-left-color: #475569;
}

.v-theme--dark .tenant-name-display {
  color: #f1f5f9;
}

.v-theme--dark .tenant-subtitle {
  color: #94a3b8;
}

.v-theme--dark .theme-item:hover {
  background: rgba(103, 58, 183, 0.15);
}

.v-theme--dark .theme-icon {
  background: rgba(103, 58, 183, 0.2);
}

.v-theme--dark .theme-item:hover .theme-icon {
  background: rgba(103, 58, 183, 0.3);
}

.v-theme--dark .action-title {
  color: #f1f5f9;
}

.v-theme--dark .action-subtitle {
  color: #94a3b8 !important;
}

/* Responsive */
@media (max-width: 768px) {
  .topbar-actions {
    gap: 8px;
  }

  .current-tenant-chip {
    font-size: 0.75rem;
    margin-right: 4px;
  }

  .current-tenant-chip :deep(.v-chip__prepend) {
    margin-right: 4px;
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

  .current-tenant-chip {
    max-width: 140px;
  }

  .current-tenant-chip :deep(.v-chip__content) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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
