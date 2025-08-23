<!-- pages/event_page.vue -->
<template>
  <h1>Event List</h1>
  <Button>
    <router-link :to="{ path: '/edit-event/1' }"> Edit event</router-link>
  </Button>
</template>

<script setup>
import Button from '../components/ButtonSuccess.vue'
</script>
