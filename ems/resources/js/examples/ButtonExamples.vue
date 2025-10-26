<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Button Component Examples</h1>

    <!-- Basic Buttons -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Basic Variants</h2>
      <div class="flex flex-wrap gap-3">
        <Button variant="primary" text="Primary" />
        <Button variant="success" text="Success" />
        <Button variant="danger" text="Danger" />
        <Button variant="warning" text="Warning" />
        <Button variant="info" text="Info" />
        <Button variant="secondary" text="Secondary" />
        <Button variant="light" text="Light" />
        <Button variant="dark" text="Dark" />
      </div>
    </section>

    <!-- Outline Buttons -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Outline Buttons</h2>
      <div class="flex flex-wrap gap-3">
        <Button variant="primary" text="Primary Outline" outline />
        <Button variant="success" text="Success Outline" outline />
        <Button variant="danger" text="Danger Outline" outline />
        <Button variant="warning" text="Warning Outline" outline />
      </div>
    </section>

    <!-- Sizes -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Sizes</h2>
      <div class="flex flex-wrap items-end gap-3">
        <Button size="xs" text="Extra Small" />
        <Button size="sm" text="Small" />
        <Button size="md" text="Medium" />
        <Button size="lg" text="Large" />
        <Button size="xl" text="Extra Large" />
      </div>
    </section>

    <!-- Shapes -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Shapes</h2>
      <div class="flex flex-wrap gap-3">
        <Button shape="rounded" text="Rounded" />
        <Button shape="square" text="Square" />
        <Button shape="pill" text="Pill" />
        <Button shape="circle" icon="home" icon-only />
      </div>
    </section>

    <!-- With Icons -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">With Icons</h2>
      <div class="flex flex-wrap gap-3">
        <Button text="Left Icon" left-icon="add" />
        <Button text="Right Icon" right-icon="arrow_forward" icon-position="right" />
        <Button text="Both Icons" left-icon="edit" right-icon="save" />
        <Button icon="settings" icon-only size="md" />
        <Button icon="search" icon-only size="lg" shape="circle" />
      </div>
    </section>

    <!-- Preset Buttons -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Preset Buttons</h2>
      <div class="flex flex-wrap gap-3">
        <Button preset="add" />
        <Button preset="save" />
        <Button preset="edit" />
        <Button preset="delete" />
        <Button preset="download" />
        <Button preset="import" />
        <Button preset="export" />
        <Button preset="filter" />
        <Button preset="search" />
        <Button preset="refresh" />
      </div>
    </section>

    <!-- Preset Buttons Outline -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Preset Buttons (Outline)</h2>
      <div class="flex flex-wrap gap-3">
        <Button preset="add" outline />
        <Button preset="save" outline />
        <Button preset="edit" outline />
        <Button preset="delete" outline />
        <Button preset="cancel" outline />
        <Button preset="back" outline />
      </div>
    </section>

    <!-- States -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">States</h2>
      <div class="flex flex-wrap gap-3">
        <Button text="Normal" />
        <Button text="Loading" loading />
        <Button text="Disabled" disabled />
        <Button text="Active" active />
      </div>
    </section>

    <!-- With Badge -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">With Badge</h2>
      <div class="flex flex-wrap gap-3">
        <Button text="Messages" badge="5" />
        <Button text="Notifications" badge="12" badge-variant="warning" />
        <Button preset="filter" badge="3" />
        <Button text="Cart" left-icon="shopping_cart" badge="99+" />
      </div>
    </section>

    <!-- Block Buttons -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Block Buttons</h2>
      <div class="space-y-3">
        <Button text="Full Width Button" block />
        <Button preset="save" block />
        <Button text="Submit Form" preset="submit" block size="lg" />
      </div>
    </section>

    <!-- Special Variants -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Special Variants</h2>
      <div class="flex flex-wrap gap-3">
        <Button variant="ghost" text="Ghost Button" />
        <Button variant="link" text="Link Button" />
        <Button variant="gradient" text="Gradient Button" />
      </div>
    </section>

    <!-- Custom Colors -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Custom Colors</h2>
      <div class="flex flex-wrap gap-3">
        <Button text="Custom Purple" custom-color="#8B5CF6" />
        <Button text="Custom Pink" custom-color="#EC4899" outline />
        <Button text="Custom Teal" custom-color="#14B8A6" left-icon="star" />
      </div>
    </section>

    <!-- Action Examples -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Interactive Examples</h2>
      <div class="flex flex-wrap gap-3">
        <Button 
          text="Click Counter" 
          :badge="clickCount" 
          @click="clickCount++" 
        />
        <Button 
          text="Toggle Loading" 
          :loading="isLoading" 
          @click="toggleLoading" 
        />
        <Button 
          text="External Link" 
          href="https://google.com" 
          target="_blank" 
          right-icon="open_in_new" 
        />
      </div>
    </section>

    <!-- Real Use Cases -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Real Use Cases</h2>
      
      <!-- Form Actions -->
      <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="font-medium mb-3">Form Actions</h3>
        <div class="flex gap-2">
          <Button preset="save" @click="handleSave" />
          <Button preset="cancel" outline @click="handleCancel" />
          <Button preset="reset" variant="warning" outline @click="handleReset" />
        </div>
      </div>

      <!-- Data Table Actions -->
      <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="font-medium mb-3">Data Table Actions</h3>
        <div class="flex gap-2">
          <Button preset="add" size="sm" />
          <Button preset="edit" size="sm" outline />
          <Button preset="delete" size="sm" variant="danger" outline />
          <Button preset="export" size="sm" />
          <Button preset="filter" size="sm" :badge="activeFilters" />
        </div>
      </div>

      <!-- Navigation -->
      <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="font-medium mb-3">Navigation</h3>
        <div class="flex gap-2">
          <Button preset="back" />
          <Button text="Dashboard" left-icon="dashboard" />
          <Button text="Settings" left-icon="settings" variant="ghost" />
          <Button preset="menu" icon-only shape="circle" />
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Button from '../components/Button.vue'

// Reactive data for examples
const clickCount = ref(0)
const isLoading = ref(false)
const activeFilters = ref(2)

// Example methods
const toggleLoading = () => {
  isLoading.value = true
  setTimeout(() => {
    isLoading.value = false
  }, 2000)
}

const handleSave = () => {
  alert('บันทึกข้อมูลแล้ว!')
}

const handleCancel = () => {
  alert('ยกเลิกแล้ว!')
}

const handleReset = () => {
  alert('รีเซ็ตแล้ว!')
}
</script>

<style scoped>
/* Additional custom styles if needed */
</style>