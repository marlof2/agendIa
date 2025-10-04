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
  }
]

export default routes
