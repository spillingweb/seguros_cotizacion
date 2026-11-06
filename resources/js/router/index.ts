import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'quote',
      component: () => import('@/views/QuoteView.vue'),
      meta: { title: 'Cotizar Seguro' },
    },
    {
      path: '/seguros',
      name: 'insurances',
      component: () => import('@/views/InsurancesView.vue'),
      meta: { title: 'Contrataciones' },
    },
  ],
})

router.afterEach((to) => {
  const title = to.meta.title as string | undefined
  document.title = title ? `${title} — Seguro de Viaje` : 'Seguro de Viaje'
})

export default router
