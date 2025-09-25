import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/profiles',
    name: 'profiles',
    component: () => import('@/pages/profiles/index.vue'),
    meta: {
      requiresAuth: true,
      requiresAbility: 'profiles.index',
      title: 'Perfis',
      description: 'Gerencie os perfis de usuário'
    }
  }
]

export default routes
