<template>
    <teleport to="body">
        <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
            <!-- overlay ดำ -->
            <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>

            <!-- modal card -->
            <div
                class="relative bg-white rounded-3xl shadow-xl text-center w-[400px] max-w-[90vw] p-6 flex flex-col items-center">

                <!-- ไอคอนกากบาทใหญ่ สีแดง ไม่มีวงขาว -->
                <XCircleIcon class="w-[96px] h-[96px] text-red-600 mt-6" />

                <!-- กล่องข้อความ -->
                <div class="mt-8 text-center flex flex-col items-center leading-snug">
                    <!-- บรรทัดบน -->
                    <div class="text-[20px] font-bold text-neutral-800 uppercase tracking-tight">
                        CAN’T CREATE EMPLOYEE!
                    </div>

                    <!-- บรรทัดล่าง -->
                    <div class="mt-6 text-[14px] font-normal text-neutral-800 normal-case whitespace-nowrap">
                        This data already exists in the system.
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
import { XCircleIcon } from '@heroicons/vue/24/solid'

export default {
    name: 'EmployeeCannotCreate',
    components: { XCircleIcon },
    props: {
        open: { type: Boolean, default: false },
    },
    emits: ['close'],
}
</script>
