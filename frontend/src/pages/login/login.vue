<template>
  <div class="login-container">
    <!-- Background Elements -->
    <div class="background-elements">
      <div class="floating-shape shape-1"></div>
      <div class="floating-shape shape-2"></div>
      <div class="floating-shape shape-3"></div>
      <div class="floating-shape shape-4"></div>
    </div>

    <v-container fluid class="fill-height">
      <v-row align="center" justify="center" class="fill-height">
        <v-col cols="12" sm="8" md="5" lg="4" xl="3">
          <v-card class="login-card" elevation="0">
            <!-- Header Section -->
            <div class="login-header">
              <div class="logo-container">
                <div class="logo-icon">
                  <v-icon size="48" color="white">mdi-calendar-check</v-icon>
                </div>
                <h1 class="logo-text">AgendIA</h1>
                <p class="logo-subtitle">
                  Sistema inteligente de agendamentos
                </p>
              </div>
            </div>

            <!-- Form Section -->
            <v-card-text class="form-section">
              <v-form @submit.prevent="handleLogin" class="login-form">
                <div class="input-group">
                  <v-text-field
                    v-model="form.email"
                    label="Email"
                    type="email"
                    prepend-inner-icon="mdi-email-outline"
                    variant="outlined"
                    :rules="emailRules"
                    class="custom-input"
                    color="primary"
                    required
                    hide-details="auto"
                    @input="clearError"
                  />
                </div>

                <div class="input-group">
                  <v-text-field
                    v-model="form.password"
                    label="Senha"
                    :type="showPassword ? 'text' : 'password'"
                    prepend-inner-icon="mdi-lock-outline"
                    :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                    variant="outlined"
                    :rules="passwordRules"
                    class="custom-input"
                    color="primary"
                    required
                    hide-details="auto"
                    @click:append-inner="showPassword = !showPassword"
                    @input="clearError"
                  />
                </div>

                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  block
                  rounded="lg"
                  :loading="loading"
                  class="login-btn"
                  elevation="2"
                >
                  <span v-if="!loading">Entrar</span>
                  <template v-slot:loader>
                    <v-progress-circular
                      color="white"
                      indeterminate
                      size="20"
                      width="2"
                    />
                  </template>
                </v-btn>

                <!-- Error Message -->
                <v-alert
                  v-if="loginError"
                  type="error"
                  variant="tonal"
                  class="mt-4"
                  closable
                  @click:close="loginError = ''"
                >
                  {{ loginError }}
                </v-alert>
              </v-form>

              <!-- Divider -->
              <div class="divider-container">
                <v-divider class="divider" />
                <span class="divider-text">ou</span>
                <v-divider class="divider" />
              </div>

              <!-- Google Login -->
              <v-btn
                color="white"
                variant="elevated"
                size="large"
                block
                rounded="lg"
                prepend-icon="mdi-google"
                :loading="googleLoading"
                class="google-btn"
                elevation="1"
                @click="handleGoogleLogin"
              >
                <span v-if="!googleLoading">Entrar com Google</span>
                <template v-slot:loader>
                  <v-progress-circular
                    color="primary"
                    indeterminate
                    size="20"
                    width="2"
                  />
                </template>
              </v-btn>

              <!-- Forgot Password -->
              <div class="forgot-password-container">
                <v-btn
                  variant="text"
                  color="primary"
                  class="forgot-password-btn"
                  @click="forgotPassword"
                >
                  <v-icon left>mdi-help-circle-outline</v-icon>
                  Esqueceu sua senha?
                </v-btn>
              </div>
            </v-card-text>

            <!-- Footer -->
            <v-card-actions class="login-footer">
              <div class="footer-content">
                <p class="footer-text">
                  Não tem uma conta?
                  <v-btn
                    variant="text"
                    color="primary"
                    class="register-btn"
                    @click="goToRegister"
                  >
                    Cadastre-se
                  </v-btn>
                </p>
              </div>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const { login, loginWithGoogle } = useAuth()

// Form data
const form = reactive({
  email: '',
  password: ''
})

// UI state
const loading = ref(false)
const googleLoading = ref(false)
const showPassword = ref(false)
const loginError = ref('')

// Validation rules
const emailRules = [
  (v: string) => !!v || 'Email é obrigatório',
  (v: string) => /.+@.+\..+/.test(v) || 'Email deve ser válido'
]

const passwordRules = [
  (v: string) => !!v || 'Senha é obrigatória',
  (v: string) => v.length >= 6 || 'Senha deve ter pelo menos 6 caracteres'
]

// Methods
const handleLogin = async () => {
  loading.value = true
  loginError.value = ''

  try {
    const success = await login(form.email, form.password)

    if (success) {
      router.push('/dashboard')
    } else {
      // Erro de credenciais - o toast já foi mostrado pelo useHttp
      loginError.value = 'Credenciais inválidas. Verifique seu email e senha.'
    }
  } catch (error: any) {
    console.error('Erro no login:', error)
    // Se não for erro 401, mostrar mensagem genérica
    if (error.response?.status !== 401) {
      loginError.value = 'Erro interno. Tente novamente.'
    }
  } finally {
    loading.value = false
  }
}

