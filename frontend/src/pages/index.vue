<template>
  <div class="home-page">
    <!-- Page Header -->
    <PageHeader
      title="Bem-vindo ao AgendIA"
      subtitle="Sistema inteligente de agendamentos com integração Google Calendar e WhatsApp"
      :breadcrumbs="[{ title: 'Início' }]"
    />

    <!-- Hero Section -->
    <v-row class="mb-8">
      <v-col cols="12">
        <v-card class="hero-card" elevation="4">
          <v-card-text class="pa-8">
            <div class="text-center">
              <v-avatar size="80" color="primary" class="mb-4">
                <v-icon color="white" size="40">mdi-calendar-check</v-icon>
              </v-avatar>
              <h1 class="text-h3 font-weight-bold text-primary mb-4">
                Bem-vindo ao AgendIA
              </h1>
              <p class="text-h6 text-medium-emphasis mb-6">
                Sistema inteligente de agendamentos com integração Google
                Calendar e WhatsApp
              </p>
              <v-btn
                color="primary"
                size="large"
                prepend-icon="mdi-rocket-launch"
                to="/dashboard"
              >
                Começar Agora
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Features Grid -->
    <v-row class="mb-8">
      <v-col cols="12" md="4">
        <v-card class="feature-card" elevation="2">
          <v-card-text class="pa-6 text-center">
            <v-avatar color="success" size="64" class="mb-4">
              <v-icon color="white" size="32">mdi-calendar-clock</v-icon>
            </v-avatar>
            <h3 class="text-h5 font-weight-bold mb-3">
              Agendamentos Inteligentes
            </h3>
            <p class="text-body-1 text-medium-emphasis">
              Gerencie seus agendamentos de forma eficiente com interface
              intuitiva e funcionalidades avançadas.
            </p>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="feature-card" elevation="2">
          <v-card-text class="pa-6 text-center">
            <v-avatar color="info" size="64" class="mb-4">
              <v-icon color="white" size="32">mdi-google</v-icon>
            </v-avatar>
            <h3 class="text-h5 font-weight-bold mb-3">Integração Google</h3>
            <p class="text-body-1 text-medium-emphasis">
              Sincronize automaticamente com o Google Calendar para manter sua
              agenda sempre atualizada.
            </p>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="feature-card" elevation="2">
          <v-card-text class="pa-6 text-center">
            <v-avatar color="warning" size="64" class="mb-4">
              <v-icon color="white" size="32">mdi-whatsapp</v-icon>
            </v-avatar>
            <h3 class="text-h5 font-weight-bold mb-3">Notificações WhatsApp</h3>
            <p class="text-body-1 text-medium-emphasis">
              Envie lembretes e confirmações automaticamente via WhatsApp para
              seus clientes.
            </p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Actions -->
    <v-row>
      <v-col cols="12">
        <v-card elevation="2">
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2">mdi-lightning-bolt</v-icon>
            Ações Rápidas
          </v-card-title>
          <v-card-text class="pa-6">
            <v-row>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  color="primary"
                  variant="outlined"
                  block
                  size="large"
                  prepend-icon="mdi-plus"
                  to="/appointments"
                >
                  Novo Agendamento
                </v-btn>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  color="success"
                  variant="outlined"
                  block
                  size="large"
                  prepend-icon="mdi-account-plus"
                  to="/clients"
                >
                  Adicionar Cliente
                </v-btn>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  color="info"
                  variant="outlined"
                  block
                  size="large"
                  prepend-icon="mdi-chart-line"
                  to="/reports"
                >
                  Ver Relatórios
                </v-btn>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  color="warning"
                  variant="outlined"
                  block
                  size="large"
                  prepend-icon="mdi-cog"
                  to="/settings"
                >
                  Configurações
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script lang="ts" setup>
import PageHeader from "@/components/PageHeader.vue";

// Lógica da página inicial
console.log("Página inicial carregada");
</script>

<style scoped>
.home-page {
  max-width: 1200px;
  margin: 0 auto;
}

.hero-card {
  background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
  color: white;
}

.hero-card :deep(.v-card-text) {
  color: white;
}

.feature-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  height: 100%;
}

.feature-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.v-btn {
  transition: all 0.3s ease;
}

.v-btn:hover {
  transform: translateY(-1px);
}
</style>
