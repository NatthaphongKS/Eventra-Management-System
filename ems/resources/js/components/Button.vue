<template>
  <header class="max-w-6xl mx-auto px-6 pt-6" v-if="showHeader">
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
  </header>

  <button
    :type="type"
    @click="handleClick"
    :disabled="disabled || loading"
    class="inline-flex items-center gap-2.5 rounded-[17px] border border-neutral-200
           font-bold leading-none shadow-sm transition-all duration-200
           hover:border-black/20 hover:shadow-md hover:scale-[1.02]
           active:translate-y-px active:shadow-inner active:scale-[0.98]
           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/35
           disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
    :class="[
      sizeClasses,
      variantClasses,
      shapeClasses,
      block ? 'w-full justify-center' : '',
      outline ? 'bg-transparent border-2' : '',
      customClass
    ]"
    :style="customStyles"
  >
    <!-- Loading Icon -->
    <span v-if="loading" class="inline-flex animate-spin">
      <span class="material-symbols-outlined leading-none" :class="iconSizeClass">refresh</span>
    </span>

    <!-- Left Icon -->
    <span v-else-if="(iconName || leftIcon) && !hideIcon && iconPosition === 'left'" 
          class="inline-flex" aria-hidden="true">
      <span class="material-symbols-outlined leading-none" :class="iconSizeClass">
        {{ leftIcon || iconName }}
      </span>
    </span>

    <!-- Text Content -->
    <span class="inline-block" v-if="!hideText && !iconOnly">
      <slot>{{ computedText }}</slot>
    </span>

    <!-- Right Icon -->
    <span v-if="rightIcon && !hideIcon && iconPosition === 'right'" 
          class="inline-flex" aria-hidden="true">
      <span class="material-symbols-outlined leading-none" :class="iconSizeClass">
        {{ rightIcon }}
      </span>
    </span>

    <!-- Icon Only Mode -->
    <span v-if="iconOnly && !loading" class="inline-flex" aria-hidden="true">
      <span class="material-symbols-outlined leading-none" :class="iconSizeClass">
        {{ iconName || leftIcon || rightIcon }}
      </span>
    </span>

    <!-- Badge/Counter -->
    <span v-if="badge && !hideText" 
          class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 
                 text-xs font-bold rounded-full"
          :class="badgeClasses">
      {{ badge }}
    </span>
  </button>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  // Button Type
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  },
  
  // Appearance
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary', 'success', 'danger', 'warning', 'info', 'secondary', 
      'light', 'dark', 'ghost', 'link', 'gradient'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  shape: {
    type: String,
    default: 'rounded',
    validator: (value) => ['rounded', 'square', 'pill', 'circle'].includes(value)
  },
  block: {
    type: Boolean,
    default: false
  },
  outline: {
    type: Boolean,
    default: false
  },
  
  // Content
  text: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: ''
  },
  leftIcon: {
    type: String,
    default: ''
  },
  rightIcon: {
    type: String,
    default: ''
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  },
  iconOnly: {
    type: Boolean,
    default: false
  },
  hideText: {
    type: Boolean,
    default: false
  },
  hideIcon: {
    type: Boolean,
    default: false
  },
  
  // Badge/Counter
  badge: {
    type: [String, Number],
    default: ''
  },
  badgeVariant: {
    type: String,
    default: 'danger',
    validator: (value) => ['primary', 'success', 'danger', 'warning', 'info'].includes(value)
  },
  
  // State
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  active: {
    type: Boolean,
    default: false
  },
  
  // Preset Buttons
  preset: {
    type: String,
    default: '',
    validator: (value) => [
      '', 'add', 'save', 'cancel', 'back', 'create', 'edit', 'delete', 
      'download', 'import', 'export', 'filter', 'search', 'sort', 'refresh',
      'submit', 'reset', 'close', 'menu', 'settings', 'info', 'help'
    ].includes(value)
  },
  
  // Styling
  customClass: {
    type: String,
    default: ''
  },
  customColor: {
    type: String,
    default: ''
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  
  // Click Actions
  href: {
    type: String,
    default: ''
  },
  target: {
    type: String,
    default: '_self'
  },
  
  // Tooltip
  tooltip: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['click', 'hover', 'focus', 'blur'])

// Computed Properties
const sizeClasses = computed(() => {
  const sizes = {
    xs: 'px-2 py-1 text-[11px]',
    sm: 'px-3 py-1.5 text-[13px]',
    md: 'px-4 py-2 text-[14px]',
    lg: 'px-6 py-2.5 text-[15px]',
    xl: 'px-8 py-3 text-[16px]'
  }
  
  // Special sizing for icon-only buttons
  if (props.iconOnly) {
    return {
      xs: 'p-1',
      sm: 'p-1.5', 
      md: 'p-2',
      lg: 'p-2.5',
      xl: 'p-3'
    }[props.size]
  }
  
  return sizes[props.size]
})

