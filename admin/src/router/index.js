import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../pages/LoginPage.vue'),
    meta: { public: true },
  },
  {
    path: '/',
    name: 'dashboard',
    component: () => import('../pages/DashboardPage.vue'),
  },
  {
    path: '/enquiries',
    name: 'enquiries',
    component: () => import('../pages/EnquiriesPage.vue'),
  },
  {
    path: '/subscribers',
    name: 'subscribers',
    component: () => import('../pages/SubscribersPage.vue'),
  },
  {
    path: '/posts',
    name: 'posts',
    component: () => import('../pages/PostsPage.vue'),
  },
  {
    path: '/resources',
    name: 'resources',
    component: () => import('../pages/ResourcesPage.vue'),
  },
  {
    path: '/jobs',
    name: 'jobs',
    component: () => import('../pages/JobsPage.vue'),
  },
  {
    path: '/job-applications',
    name: 'job-applications',
    component: () => import('../pages/JobApplicationsPage.vue'),
  },
  {
    path: '/gallery',
    name: 'gallery',
    component: () => import('../pages/GalleryPage.vue'),
  },
  {
    path: '/partners',
    name: 'partners',
    component: () => import('../pages/PartnersPage.vue'),
  },
  {
    path: '/office-locations',
    name: 'office-locations',
    component: () => import('../pages/OfficeLocationsPage.vue'),
  },
  {
    path: '/solutions',
    name: 'solutions',
    component: () => import('../pages/solutions/SolutionsPage.vue'),
  },
  {
    path: '/page-content',
    name: 'page-content',
    component: () => import('../pages/PageContentPage.vue'),
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('../pages/SettingsPage.vue'),
    meta: { requiresAdmin: true },
  },
  {
    path: '/users',
    name: 'users',
    component: () => import('../pages/UsersPage.vue'),
    meta: { requiresAdmin: true },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('../pages/NotFoundPage.vue'),
  },
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.initialized) {
    await auth.fetchUser()
  }

  if (!to.meta.public && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.name === 'login' && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  if (to.meta.requiresAdmin && auth.user?.role !== 'admin') {
    return { name: 'dashboard' }
  }

  return true
})
