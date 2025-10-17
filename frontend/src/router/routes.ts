import type { RouteRecordRaw } from 'vue-router'

// Importações das páginas

import routesProfiles from '@/pages/profiles/routes/index'
import routesCompanies from '@/pages/companies/routes/index'
import routesUsers from '@/pages/users/routes/index'
import routesClients from '@/pages/clients/routes/index'
import routesProfile from '@/pages/profile/routes/index'


const routes: RouteRecordRaw[] = [
  ...routesProfiles,
  ...routesCompanies,
  ...routesUsers,
  ...routesClients,
  ...routesProfile,
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
    path: '/register',
    name: 'register',
    component: () => import('@/pages/register/Register.vue'),
    meta: {
      requiresAuth: false,
      title: 'Criar Conta',
      description: 'Crie sua conta no sistema AgendIA',
      layout: 'public'
    }
  },
  {
    path: '/select-tenant',
    name: 'select-tenant',
    component: () => import('@/pages/select-tenant/SelectTenant.vue'),
    meta: {
      requiresAuth: true,
      requiresTenant: false, // Não requer tenant selecionado
      title: 'Selecione a Empresa',
      description: 'Escolha qual empresa você deseja acessar',
      layout: 'public'
    }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/pages/dashboard.vue'),
    meta: {
      requiresAuth: true,
      requiresAbility: 'dashboard',
      title: 'Dashboard',
      description: 'Visão geral do sistema'
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
    path: '/my-companies',
    name: 'my-companies',
    component: () => import('@/pages/my-companies/index.vue'),
    meta: {
      requiresAuth: true,
      requiresAbility: 'users.my_companies',
      title: 'Minhas Empresas',
      description: 'Gerencie suas associações com empresas'
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
