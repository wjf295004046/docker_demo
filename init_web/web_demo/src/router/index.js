import Vue from 'vue'
import Router from 'vue-router'
import HelloWorld from '@/components/HelloWorld'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'HelloWorld',
      component: HelloWorld
    },
    {
      path: '/chat',
      name: 'Chat',
      component: () => import('@/components/chat/Chat')
    },
    {
      path: '/chat1',
      name: 'Chat1',
      component: () => import('@/components/chat/Chat1')
    }
  ]
})
