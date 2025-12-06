<template>
  <div class="min-h-screen flex items-center justify-center md:justify-end pr-[8vw] md:pr-[12vw] px-6 bg-red-700 bg-[url('/images/email/ChatGPT_Image_20_.._2568_23_31_01.png')] bg-cover bg-center">
    
    <div class="rounded-[28px] bg-white shadow-lg w-[484px] h-[592px] p-8 flex flex-col items-center relative">
      
      <div class="mt-10">
        <h2 class="text-4xl font-semibold text-neutral-800 text-center">
          Forgot your password?
        </h2>
      </div>

      <div class="flex-grow flex items-center justify-center w-full">
        <p class="text-4xl font-normal text-neutral-600 text-center leading-relaxed">
          Please tell your admin <br>
          to reset your password
        </p>
      </div>

      <div class="mb-10">
        <router-link to="/login" class="hover:opacity-80 transition-opacity">
          <Icon icon="material-symbols:arrow-back-rounded" width="58" height="58" color="#C10008"/>
        </router-link>
      </div>

    </div>
  </div>
</template>

<script>
import { Icon } from '@iconify/vue';

export default {
  components: {
    Icon,
  },
};
</script>