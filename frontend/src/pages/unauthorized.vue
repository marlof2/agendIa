<template>
  <div class="unauthorized-page">
    <!-- Background Animation -->
    <div class="unauthorized-background">
      <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="unauthorized-container">
      <v-card class="unauthorized-card" elevation="12">
        <v-card-text class="text-center pa-12">
          <div class="unauthorized-content">
            <!-- Animated Icon -->
            <div class="unauthorized-icon-container">
              <div class="unauthorized-icon-wrapper">
                <v-icon size="140" class="unauthorized-icon">mdi-shield-alert</v-icon>
                <div class="unauthorized-icon-glow"></div>
              </div>
            </div>

            <!-- Error Code -->
            <div class="unauthorized-code">
              <span class="code-4">4</span>
              <span class="code-0">0</span>
              <span class="code-3">3</span>
            </div>

            <!-- Error Title -->
            <h1 class="unauthorized-title">
              Acesso Negado
            </h1>

            <!-- Error Description -->
            <p class="unauthorized-description">
              Você não tem permissão para acessar esta página.
              <br>
              Verifique suas credenciais ou entre em contato com o administrador.
            </p>

            <!-- Action Buttons -->
            <div class="action-buttons">
              <v-btn
                color="primary"
                size="x-large"
                rounded="xl"
                prepend-icon="mdi-arrow-left"
                class="action-btn primary-btn"
                @click="goBack"
                elevation="4"
              >
                <span class="btn-text">Voltar</span>
              </v-btn>

              <v-btn
                color="secondary"
                variant="outlined"
                size="x-large"
                rounded="xl"
                prepend-icon="mdi-home"
                class="action-btn secondary-btn"
                @click="goHome"
                elevation="2"
              >
                <span class="btn-text">Dashboard</span>
              </v-btn>

              <v-btn
                color="error"
                variant="outlined"
                size="x-large"
                rounded="xl"
                prepend-icon="mdi-logout"
                class="action-btn error-btn"
                @click="handleLogout"
                elevation="2"
              >
                <span class="btn-text">Sair do Sistema</span>
              </v-btn>
            </div>

            <!-- Help Text -->
            <div class="help-text">
              <v-icon size="20" class="mr-2">mdi-help-circle-outline</v-icon>
              <span>Precisa de ajuda? Entre em contato com nosso suporte</span>
            </div>
          </div>
        </v-card-text>
      </v-card>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { useRouter } from "vue-router";
import { useAuth } from "@/composables/useAuth";

const router = useRouter();
const { logout } = useAuth();

const goBack = () => {
  router.go(-1);
};

const goHome = () => {
  router.push("/dashboard");
};

const handleLogout = async () => {
  await logout();
  router.push("/login");
};
</script>

<style scoped>
/* Main Container */
.unauthorized-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  padding: 20px;
}

/* Background Animation */
.unauthorized-background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
}

.floating-shapes {
  position: relative;
  width: 100%;
  height: 100%;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 80px;
  height: 80px;
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 120px;
  height: 120px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape-3 {
  width: 60px;
  height: 60px;
  top: 30%;
  right: 30%;
  animation-delay: 4s;
}

.shape-4 {
  width: 100px;
  height: 100px;
  bottom: 20%;
  left: 20%;
  animation-delay: 1s;
}

.shape-5 {
  width: 40px;
  height: 40px;
  top: 10%;
  right: 50%;
  animation-delay: 3s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
    opacity: 0.7;
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
    opacity: 1;
  }
}

/* Unauthorized Container */
.unauthorized-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 600px;
}

/* Unauthorized Card */
.unauthorized-card {
  border-radius: 32px;
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.unauthorized-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 35px 70px rgba(0, 0, 0, 0.2);
}

/* Unauthorized Content */
.unauthorized-content {
  position: relative;
}

/* Unauthorized Icon */
.unauthorized-icon-container {
  margin-bottom: 2rem;
  position: relative;
}

.unauthorized-icon-wrapper {
  position: relative;
  display: inline-block;
}

.unauthorized-icon {
  color: #f59e0b;
  animation: pulse 2s ease-in-out infinite;
  filter: drop-shadow(0 4px 8px rgba(245, 158, 11, 0.3));
}

.unauthorized-icon-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 160px;
  height: 160px;
  background: radial-gradient(circle, rgba(245, 158, 11, 0.2) 0%, transparent 70%);
  border-radius: 50%;
  animation: glow 2s ease-in-out infinite alternate;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

@keyframes glow {
  0% {
    opacity: 0.5;
    transform: translate(-50%, -50%) scale(0.8);
  }
  100% {
    opacity: 0.8;
    transform: translate(-50%, -50%) scale(1.2);
  }
}

/* Unauthorized Code */
.unauthorized-code {
  margin-bottom: 1.5rem;
  font-family: 'Roboto', sans-serif;
  font-weight: 900;
  font-size: 4.5rem;
  line-height: 1;
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}

.code-4,
.code-0,
.code-3 {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: bounce 1s ease-in-out;
}

.code-0 {
  animation-delay: 0.2s;
}

.code-3 {
  animation-delay: 0.4s;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-10px);
  }
  60% {
    transform: translateY(-5px);
  }
}

/* Unauthorized Title */
.unauthorized-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.2;
}

/* Unauthorized Description */
.unauthorized-description {
  font-size: 1rem;
  color: #64748b;
  margin-bottom: 3rem;
  line-height: 1.6;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.action-btn {
  min-height: 56px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.action-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.action-btn:hover::before {
  left: 100%;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.primary-btn {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
}

.secondary-btn {
  border: 2px solid #6b7280;
  color: #6b7280;
}

.secondary-btn:hover {
  background: #6b7280;
  color: white;
}

.error-btn {
  border: 2px solid #ef4444;
  color: #ef4444;
}

.error-btn:hover {
  background: #ef4444;
  color: white;
}

.btn-text {
  position: relative;
  z-index: 1;
}

/* Help Text */
.help-text {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  font-size: 0.9rem;
  margin-top: 1rem;
}

/* Dark Theme */
.v-theme--dark .unauthorized-card {
  background: rgba(30, 41, 59, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.v-theme--dark .unauthorized-title {
  background: linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.v-theme--dark .unauthorized-description {
  color: #94a3b8;
}

.v-theme--dark .help-text {
  color: #64748b;
}

/* Responsive Design */
@media (max-width: 768px) {
  .unauthorized-page {
    padding: 15px;
  }

  .unauthorized-card {
    border-radius: 24px;
  }

  .unauthorized-code {
    font-size: 3.5rem;
  }

  .unauthorized-title {
    font-size: 1.75rem;
  }

  .unauthorized-description {
    font-size: 0.9rem;
    margin-bottom: 2rem;
  }

  .action-buttons {
    gap: 0.75rem;
  }

  .action-btn {
    min-height: 48px;
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .unauthorized-code {
    font-size: 2.5rem;
  }

  .unauthorized-title {
    font-size: 1.25rem;
  }

  .unauthorized-icon {
    font-size: 100px !important;
  }

  .unauthorized-icon-glow {
    width: 120px;
    height: 120px;
  }
}

/* Animation for page load */
.unauthorized-page {
  animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
