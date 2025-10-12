<template>
  <div class="select-tenant-container">
    <!-- Background Elements -->
    <div class="background-elements">
      <div class="floating-shape shape-1"></div>
      <div class="floating-shape shape-2"></div>
      <div class="floating-shape shape-3"></div>
    </div>

    <v-container fluid class="fill-height">
      <v-row align="center" justify="center" class="fill-height">
        <v-col cols="12" sm="10" md="8" lg="6" xl="5">
          <v-card class="select-card" elevation="0">
            <!-- Header -->
            <div class="select-header">
              <div class="header-content">
                <v-icon size="48" color="white" class="mb-3">mdi-office-building-outline</v-icon>
                <h1 class="header-title">Selecione a Empresa</h1>
                <p class="header-subtitle">
                  Escolha a empresa que deseja acessar
                </p>
              </div>
            </div>

            <!-- Content -->
            <v-card-text class="content-section">
              <!-- User Info -->
              <div class="user-info-box">
                <v-avatar size="48" color="primary" class="mr-3">
                  <v-icon color="white">mdi-account</v-icon>
                </v-avatar>
                <div class="user-details">
                  <div class="user-name">{{ userName }}</div>
                  <div class="user-email">{{ userEmail }}</div>
                </div>
              </div>

              <v-divider class="my-6" />

              <!-- Info Alert -->
              <v-alert
                v-if="!loading && availableTenants.length > 0"
                type="info"
                variant="tonal"
                density="compact"
                class="mb-4"
                icon="mdi-information-outline"
              >
                <span class="text-body-2">
                  Se alguma empresa não estiver aparecendo, ela pode ter sido apagada ou inativada.
                </span>
              </v-alert>

              <!-- Tenants List -->
              <div v-if="loading" class="text-center py-8">
                <v-progress-circular
                  color="primary"
                  indeterminate
                  size="48"
                />
                <p class="mt-4 text-body-2">Carregando empresas...</p>
              </div>

              <div v-else-if="availableTenants.length === 0" class="text-center py-8">
                <v-icon size="64" color="grey">mdi-office-building-off-outline</v-icon>
                <p class="mt-4 text-h6 text-grey">Nenhuma empresa disponível</p>
                <p class="text-body-2 text-grey">
                  Entre em contato com o administrador
                </p>
              </div>

              <div v-else class="tenants-grid">
                <v-card
                  v-for="tenant in availableTenants"
                  :key="tenant.id"
                  class="tenant-card"
                  :class="{ 'tenant-card--selected': selectedTenantId === tenant.id }"
                  elevation="0"
                  @click="selectTenant(tenant)"
                  hover
                >
                  <div class="tenant-card-content">
                    <div class="tenant-icon">
                      <v-avatar
                        v-if="tenant.logo"
                        size="64"
                        rounded="lg"
                      >
                        <v-img :src="tenant.logo" :alt="tenant.name" />
                      </v-avatar>
                      <v-avatar
                        v-else
                        size="64"
                        color="primary"
                        rounded="lg"
                      >
                        <v-icon size="32" color="white">mdi-domain</v-icon>
                      </v-avatar>
                    </div>

                    <div class="tenant-info">
                      <h3 class="tenant-name">{{ tenant.name }}</h3>
                      <p class="tenant-slug">@{{ tenant.slug }}</p>
                      <v-chip
                        size="x-small"
                        color="success"
                        variant="flat"
                        class="mt-2"
                      >
                        <v-icon size="12" class="mr-1">mdi-check-circle</v-icon>
                        Ativa
                      </v-chip>
                    </div>

                    <!-- Selected Indicator -->
                    <div v-if="selectedTenantId === tenant.id" class="selected-indicator">
                      <v-icon color="success" size="32">mdi-check-circle</v-icon>
                    </div>
                  </div>
                </v-card>
              </div>

              <!-- Action Button -->
              <v-btn
                v-if="!loading && availableTenants.length > 0"
                color="primary"
                size="large"
                block
                rounded="lg"
                class="mt-6 action-btn"
                :disabled="!selectedTenantId || selecting"
                :loading="selecting"
                @click="confirmSelection"
              >
                <v-icon left>mdi-arrow-right</v-icon>
                {{ selecting ? 'Acessando...' : 'Acessar Empresa' }}
              </v-btn>
            </v-card-text>

            <!-- Footer -->
            <v-card-actions class="select-footer">
              <v-btn
                variant="text"
                color="grey"
                prepend-icon="mdi-logout"
                @click="handleLogout"
              >
                Sair
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useTenant, type Tenant } from '@/composables/useTenant'

const router = useRouter()
const { user, logout } = useAuth()
const {
  availableTenants,
  setCurrentTenant,
  loadAvailableTenants,
  switchTenant
} = useTenant()

// Estado
const loading = ref(true)
const selecting = ref(false)
const selectedTenantId = ref<number | null>(null)

