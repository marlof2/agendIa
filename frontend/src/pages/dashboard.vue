<template>
  <div class="dashboard">
    <!-- Page Header -->
    <PageHeader
      title="Dashboard"
      subtitle="Visão geral do sistema de agendamentos"
      :breadcrumbs="[{ title: 'Dashboard' }]"
    />

    <!-- Welcome Section -->
    <v-row class="mb-8">
      <v-col cols="12">
        <v-card class="welcome-card" elevation="0">
          <div class="welcome-card__background"></div>
          <v-card-text class="pa-8">
            <div class="d-flex align-center mb-6">
              <div class="welcome-icon">
                <v-icon color="white" size="32">mdi-calendar-check</v-icon>
              </div>
              <div class="ml-6">
                <h1 class="welcome-title">Bem-vindo ao AgendIA</h1>
                <p class="welcome-subtitle">
                  Sistema inteligente de agendamentos com IA integrada
                </p>
                <div class="welcome-stats">
                  <div class="stat-item">
                    <span class="stat-number">24</span>
                    <span class="stat-label">Agendamentos Hoje</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-number">156</span>
                    <span class="stat-label">Clientes Ativos</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-number">98%</span>
                    <span class="stat-label">Satisfação</span>
                  </div>
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Stats Cards -->
    <v-row class="mb-8">
      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card stats-card--success" elevation="0">
          <div
            class="stats-card__background stats-card__background--success"
          ></div>
          <v-card-text class="pa-6">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="stats-icon stats-icon--success">
                <v-icon color="white" size="24">mdi-calendar-today</v-icon>
              </div>
              <div class="stats-trend stats-trend--up">
                <v-icon size="16">mdi-trending-up</v-icon>
                <span>+12%</span>
              </div>
            </div>
            <div class="stats-content">
              <div class="stats-number">24</div>
              <div class="stats-label">Agendamentos Hoje</div>
              <div class="stats-description">vs. ontem</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card stats-card--info" elevation="0">
          <div
            class="stats-card__background stats-card__background--info"
          ></div>
          <v-card-text class="pa-6">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="stats-icon stats-icon--info">
                <v-icon color="white" size="24">mdi-account-group</v-icon>
              </div>
              <div class="stats-trend stats-trend--up">
                <v-icon size="16">mdi-trending-up</v-icon>
                <span>+8%</span>
              </div>
            </div>
            <div class="stats-content">
              <div class="stats-number">156</div>
              <div class="stats-label">Clientes Ativos</div>
              <div class="stats-description">este mês</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card stats-card--warning" elevation="0">
          <div
            class="stats-card__background stats-card__background--warning"
          ></div>
          <v-card-text class="pa-6">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="stats-icon stats-icon--warning">
                <v-icon color="white" size="24">mdi-account-tie</v-icon>
              </div>
              <div class="stats-trend stats-trend--neutral">
                <v-icon size="16">mdi-minus</v-icon>
                <span>0%</span>
              </div>
            </div>
            <div class="stats-content">
              <div class="stats-number">8</div>
              <div class="stats-label">Profissionais</div>
              <div class="stats-description">disponíveis</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card stats-card--error" elevation="0">
          <div
            class="stats-card__background stats-card__background--error"
          ></div>
          <v-card-text class="pa-6">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="stats-icon stats-icon--error">
                <v-icon color="white" size="24">mdi-cancel</v-icon>
              </div>
              <div class="stats-trend stats-trend--down">
                <v-icon size="16">mdi-trending-down</v-icon>
                <span>-5%</span>
              </div>
            </div>
            <div class="stats-content">
              <div class="stats-number">3</div>
              <div class="stats-label">Cancelamentos</div>
              <div class="stats-description">hoje</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Appointments -->
    <v-row>
      <v-col cols="12" md="8">
        <v-card elevation="2">
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2">mdi-calendar-clock</v-icon>
            Próximos Agendamentos
          </v-card-title>
          <v-card-text>
            <v-list>
              <v-list-item
                v-for="appointment in recentAppointments"
                :key="appointment.id"
                class="px-0"
              >
                <template v-slot:prepend>
                  <v-avatar :color="appointment.color" size="40">
                    <v-icon color="white">{{ appointment.icon }}</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title>{{ appointment.client }}</v-list-item-title>
                <v-list-item-subtitle>
                  {{ appointment.service }} - {{ appointment.time }}
                </v-list-item-subtitle>
                <template v-slot:append>
                  <v-chip
                    :color="appointment.statusColor"
                    size="small"
                    variant="flat"
                  >
                    {{ appointment.status }}
                  </v-chip>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card elevation="2">
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2">mdi-chart-pie</v-icon>
            Resumo do Mês
          </v-card-title>
          <v-card-text>
            <div class="text-center">
              <v-progress-circular
                :model-value="75"
                :size="80"
                :width="8"
                color="primary"
                class="mb-4"
              >
                <span class="text-h6">75%</span>
              </v-progress-circular>
              <p class="text-body-2 text-medium-emphasis">Taxa de ocupação</p>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script lang="ts" setup>
