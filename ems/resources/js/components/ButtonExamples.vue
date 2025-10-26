<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold mb-8">🎯 Button Component Usage Examples</h1>
    
    <!-- วิธีใช้แบบเดิม (ทีมไม่ต้องแก้ไขโค้ด) -->
    <section>
      <h2 class="text-2xl font-semibold mb-4">✅ วิธีใช้แบบเดิม (ไม่ต้องแก้ไขโค้ด)</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- AddButton เดิม -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">AddButton (เดิม)</h3>
          <Button variant="add" size="md">Add</Button>
        </div>

        <!-- CreateButton เดิม -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">CreateButton (เดิม)</h3>
          <Button variant="create" size="md">Create</Button>
        </div>

        <!-- SaveButton เดิม -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">SaveButton (เดิม)</h3>
          <Button variant="save" size="md">Save</Button>
        </div>

        <!-- CancelButton เดิม -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">CancelButton (เดิม)</h3>
          <Button variant="cancel" size="md">Cancel</Button>
        </div>

        <!-- BackButton เดิม -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">BackButton (เดิม)</h3>
          <Button variant="back" size="md">Back</Button>
        </div>

        <!-- DownloadButton เดิม -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">DownloadButton (เดิม)</h3>
          <Button variant="download" size="md">*Click to download template excel file*</Button>
        </div>

        <!-- ImportButton เดิม -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">ImportButton (เดิม)</h3>
          <Button variant="import" size="md">Import</Button>
        </div>

        <!-- GenerateDataButton เดิม -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">GenerateDataButton (เดิม)</h3>
          <Button variant="generate" size="md">Generate Data</Button>
        </div>

        <!-- Filter เดิม -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">Filter (เดิม)</h3>
          <Button variant="filter" size="md">Filter</Button>
        </div>
      </div>
    </section>

    <!-- วิธีใช้แบบใหม่ (ยืดหยุ่นมากขึ้น) -->
    <section>
      <h2 class="text-2xl font-semibold mb-4">🚀 วิธีใช้แบบใหม่ (ยืดหยุ่นขึ้น)</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Label with auto icon -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">Auto Icon</h3>
          <Button label="Delete" variant="danger" />
        </div>

        <!-- Custom icon -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">Custom Icon</h3>
          <Button icon="star" variant="warning" label="Favorite" />
        </div>

        <!-- Outline style -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">Outline Style</h3>
          <Button variant="success" outline label="Success" />
        </div>

        <!-- Different sizes -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">Sizes</h3>
          <div class="space-y-2">
            <Button variant="primary" size="sm" label="Small" />
            <Button variant="primary" size="md" label="Medium" />
            <Button variant="primary" size="lg" label="Large" />
          </div>
        </div>

        <!-- Icon Only -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">Icon Only</h3>
          <div class="space-x-2">
            <Button variant="edit" icon-only size="sm" />
            <Button variant="delete" icon-only size="md" />
            <Button variant="save" icon-only size="lg" />
          </div>
        </div>

        <!-- Loading state -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">Loading</h3>
          <Button variant="primary" loading label="Loading..." />
        </div>

        <!-- Disabled state -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">Disabled</h3>
          <Button variant="secondary" disabled label="Disabled" />
        </div>

        <!-- Block width -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-medium mb-2">Block Width</h3>
          <Button variant="info" block label="Full Width" />
        </div>
      </div>
    </section>

    <!-- Event Handling -->
    <section>
      <h2 class="text-2xl font-semibold mb-4">⚡ Event Handling</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Button 
          variant="success" 
          label="Click Me!" 
          @click="handleClick('Success clicked!')"
        />
        
        <Button 
          variant="warning" 
          label="Show Alert"
          @click="showAlert"
        />
        
        <Button 
          variant="info" 
          label="Console Log"
          @click="logToConsole"
        />
      </div>
      
      <div v-if="message" class="mt-4 p-4 bg-blue-100 rounded-lg">
        <p class="text-blue-800">{{ message }}</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Button from './Button.vue'

const message = ref('')

const handleClick = (msg) => {
  message.value = msg
  setTimeout(() => {
    message.value = ''
  }, 3000)
}

const showAlert = () => {
  alert('Button clicked!')
}

const logToConsole = () => {
  console.log('Button clicked at:', new Date().toLocaleTimeString())
  handleClick('Check console for log message')
}

const handleFilter = () => {
  handleClick('Filter button clicked!')
}
</script>