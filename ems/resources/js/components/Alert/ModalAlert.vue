<!-- /**
 * ชื่อไฟล์: ModalAlert.vue
 * คำอธิบาย: หน้า Alert แสดงหน้าต่างยืนยัน
 * Input: defineProps จากหน้าแม่
 * Output: หน้าAlert ตามประเภทที่หน้าแม่บอกมาใน defineprops
 * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
 * วันที่จัดทำ/แก้ไข: 2026-02-17
 */ -->

<!-- components/Alert/ModalAlert.vue -->
<template>
  <teleport to="body">
    <transition name="fade">
      <div
        v-if="open"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/40 backdrop-blur-[2px]"
        role="dialog"
        aria-modal="true"
        @keydown.esc.prevent="handleCancel"
        @keyup.enter.prevent="handleConfirm"
      >
        <!-- Modal -->
        <transition name="pop">
          <div
            class="relative max-w-[90vw] w-[350px] h-[350px] text-center shadow-2xl"
            :class="wrapperClass"
          >
            <!-- Icon circle -->
            <div
              class="mx-auto grid place-items-center rounded-full "
              :class="iconWrapperClass"
            >
              <Icon :icon="iconName" :class="iconClass" />
            </div>

            <!-- Title -->
            <h2 :class="titleClass">
              {{ title }}
            </h2>

            <!-- Message -->
            <p :class="messageClass">
              {{ message }}
            </p>

            <!-- Actions -->
            <div class="mb-4 flex items-center justify-center" :class="actionGapClass">
              <button
                v-if="showCancelFinal"
                type="button"
                @click="handleCancel"
                class="h-[58px] w-[168px] rounded-[20px] border border-neutral-200 bg-red-700 text-white transition
                       hover:brightness-95 active:brightness-90 focus:outline-none focus:ring-4 focus:ring-red-200"
              >
                {{ cancelText }}
              </button>

              <button
                ref="okBtn"
                type="button"
                @click="handleConfirm"
                :class="okBtnClass"
                autofocus
              >
                {{ okText }}
              </button>
            </div>

            <!-- ปิดโดยการกดนอกกรอบ (optional) -->
            <button
              class="absolute inset-0 -z-10 cursor-default"
              aria-hidden="true"
              @click.self="handleCancel"
            ></button>
          </div>
        </transition>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Icon } from '@iconify/vue'

const open = defineModel('open', { type: Boolean, default: false })

const props = defineProps({
  type: { type: String, default: 'confirm' }, // success | error | warning | confirm
  title: { type: String, default: 'ARE YOU SURE TO EDIT?' },
  message: { type: String, default: 'Are you sure to change this ?' },
  showCancel: { type: Boolean, default: true },
  okText: { type: String, default: 'OK' },
  cancelText: { type: String, default: 'Cancel' },
})

const emit = defineEmits(['confirm', 'cancel'])

// ---------- Icon ----------
const iconName = computed(() => {
  switch (props.type) {
    case 'success': return 'lets-icons:check-fill' // เช็คทึบ
    case 'error':   return 'mdi:close-circle'
    case 'warning': return 'mdi:alert'
    case 'confirm': return 'mdi:help-circle'
    default:        return 'mdi:help-circle'
  }
})

const iconWrapperClass = computed(() => {
  // success: วงกลมเขียวทึบ 120x120
  if (props.type === 'success') return 'h-[120px] w-[120px]'
  // confirm: ใหญ่ 144
  if (props.type === 'confirm') return 'h-[120px] w-[120px]  '
  // อื่นๆ โทนพาสเทล
  if (props.type === 'error')   return 'h-[120px] w-[120px] bg-red-100 '
  if (props.type === 'warning') return 'h-[120px] w-[120px] bg-yellow-100 '
  return 'h-[120px] w-[120px] bg-neutral-100 '
})

const iconClass = computed(() => {
  if (props.type === 'success') return 'h-[90px] w-[90px] text-green-500' // เช็คสีขาว
  if (props.type === 'confirm') return 'h-[90px] w-[90px] text-[#FDC800]' // ? สีขาว
  return 'h-[90px] w-[90px] text-current'
})

// ---------- Typography ----------
const titleClass = computed(() => {
  if (props.type === 'success') {
    return 'text-xl font-extrabold tracking-wide text-neutral-800 uppercase'
  }
  return 'text-xl font-extrabold tracking-[0.02em] text-neutral-800'
})

const messageClass = computed(() => {
  if (props.type === 'success') {
    return 'mx-auto  mb-10 max-w-[420px] text-base font-medium text-neutral-700'
  }
  return 'mt-2 mx-auto max-w-[420px] text-l font-semibold leading-6 text-neutral-800/90'
})

// ---------- Wrapper / Buttons ----------
const wrapperClass = computed(() => {
  // success กล่องเล็กลงตามภาพ
  if (props.type === 'success') return 'w-[450px] rounded-[20px] bg-white p-8'
  // อื่นๆ ใช้ขนาดเดิม
  return 'w-[450px] rounded-[24px] bg-white p-6 sm:p-8'
})

// ปุ่ม OK: success เล็กลงและโค้ง 12px
const okBtnClass = computed(() => {
  if (props.type === 'success') {
    return 'mx-auto h-[48px] w-[140px] rounded-[12px] bg-green-600 text-white font-semibold ' +
           'transition hover:bg-green-700 focus:outline-none '
  }
  return 'h-[58px] w-[168px] rounded-[20px] border border-neutral-200 bg-green-600 text-white transition ' +
         'hover:brightness-95 active:brightness-90 focus:outline-none'
})

// ช่องว่างปุ่ม
const actionGapClass = computed(() => (props.type === 'success' ? 'mt-0 gap-0' : 'mt-10 gap-12 sm:gap-20'))

// success ไม่ให้มี Cancel (แม้ตัวแม่จะส่งมา)
const showCancelFinal = computed(() => props.type === 'success' ? false : props.showCancel)

const okBtn = ref(null)
watch(open, (v) => { if (v) setTimeout(() => okBtn.value?.focus(), 0) })

function handleConfirm() {
  emit('confirm')
  open.value = false
}
function handleCancel() {
  emit('cancel')
  open.value = false
}
onMounted(() => { if (open.value) okBtn.value?.focus() })
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.pop-enter-active, .pop-leave-active { transition: transform .18s ease, opacity .18s ease; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: translateY(6px) scale(.98); }
</style>
