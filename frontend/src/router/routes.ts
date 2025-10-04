import type { RouteRecordRaw } from 'vue-router'

// Importações das páginas

import routesProfiles from '@/pages/profiles/routes/index'
import routesCompanies from '@/pages/companies/routes/index'
import routesUsers from '@/pages/users/routes/index'


const routes: RouteRecordRaw[] = [
  ...routesProfiles,
  ...routesCompanies,
  ...routesUsers,
  {
    path: '/',
    name: 'index',
    component: () => import('@/pages/index.vue'),
    meta: {
      requiresAuth: false,
      title: 'Início',
      description: 'Página inicial do AgendIA'
    }
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/login/login.vue'),
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
    component: () => import('@/pages/dashboard.vue'),
    meta: {
      requiresAuth: true,
      requiresAbility: 'dashboard.index',
      title: 'Dashboard',
      description: 'Visão geral do sistema'
    }
  },
  {
    path: '/clients',
    name: 'clients',
    component: () => import('@/pages/clients/index.vue'),
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
    component: () => import('@/pages/appointments/index.vue'),
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
    component: () => import('@/pages/error/unauthorized.vue'),
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
    component: () => import('@/pages/error/404.vue'),
    meta: {
      requiresAuth: false,
      title: 'Página não encontrada',
      description: 'A página solicitada não foi encontrada',
      layout: 'error'
    }
  }
]

export default routes
