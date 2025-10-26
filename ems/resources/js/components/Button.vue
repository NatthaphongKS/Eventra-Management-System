<template>
  <!-- Material Icons Import -->
  <link
    rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
  />

  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
    v-bind="$attrs"
  >
    <!-- Loading spinner
    <span v-if="loading" class="animate-spin mr-2">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span> -->

    <!-- Icon -->
    <span v-if="iconName && !loading" class="material-symbols-outlined" :class="iconSize">
      {{ iconName }}
    </span>

    <!-- Label/Text -->
    <span v-if="!iconOnly && (label || $slots.default)" :class="textClasses">
      <slot>{{ label }}</slot>
    </span>

    <!-- Custom slot for special cases like download text -->
    <span v-if="$slots.icon" class="inline-flex">
      <slot name="icon" />
    </span>
  </button>
</template>

<script setup>
import { computed } from 'vue'

// Props - รวมทุก prop ที่ใช้ในปุ่มเดิม
const props = defineProps({
  // Basic props
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  },
  label: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: ''
  },
  
  // Styling props
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary', 'secondary', 'success', 'danger', 'warning', 'info', 
      'light', 'dark', 'add', 'create', 'save', 'cancel', 'back', 
      'download', 'import', 'generate', 'delete', 'edit', 'filter',
      'confirm', 'ok', 'export', 'show-data'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  
  // State props
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  outline: {
    type: Boolean,
    default: false
  },
  block: {
    type: Boolean,
    default: false
  },
  iconOnly: {
    type: Boolean,
    default: false
  },
  
  // Layout props
  shape: {
    type: String,
    default: 'rounded',
    validator: (value) => ['rounded', 'pill', 'square', 'circle'].includes(value)
  },
  
  // Backward compatibility - รองรับการใช้งานแบบเดิม
  preset: {
    type: String,
    default: '',
    validator: (value) => [
      '', 'add', 'create', 'save', 'cancel', 'back', 
      'download', 'import', 'generate', 'delete', 'edit', 'filter',
      'confirm', 'ok', 'export', 'show-data'
    ].includes(value)
  }
})

// Emits
const emit = defineEmits(['click'])

// Computed: Icon Name - รวม logic จากทุกปุ่ม
const iconName = computed(() => {
  if (props.icon) return props.icon
  
  // จาก preset หรือ variant
  const buttonType = props.preset || props.variant || props.label?.toLowerCase()
  
  const iconMap = {
    // Action buttons
    add: 'add',
    create: 'add',
    save: 'save',
    cancel: 'close',
    back: 'arrow_back',
    delete: 'delete',
    edit: 'edit',
    confirm: '',
    ok: '',
    
    // Data buttons  
    download: 'download',
    import: 'download',
    export: 'file_upload',
    generate: 'upload',
    'show-data': '',
    
    // Common actions
    search: 'search',
    filter: 'filter_list',
    settings: 'settings',
    refresh: 'refresh',
    menu: 'menu',
    submit: 'send',
    reset: 'refresh',
    close: 'close',
    info: 'info',
    help: 'help'
  }
  
  return iconMap[buttonType] || ''
})

// Computed: Icon Size
const iconSize = computed(() => {
  const sizes = {
    xs: 'text-sm',
    sm: 'text-base', 
    md: 'text-lg',
    lg: 'text-xl',
    xl: 'text-2xl'
  }
  return sizes[props.size] || sizes.md
})

// Computed: Text Classes - สำหรับปุ่มพิเศษ
const textClasses = computed(() => {
  if (props.variant === 'download') {
    return 'inline-block text-sky-600'
  }
  return 'inline-block'
})