const handleGoogleLogin = async () => {
  googleLoading.value = true

  try {
    const success = await loginWithGoogle()

    if (success) {
      router.push('/dashboard')
    } else {
      // Mostrar erro de login
      console.error('Erro no login com Google')
    }
  } catch (error) {
    console.error('Erro no login com Google:', error)
  } finally {
    googleLoading.value = false
  }
}

const forgotPassword = () => {
  // Implementar recuperação de senha
  console.log('Recuperar senha')
}

const goToRegister = () => {
  // Implementar navegação para registro
  console.log('Ir para registro')
  // router.push('/register')
}

const clearError = () => {
  loginError.value = ''
}
</script>

<style scoped>
/* Main Container */
.login-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Animated Background Elements */
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
  width: 60px;
  height: 60px;
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 80px;
  height: 80px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape-3 {
  width: 40px;
  height: 40px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

.shape-4 {
  width: 70px;
  height: 70px;
  top: 10%;
  right: 30%;
  animation-delay: 1s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* Login Card */
.login-card {
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
  transition: all 0.3s ease;
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

.login-card:hover {
  transform: translateY(-3px);
  box-shadow:
    0 20px 40px -8px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.2);
}

/* Header Section */
.login-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 1.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.login-header::before {
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

.logo-container {
  position: relative;
  z-index: 1;
}

.logo-icon {
  display: inline-block;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  margin-bottom: 0.75rem;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.logo-text {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  letter-spacing: -0.02em;
}

.logo-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1rem;
  margin: 0.25rem 0 0 0;
  font-weight: 400;
}

/* Form Section */
.form-section {
  padding: 1.5rem 1.5rem;
}

.login-form {
  margin-bottom: 1rem;
}

.input-group {
  margin-bottom: 1rem;
}

.custom-input {
  transition: all 0.3s ease;
}

.custom-input :deep(.v-field) {
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(102, 126, 234, 0.2);
  transition: all 0.3s ease;
}

.custom-input :deep(.v-field:hover) {
  border-color: rgba(102, 126, 234, 0.4);
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
}

.custom-input :deep(.v-field--focused) {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Buttons */
.login-btn {
  margin-top: 0.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: 600;
  font-size: 1rem;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.login-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.login-btn:hover::before {
  left: 100%;
}

.login-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
}

.google-btn {
  background: white;
  color: #333;
  font-weight: 500;
  text-transform: none;
  border: 2px solid rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.google-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  border-color: rgba(0, 0, 0, 0.2);
}

/* Divider */
.divider-container {
  display: flex;
  align-items: center;
  margin: 1.5rem 0;
  position: relative;
}

.divider {
  flex: 1;
  border-color: rgba(102, 126, 234, 0.2);
}

.divider-text {
  padding: 0 1rem;
  color: rgba(102, 126, 234, 0.7);
  font-weight: 500;
  font-size: 0.9rem;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 20px;
}

/* Forgot Password */
.forgot-password-container {
  text-align: center;
  margin-top: 1rem;
}

.forgot-password-btn {
  font-weight: 500;
  text-transform: none;
  transition: all 0.3s ease;
}

.forgot-password-btn:hover {
  transform: translateY(-1px);
}

/* Footer */
.login-footer {
  padding: 1rem 1.5rem;
  background: rgba(102, 126, 234, 0.05);
  border-top: 1px solid rgba(102, 126, 234, 0.1);
}

.footer-content {
  width: 100%;
  text-align: center;
}

.footer-text {
  color: rgba(102, 126, 234, 0.8);
  margin: 0;
  font-size: 0.95rem;
}

.register-btn {
  font-weight: 600;
  text-transform: none;
  margin-left: 0.5rem;
}

/* Dark Theme */
.v-theme--dark .login-card {
  background: rgba(30, 41, 59, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.v-theme--dark .custom-input :deep(.v-field) {
  background: rgba(30, 41, 59, 0.8);
  border-color: rgba(139, 92, 246, 0.3);
}

.v-theme--dark .custom-input :deep(.v-field:hover) {
  border-color: rgba(139, 92, 246, 0.5);
}

.v-theme--dark .custom-input :deep(.v-field--focused) {
  border-color: #8b5cf6;
}

.v-theme--dark .divider-text {
  background: rgba(30, 41, 59, 0.9);
  color: rgba(139, 92, 246, 0.7);
}

.v-theme--dark .login-footer {
  background: rgba(139, 92, 246, 0.05);
  border-top-color: rgba(139, 92, 246, 0.1);
}

.v-theme--dark .footer-text {
  color: rgba(139, 92, 246, 0.8);
}

/* Responsive Design */
@media (max-width: 768px) {
  .login-container {
    padding: 0.5rem;
  }

  .login-card {
    border-radius: 16px;
    margin: 0.25rem;
  }

  .login-header {
    padding: 1.5rem 1rem;
  }

  .logo-text {
    font-size: 1.8rem;
  }

  .form-section {
    padding: 1.25rem 1rem;
  }

  .login-footer {
    padding: 0.75rem 1rem;
  }

  .floating-shape {
    display: none;
  }
}

@media (max-width: 480px) {
  .login-header {
    padding: 1.25rem 0.75rem;
  }

  .logo-text {
    font-size: 1.6rem;
  }

  .form-section {
    padding: 1rem 0.75rem;
  }

  .login-footer {
    padding: 0.75rem;
  }
}
</style>
