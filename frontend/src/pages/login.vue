<template>
  <div class="login-container">
    <v-container fluid class="fill-height">
      <v-row align="center" justify="center" class="fill-height">
        <v-col cols="12" sm="8" md="6" lg="4" xl="3">
          <v-card class="login-card" elevation="8">
            <v-card-title class="text-center pa-8">
              <div class="login-header">
                <v-icon size="48" color="primary" class="mb-4">mdi-calendar-check</v-icon>
                <h1 class="text-h4 font-weight-bold">AgendIA</h1>
                <p class="text-body-1 text-medium-emphasis mt-2">
                  Sistema inteligente de agendamentos
                </p>
              </div>
            </v-card-title>

            <v-card-text class="pa-8">
              <v-form @submit.prevent="handleLogin">
                <v-text-field
                  v-model="form.email"
                  label="Email"
                  type="email"
                  prepend-inner-icon="mdi-email"
                  variant="outlined"
                  :rules="emailRules"
                  class="mb-4"
                  required
                />

                <v-text-field
                  v-model="form.password"
                  label="Senha"
                  :type="showPassword ? 'text' : 'password'"
                  prepend-inner-icon="mdi-lock"
                  :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                  variant="outlined"
                  :rules="passwordRules"
                  class="mb-6"
                  required
                  @click:append-inner="showPassword = !showPassword"
                />

                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  block
                  rounded="lg"
                  :loading="loading"
                  class="text-none font-weight-medium"
                >
                  Entrar
                </v-btn>
              </v-form>

              <v-divider class="my-6" />

              <v-btn
                color="primary"
                variant="outlined"
                size="large"
                block
                rounded="lg"
                prepend-icon="mdi-google"
                :loading="googleLoading"
                class="text-none font-weight-medium"
                @click="handleGoogleLogin"
              >
                Entrar com Google
              </v-btn>

              <div class="text-center mt-6">
                <v-btn
                  variant="text"
                  color="primary"
                  class="text-none"
                  @click="forgotPassword"
                >
                  Esqueceu sua senha?
                </v-btn>
              </div>
            </v-card-text>
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

  try {
    const success = await login(form.email, form.password)

    if (success) {
      router.push('/dashboard')
    } else {
      // Mostrar erro de login
      console.error('Erro no login')
    }
  } catch (error) {
    console.error('Erro no login:', error)
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
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
}

.login-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
  opacity: 0.3;
}

.login-card {
  position: relative;
  z-index: 1;
  border-radius: 24px;
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.login-header {
  text-align: center;
}

.login-header h1 {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Dark theme */
.v-theme--dark .login-card {
  background: rgba(30, 41, 59, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.v-theme--dark .login-header h1 {
  background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