import { ref } from "vue";
import PageHeader from "@/components/PageHeader.vue";



// Sample data
const recentAppointments = ref([
  {
    id: 1,
    client: "Maria Silva",
    service: "Consulta Médica",
    time: "09:00",
    status: "Confirmado",
    statusColor: "success",
    color: "primary",
    icon: "mdi-account",
  },
  {
    id: 2,
    client: "João Santos",
    service: "Exame",
    time: "10:30",
    status: "Pendente",
    statusColor: "warning",
    color: "info",
    icon: "mdi-account",
  },
  {
    id: 3,
    client: "Ana Costa",
    service: "Retorno",
    time: "14:00",
    status: "Confirmado",
    statusColor: "success",
    color: "success",
    icon: "mdi-account",
  },
]);
</script>

<style scoped>
.dashboard {
  max-width: 1400px;
  margin: 0 auto;
}

/* Welcome Card */
.welcome-card {
  position: relative;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
}

.welcome-card__background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
  opacity: 0.3;
}

.welcome-card :deep(.v-card-text) {
  color: white;
  position: relative;
  z-index: 1;
}

.welcome-icon {
  width: 80px;
  height: 80px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.welcome-title {
  font-size: 2.5rem;
  font-weight: 800;
  margin: 0 0 8px 0;
  background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.welcome-subtitle {
  font-size: 1.2rem;
  margin: 0 0 24px 0;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
}

.welcome-stats {
  display: flex;
  gap: 32px;
  flex-wrap: wrap;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.stat-number {
  font-size: 2rem;
  font-weight: 800;
  color: white;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

/* Stats Cards */
.stats-card {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
}

.stats-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.stats-card__background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  opacity: 0.1;
}

.stats-card__background--success {
  background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
}

.stats-card__background--info {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.stats-card__background--warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stats-card__background--error {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.stats-card :deep(.v-card-text) {
  position: relative;
  z-index: 1;
}

.stats-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.stats-icon--success {
  background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
}

.stats-icon--info {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.stats-icon--warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stats-icon--error {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.stats-trend {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.stats-trend--up {
  background: rgba(34, 197, 94, 0.1);
  color: #16a34a;
}

.stats-trend--down {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.stats-trend--neutral {
  background: rgba(107, 114, 128, 0.1);
  color: #6b7280;
}

.stats-content {
  margin-top: 8px;
}

.stats-number {
  font-size: 2.5rem;
  font-weight: 800;
  line-height: 1;
  margin-bottom: 8px;
  background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stats-label {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 4px;
}

.stats-description {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

/* Recent Appointments */
.v-list-item {
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.v-list-item:hover {
  background: rgba(102, 126, 234, 0.05);
  transform: translateX(4px);
}

.v-list-item:last-child {
  border-bottom: none;
}

/* Dark theme adjustments */
.v-theme--dark .welcome-card {
  background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
}

.v-theme--dark .welcome-title {
  background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.v-theme--dark .welcome-subtitle {
  color: rgba(255, 255, 255, 0.9);
}

.v-theme--dark .stat-number {
  color: white;
}

.v-theme--dark .stat-label {
  color: rgba(255, 255, 255, 0.8);
}

.v-theme--dark .stats-number {
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.v-theme--dark .stats-label {
  color: #e2e8f0;
}

.v-theme--dark .stats-description {
  color: #94a3b8;
}

.v-theme--dark .stats-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
}

.v-theme--dark .stats-card:hover {
  background: rgba(30, 41, 59, 0.7);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.v-theme--dark .v-list-item:hover {
  background: rgba(139, 92, 246, 0.1);
}

/* Responsive */
@media (max-width: 768px) {
  .welcome-title {
    font-size: 2rem;
  }

  .welcome-stats {
    gap: 16px;
  }

  .stat-number {
    font-size: 1.5rem;
  }

  .stats-number {
    font-size: 2rem;
  }
}

/* Animations */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stats-card {
  animation: slideInUp 0.6s ease-out;
}

.stats-card:nth-child(1) {
  animation-delay: 0.1s;
}
.stats-card:nth-child(2) {
  animation-delay: 0.2s;
}
.stats-card:nth-child(3) {
  animation-delay: 0.3s;
}
.stats-card:nth-child(4) {
  animation-delay: 0.4s;
}
</style>