// Computed: Button Classes - รวม style จากทุกปุ่ม
const buttonClasses = computed(() => {
  const baseClasses = [
    'inline-flex',
    'items-center',
    'justify-center',
    'font-bold',
    'leading-none', 
    'shadow-sm',
    'transition',
    'focus-visible:outline-none',
    'focus-visible:ring-2',
    'focus-visible:ring-blue-500/35',
    'disabled:opacity-50',
    'disabled:cursor-not-allowed',
    'border'
  ]

  // Shape classes
  const shapeClasses = {
    rounded: 'rounded-lg',
    pill: 'rounded-[17px]',
    square: 'rounded-none', 
    circle: 'rounded-full'
  }

  // Size classes (ขนาดของปุ่ม)
  const sizeClasses = {
    xs: props.iconOnly ? 'w-8 h-8' : 'min-w-[120px] h-[35px] px-4 py-2 text-xs gap-2',
    sm: props.iconOnly ? 'w-9 h-9' : 'min-w-[130px] h-[40px] px-4 py-2 text-sm gap-2',
    md: props.iconOnly ? 'w-10 h-10' : 'min-w-[140px] h-[45px] px-4 py-2 text-[15px] gap-2.5',
    lg: props.iconOnly ? 'w-11 h-11' : 'min-w-[150px] h-[50px] px-5 py-3 text-base gap-2.5',
    xl: props.iconOnly ? 'w-12 h-12' : 'min-w-[160px] h-[55px] px-6 py-3 text-lg gap-3'
  }

  // Variant classes - รวมสีและ style จากทุกปุ่ม
  const getVariantClasses = () => {
    const type = props.preset || props.variant
    
    switch (type) {
      case 'add':
      case 'delete':
        return props.outline 
          ? 'border-red-600 text-red-600 bg-transparent hover:bg-red-50'
          : 'bg-red-700 text-white border-red-700 hover:bg-red-800'
          
      case 'create':
      case 'save':
      case 'success':
      case 'confirm':
      case 'ok':
        return props.outline
          ? 'border-green-600 text-green-600 bg-transparent hover:bg-green-50'  
          : 'bg-green-600 text-white border-green-600 hover:bg-green-700'
          
      case 'cancel':
      case 'danger':
      case 'show-data':
        return props.outline
          ? 'border-red-600 text-red-600 bg-transparent hover:bg-red-50'
          : 'bg-red-700 text-white border-red-700 hover:bg-red-800'
          
      case 'back':
      case 'secondary':
        return props.outline
          ? 'border-gray-600 text-gray-600 bg-transparent hover:bg-gray-50'
          : 'bg-gray-500 text-white border-gray-500 hover:bg-gray-600'
          
      case 'download':
      case 'import':
      case 'generate':
      case 'filter':
      case 'light':
      case 'export':
        return props.outline
          ? 'border-gray-300 text-gray-700 bg-transparent hover:bg-gray-50'
          : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-md'
          
      case 'warning':
        return props.outline
          ? 'border-yellow-600 text-yellow-600 bg-transparent hover:bg-yellow-50'
          : 'bg-yellow-600 text-white border-yellow-600 hover:bg-yellow-700'
          
      case 'info':
        return props.outline  
          ? 'border-cyan-600 text-cyan-600 bg-transparent hover:bg-cyan-50'
          : 'bg-cyan-600 text-white border-cyan-600 hover:bg-cyan-700'
          
      case 'dark':
        return props.outline
          ? 'border-gray-900 text-gray-900 bg-transparent hover:bg-gray-50'
          : 'bg-gray-900 text-white border-gray-900 hover:bg-gray-800'
          
      default: // primary
        return props.outline
          ? 'border-blue-600 text-blue-600 bg-transparent hover:bg-blue-50'
          : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700'
    }
  }

  // Additional classes
  const blockClass = props.block ? 'w-full justify-center' : ''
  const hoverClass = 'hover:border-black/20 hover:shadow-md active:translate-y-px active:shadow-inner'

  return [
    ...baseClasses,
    shapeClasses[props.shape] || shapeClasses.rounded,
    sizeClasses[props.size] || sizeClasses.md,
    getVariantClasses(),
    blockClass,
    hoverClass
  ].filter(Boolean).join(' ')
})

// Methods
const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>

/* Material symbols styling */
.material-symbols-outlined {
  font-variation-settings:
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
  user-select: none;
}

/* Hover effects
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: translateY(0) scale(0.98);
} */

/* Font family - รองรับ font จากปุ่มเดิม */
button {
  font-family: "Poppins", "Noto Sans Thai", system-ui, -apple-system, "Segoe UI",
               Roboto, "Helvetica Neue", Arial, "Apple Color Emoji", "Segoe UI Emoji";
}
</style>