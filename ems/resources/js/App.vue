<template>
  <div class="font-[Poppins] text-neutral-800">
    <!-- ถ้า route ใดตั้ง meta.blank = true ให้เรนเดอร์หน้าตรง ๆ -->
    <RouterView v-if="$route.meta?.blank" class="font-[Poppins] text-neutral-800"/>
    <!-- นอกนั้นครอบด้วย Layout -->
    <Layout class="font-[Poppins] text-neutral-800" v-else :pageTitle="$route.meta?.title || 'Dashboard' ">
      <RouterView />
    </Layout>
  </div>
</template>

<script>
import { RouterView } from 'vue-router'
import Layout from './components/layout.vue'
export default { 
  components: { Layout, RouterView } 
}
</script>
