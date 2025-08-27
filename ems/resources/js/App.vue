<template>
    <!-- ถ้า route ใดตั้ง meta.blank = true ให้เรนเดอร์หน้าตรง ๆ -->
    <RouterView v-if="$route.meta?.blank" />
    <!-- นอกนั้นครอบด้วย Layout -->
    <Layout v-else :pageTitle="$route.meta?.title || 'Dashboard'">
      <RouterView />
    </Layout>
  </template>
  <script>
  import Layout from './components/layout.vue'
  export default { components: { Layout } }
  </script>