const iconSizeClass = computed(() => {
  const iconSizes = {
    xs: 'text-[14px]',
    sm: 'text-[16px]',
    md: 'text-[18px]',
    lg: 'text-[20px]',
    xl: 'text-[22px]'
  }
  return iconSizes[props.size]
})

const shapeClasses = computed(() => {
  const shapes = {
    rounded: 'rounded-[17px]',
    square: 'rounded-none',
    pill: 'rounded-full',
    circle: 'rounded-full aspect-square'
  }
  return shapes[props.shape]
})

const variantClasses = computed(() => {
  const baseVariants = {
    primary: 'bg-blue-600 text-white hover:bg-blue-700',
    success: 'bg-green-600 text-white hover:bg-green-700', 
    danger: 'bg-red-600 text-white hover:bg-red-700',
    warning: 'bg-yellow-500 text-white hover:bg-yellow-600',
    info: 'bg-cyan-600 text-white hover:bg-cyan-700',
    secondary: 'bg-gray-600 text-white hover:bg-gray-700',
    light: 'bg-gray-100 text-gray-800 hover:bg-gray-200 border-gray-300',
    dark: 'bg-gray-900 text-white hover:bg-gray-800',
    ghost: 'bg-transparent text-gray-700 hover:bg-gray-100 border-transparent',
    link: 'bg-transparent text-blue-600 hover:text-blue-700 border-transparent shadow-none hover:shadow-none hover:underline',
    gradient: 'bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:from-blue-600 hover:to-purple-700'
  }
  
  const outlineVariants = {
    primary: 'border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white',
    success: 'border-green-600 text-green-600 hover:bg-green-600 hover:text-white',
    danger: 'border-red-600 text-red-600 hover:bg-red-600 hover:text-white',
    warning: 'border-yellow-500 text-yellow-600 hover:bg-yellow-500 hover:text-white',
    info: 'border-cyan-600 text-cyan-600 hover:bg-cyan-600 hover:text-white',
    secondary: 'border-gray-600 text-gray-600 hover:bg-gray-600 hover:text-white',
    light: 'border-gray-300 text-gray-700 hover:bg-gray-100',
    dark: 'border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white'
  }
  
  // Use preset variant if preset is specified
  if (props.preset) {
    const presetVariants = {
      add: props.outline ? outlineVariants.danger : 'bg-red-700 text-white hover:bg-red-800',
      save: props.outline ? outlineVariants.success : 'bg-green-600 text-white hover:bg-green-700',
      cancel: props.outline ? outlineVariants.secondary : 'bg-gray-600 text-white hover:bg-gray-700',
      back: props.outline ? outlineVariants.secondary : 'bg-gray-500 text-white hover:bg-gray-600',
      create: props.outline ? outlineVariants.primary : 'bg-blue-600 text-white hover:bg-blue-700',
      edit: props.outline ? outlineVariants.warning : 'bg-yellow-500 text-white hover:bg-yellow-600',
      delete: props.outline ? outlineVariants.danger : 'bg-red-600 text-white hover:bg-red-700',
      download: props.outline ? outlineVariants.info : 'bg-cyan-600 text-white hover:bg-cyan-700',
      import: props.outline ? outlineVariants.primary : 'bg-purple-600 text-white hover:bg-purple-700',
      export: props.outline ? outlineVariants.success : 'bg-emerald-600 text-white hover:bg-emerald-700',
      filter: props.outline ? outlineVariants.info : 'bg-indigo-600 text-white hover:bg-indigo-700',
      search: props.outline ? outlineVariants.primary : 'bg-blue-500 text-white hover:bg-blue-600',
      sort: props.outline ? outlineVariants.secondary : 'bg-slate-600 text-white hover:bg-slate-700',
      refresh: props.outline ? outlineVariants.info : 'bg-teal-600 text-white hover:bg-teal-700',
      submit: props.outline ? outlineVariants.success : 'bg-green-600 text-white hover:bg-green-700',
      reset: props.outline ? outlineVariants.warning : 'bg-orange-500 text-white hover:bg-orange-600',
      close: props.outline ? outlineVariants.secondary : 'bg-gray-500 text-white hover:bg-gray-600',
      menu: props.outline ? outlineVariants.dark : 'bg-gray-800 text-white hover:bg-gray-900',
      settings: props.outline ? outlineVariants.secondary : 'bg-slate-600 text-white hover:bg-slate-700',
      info: props.outline ? outlineVariants.info : 'bg-blue-500 text-white hover:bg-blue-600',
      help: props.outline ? outlineVariants.info : 'bg-indigo-500 text-white hover:bg-indigo-600'
    }
    return presetVariants[props.preset] || (props.outline ? outlineVariants[props.variant] : baseVariants[props.variant])
  }
  
  return props.outline ? outlineVariants[props.variant] : baseVariants[props.variant]
})