// Computed
const userName = computed(() => user.value?.name || 'Usuário')
const userEmail = computed(() => user.value?.email || '')

// Métodos
const selectTenant = (tenant: Tenant) => {
  selectedTenantId.value = tenant.id
}

const confirmSelection = async () => {
  if (!selectedTenantId.value) return

  selecting.value = true

  try {
    const tenant = availableTenants.value.find(t => t.id === selectedTenantId.value)

    if (tenant) {
      await switchTenant(tenant)

      // Redireciona para o dashboard
      router.push('/dashboard')
    }
  } catch (error) {
    console.error('Erro ao selecionar empresa:', error)
  } finally {
    selecting.value = false
  }
}

const handleLogout = async () => {
  await logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  // Carrega os tenants disponíveis
  loadAvailableTenants()

  // Se só houver uma empresa, seleciona automaticamente
  if (availableTenants.value.length === 1) {
    const tenant = availableTenants.value[0]
    if (tenant) {
      selectedTenantId.value = tenant.id
    }
  }

  loading.value = false
})
</script>

<style scoped>
/* Container */
.select-tenant-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Background Elements */
.background-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 0;
}

.floating-shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 80px;
  height: 80px;
  top: 15%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 100px;
  height: 100px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape-3 {
  width: 60px;
  height: 60px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* Card */
.select-card {
  position: relative;
  z-index: 1;
  border-radius: 20px;
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow:
    0 15px 35px -5px rgba(0, 0, 0, 0.2),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  overflow: hidden;
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Header */
.select-header {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  padding: 2rem 1.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.select-header::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  animation: rotate 20s linear infinite;
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.header-content {
  position: relative;
  z-index: 1;
}

.header-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: white;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.header-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1rem;
  margin: 0.5rem 0 0 0;
}

/* Content */
.content-section {
  padding: 2rem 1.5rem;
}

/* User Info */
.user-info-box {
  display: flex;
  align-items: center;
  padding: 1rem;
  background: rgba(30, 41, 59, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(30, 41, 59, 0.1);
}

.user-details {
  flex: 1;
}

.user-name {
  font-weight: 600;
  font-size: 1rem;
  color: #1e293b;
}

.user-email {
  font-size: 0.875rem;
  color: #64748b;
}

/* Tenants Grid */
.tenants-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.tenant-card {
  border: 2px solid rgba(30, 41, 59, 0.1);
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  background: white;
}

.tenant-card:hover {
  border-color: rgba(30, 41, 59, 0.3);
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(30, 41, 59, 0.15);
}

.tenant-card--selected {
  border-color: #059669;
  background: rgba(5, 150, 105, 0.05);
  box-shadow: 0 8px 20px rgba(5, 150, 105, 0.2);
}

.tenant-card-content {
  padding: 1.5rem;
  position: relative;
}

.tenant-icon {
  display: flex;
  justify-content: center;
  margin-bottom: 1rem;
}

.tenant-info {
  text-align: center;
}

.tenant-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.tenant-slug {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.selected-indicator {
  position: absolute;
  top: 1rem;
  right: 1rem;
}

/* Action Button */
.action-btn {
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.action-btn:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(30, 41, 59, 0.3);
}

/* Footer */
.select-footer {
  padding: 1rem 1.5rem;
  background: rgba(30, 41, 59, 0.03);
  border-top: 1px solid rgba(30, 41, 59, 0.1);
  justify-content: center;
}

/* Dark Theme */
.v-theme--dark .select-card {
  background: rgba(30, 41, 59, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.v-theme--dark .user-info-box {
  background: rgba(51, 65, 85, 0.3);
  border-color: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .user-name {
  color: #f1f5f9;
}

.v-theme--dark .user-email {
  color: #94a3b8;
}

.v-theme--dark .tenant-card {
  background: rgba(51, 65, 85, 0.5);
  border-color: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .tenant-card:hover {
  border-color: rgba(255, 255, 255, 0.2);
}

.v-theme--dark .tenant-card--selected {
  background: rgba(5, 150, 105, 0.15);
  border-color: #10b981;
}

.v-theme--dark .tenant-name {
  color: #f1f5f9;
}

.v-theme--dark .tenant-slug {
  color: #94a3b8;
}

.v-theme--dark .select-footer {
  background: rgba(51, 65, 85, 0.3);
  border-top-color: rgba(255, 255, 255, 0.1);
}

/* Responsive */
@media (max-width: 768px) {
  .select-tenant-container {
    padding: 0.5rem;
  }

  .select-header {
    padding: 1.5rem 1rem;
  }

  .header-title {
    font-size: 1.5rem;
  }

  .content-section {
    padding: 1.5rem 1rem;
  }

  .tenants-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .select-header {
    padding: 1.25rem 0.75rem;
  }

  .header-title {
    font-size: 1.35rem;
  }

  .content-section {
    padding: 1.25rem 0.75rem;
  }
}
</style>

