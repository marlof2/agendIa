import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/clients',
    name: 'clients',
    component: () => import('@/pages/clients/index.vue'),
    meta: {
      requiresAuth: true,
      requiresAbility: 'clients.index',
      title: 'Clientes',
      description: 'Gerencie os clientes cadastrados'
    }
  }
]

export default routes

