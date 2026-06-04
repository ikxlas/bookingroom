import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const routes = [
  {
    path: '/',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { guestOnly: true }
  },
  {
    path: '/admin',
    name: 'AdminDashboard',
    component: () => import('@/views/AdminDashboard.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/user',
    name: 'UserDashboard',
    component: () => import('@/views/UserDashboard.vue'),
    meta: { requiresAuth: true, role: 'user' }
  },
  {
    path: '/user/history',
    name: 'UserHistory',
    component: () => import('@/views/UserHistory.vue'),
    meta: { requiresAuth: true, role: 'user' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation Guard
router.beforeEach(async (to, from, next) => {
  const { user, initAuth } = useAuth()
  
  // Ensure we load the user state from localStorage/API on first load
  await initAuth()

  if (to.meta.requiresAuth) {
    if (!user.value) {
      // Not logged in, redirect to login
      next({ name: 'Login' })
    } else if (to.meta.role && user.value.role !== to.meta.role) {
      // Logged in but wrong role, redirect to correct dashboard
      next({ name: user.value.role === 'admin' ? 'AdminDashboard' : 'UserDashboard' })
    } else {
      next()
    }
  } else if (to.meta.guestOnly && user.value) {
    // Already logged in, redirect to correct dashboard
    next({ name: user.value.role === 'admin' ? 'AdminDashboard' : 'UserDashboard' })
  } else {
    next()
  }
})

export default router
