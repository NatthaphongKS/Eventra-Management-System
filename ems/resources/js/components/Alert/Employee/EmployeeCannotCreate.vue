<template>
    <teleport to="body">
        <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
            <!-- overlay ดำ -->
            <div class="absolute inset-0 bg-black/50"></div>

            <!-- modal card -->
            <div
                class="relative bg-white rounded-3xl shadow-xl text-center w-[480px] max-w-[90vw] p-6 flex flex-col items-center">

                <!-- ไอคอนกากบาทวงกลมทึบสีแดง -->
                <ExclamationCircleIcon class="mt-6 h-[96px] w-[96px] text-red-600" />

                <!-- กล่องข้อความ -->
                <div class="mt-8 text-center flex flex-col items-center leading-snug">
                    <!-- บรรทัดบน -->
                    <div class="text-[20px] font-bold text-neutral-800 uppercase tracking-tight">
                        ERROR!
                    </div>

                    <!-- บรรทัดล่าง: แสดงข้อความจาก Prop (ถ้าไม่มีให้ใช้ค่า Default) -->
                    <div
                        class="mt-6 text-[14px] font-normal text-neutral-800 normal-case px-4 break-words whitespace-pre-wrap">
                        {{ message }}
                    </div>
                </div>

                <!-- ปุ่ม OK -->
                <div class="mt-12 mb-4 flex items-center justify-center w-full">
                    <button type="button" @click="$emit('close')"
                        class="min-w-[88px] h-[38px] rounded-[10px] bg-green-600 text-white text-[14px] font-semibold px-5 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                        autofocus>
                        OK
                    </button>
                </div>
            </div>
        </div>
    </teleport>
</template>

<script>
import { ExclamationCircleIcon } from '@heroicons/vue/24/solid'

export default {
    name: 'EmployeeCannotCreate',
    components: { ExclamationCircleIcon },
    props: {
        open: { type: Boolean, default: false },
        message: {
            type: String,
            default: 'Please try again later.'
        }
    },
    emits: ['close'],
}
</script>
