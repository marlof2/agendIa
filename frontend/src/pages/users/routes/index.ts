import type { RouteRecordRaw } from 'vue-router'

const routesUsers: RouteRecordRaw[] = [
  {
    path: '/users',
    name: 'users',
    component: () => import('@/pages/users/index.vue'),
    meta: {
      requiresAuth: true,
      requiresAbility: 'users.index',
      title: 'Usuários',
      description: 'Gerencie todos os usuários do sistema'
    }
  }
]

export default routesUsers
