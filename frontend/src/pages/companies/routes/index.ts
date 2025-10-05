import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/companies',
    name: 'companies',
    component: () => import('../index.vue'),
    meta: {
      requiresAuth: true,
      requiresAbility: 'companies.index',
      title: 'Empresas',
      description: 'Gerencie todas as empresas do sistema'
    }
  },
  {
    path: '/companies/:id/bind-professional',
    name: 'companies.bind-professional',
    component: () => import('../components/bind-professional/index.vue'),
    meta: {
      requiresAuth: false,
      requiresAbility: 'companies.manage_users',
      title: 'Gerenciar Profissionais',
      description: 'Gerencie os profissionais associados à empresa'
    }
  }
]

export default routes
