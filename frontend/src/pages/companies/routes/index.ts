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
    path: '/companies/:id/associate-users',
    name: 'companies.associate-users',
    component: () => import('../components/associate-users/index.vue'),
    meta: {
      requiresAuth: true,
      requiresAbility: 'companies.users.index',
      title: 'Gerenciar Usuários',
      description: 'Gerencie todos os usuários associados à empresa'
    }
  }
]

export default routes