const badgeClasses = computed(() => {
  const badgeVariants = {
    primary: 'bg-blue-500 text-white',
    success: 'bg-green-500 text-white',
    danger: 'bg-red-500 text-white',
    warning: 'bg-yellow-500 text-white',
    info: 'bg-cyan-500 text-white'
  }
  return badgeVariants[props.badgeVariant]
})

const iconName = computed(() => {
  if (props.icon) return props.icon
  if (props.leftIcon) return props.leftIcon
  if (props.rightIcon) return props.rightIcon
  
  if (props.preset) {
    const presetIcons = {
      add: 'add',
      save: 'save',
      cancel: 'cancel',
      back: 'arrow_back',
      create: 'add_circle',
      edit: 'edit',
      delete: 'delete',
      download: 'download',
      import: 'upload',
      export: 'file_download',
      filter: 'filter_list',
      search: 'search',
      sort: 'sort',
      refresh: 'refresh',
      submit: 'check',
      reset: 'restart_alt',
      close: 'close',
      menu: 'menu',
      settings: 'settings',
      info: 'info',
      help: 'help'
    }
    return presetIcons[props.preset] || ''
  }
  
  return ''
})

const computedText = computed(() => {
  if (props.text) return props.text
  
  if (props.preset) {
    const presetTexts = {
      add: 'เพิ่ม',
      save: 'บันทึก',
      cancel: 'ยกเลิก', 
      back: 'กลับ',
      create: 'สร้าง',
      edit: 'แก้ไข',
      delete: 'ลบ',
      download: 'ดาวน์โหลด',
      import: 'นำเข้า',
      export: 'ส่งออก',
      filter: 'กรอง',
      search: 'ค้นหา',
      sort: 'เรียงลำดับ',
      refresh: 'รีเฟรช',
      submit: 'ส่ง',
      reset: 'รีเซ็ต',
      close: 'ปิด',
      menu: 'เมนู',
      settings: 'ตั้งค่า',
      info: 'ข้อมูล',
      help: 'ช่วยเหลือ'
    }
    return presetTexts[props.preset] || ''
  }
  
  return ''
})

const customStyles = computed(() => {
  let styles = {}
  
  // Custom color override
  if (props.customColor && !props.outline) {
    styles.backgroundColor = props.customColor
    styles.borderColor = props.customColor
  } else if (props.customColor && props.outline) {
    styles.borderColor = props.customColor
    styles.color = props.customColor
  }
  
  // Active state
  if (props.active) {
    styles.transform = 'scale(0.95)'
    styles.boxShadow = 'inset 0 2px 4px rgba(0,0,0,0.1)'
  }
  
  return styles
})

// Methods
const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    // Handle href navigation
    if (props.href) {
      if (props.target === '_blank') {
        window.open(props.href, '_blank')
      } else {
        window.location.href = props.href
      }
    }
    
    emit('click', event)
  }
}

const handleHover = (event) => {
  emit('hover', event)
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleBlur = (event) => {
  emit('blur', event)
}
</script>

<style scoped>
/* Animation for loading */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Smooth transitions */
button {
  transition: all 0.2s ease-in-out;
}

/* Focus ring enhancement */
button:focus-visible {
  outline: 2px solid rgba(59, 130, 246, 0.35);
  outline-offset: 2px;
}

/* Hover effects enhancement */
button:hover:not(:disabled) {
  transform: translateY(-1px);
}

button:active:not(:disabled) {
  transform: translateY(0px) scale(0.98);
}

/* Gradient hover effect */
button.bg-gradient-to-r:hover {
  background-size: 200% 200%;
  animation: gradient-shift 0.3s ease-in-out;
}

@keyframes gradient-shift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

/* Badge positioning */
.badge {
  position: relative;
  top: -2px;
}

/* Icon size adjustments for different button sizes */
.material-symbols-outlined {
  font-variation-settings: 
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  button {
    transition: none;
  }
  
  .animate-spin {
    animation: none;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  button {
    border-width: 2px;
  }
}
</style>