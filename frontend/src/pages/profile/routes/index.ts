import type { RouteRecordRaw } from 'vue-router'

const profileRoutes: RouteRecordRaw[] = [
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('../index.vue'),
    meta: {
      requiresAuth: true,
      title: 'Meu Perfil'
    }
  }
]

export default profileRoutes

