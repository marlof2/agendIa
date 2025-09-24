import type { RouteRecordRaw } from 'vue-router'

// Importações das páginas
import Index from '@/pages/index.vue'
import Login from '@/pages/login.vue'
import Dashboard from '@/pages/dashboard.vue'
import ClientsIndex from '@/pages/clients/index.vue'
import AppointmentsIndex from '@/pages/appointments/index.vue'
import Unauthorized from '@/pages/unauthorized.vue'
import NotFound from '@/pages/404.vue'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'index',
    component: Index,
    meta: {
      requiresAuth: false,
      title: 'Início',
      description: 'Página inicial do AgendIA'
    }
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: {
      requiresAuth: false,
      title: 'Login',
      description: 'Faça login no sistema AgendIA',
      layout: 'public'
    }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      requiresAuth: true,
      requiresAbility: 'appointments.index',
      title: 'Dashboard',
      description: 'Visão geral do sistema'
    }
  },
  {
    path: '/clients',
    name: 'clients',
    component: ClientsIndex,
    meta: {
      requiresAuth: true,
      requiresAbility: 'clients.index',
      title: 'Clientes',
      description: 'Gerencie todos os clientes cadastrados'
    }
  },
  {
    path: '/appointments',
    name: 'appointments',
    component: AppointmentsIndex,
    meta: {
      requiresAuth: true,
      requiresAbility: 'appointments.index',
      title: 'Agendamentos',
      description: 'Gerencie todos os agendamentos do sistema'
    }
  },
  {
    path: '/unauthorized',
    name: 'unauthorized',
    component: Unauthorized,
    meta: {
      requiresAuth: false,
      title: 'Acesso Negado',
      description: 'Você não tem permissão para acessar esta página',
      layout: 'error'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: {
      requiresAuth: false,
      title: 'Página não encontrada',
      description: 'A página solicitada não foi encontrada',
      layout: 'error'
    }
  }
]

export default routes
