<!-- resources/js/components/IndexEvent/EventSort.vue -->
<template>
  <div ref="root" class="relative inline-block">
    <button
      type="button"
      class="inline-flex items-center gap-2 px-3 py-2 text-xl text-neutral-800 font-[Poppins]  focus:outline-none rounded-md"
      @click="open = !open"
    >
      <!-- icon -->
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <path
          fill="none"
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="m3 16l4 4l4-4m-4 4V4m4 0h10M11 8h7m-7 4h4"
        />
      </svg>
      <span>Sort</span>
    </button>

    <Transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 translate-y-1 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-1 scale-95"
    >
      <ul
        v-show="open"
        class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-56 bg-white rounded-lg shadow-lg border border-neutral-200 z-50"
        role="menu"
      >
        <li v-for="option in optionsList" :key="option.id" class="p-[3px]">
          <button
            type="button"
            @click="select(option)"
            class="w-full flex items-center  px-2 py-2 text-sm text-left  rounded-[7px]"
            :class="[
              modelValue?.id === option.id
                ? 'bg-red-50 text-red-700' /* ✅ ถ้าเลือก: พื้นแดงอ่อน ตัวหนังสือแดงเข้ม */
                : 'text-neutral-800 hover:bg-slate-50' /* ❌ ถ้าไม่เลือก: สีปกติ */,
            ]"
          >
            <span>{{ option.label }}</span>
          </button>
        </li>
      </ul>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";

defineOptions({ name: "EventSort" });

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ id: "", key: "", order: "asc", type: "text" }),
  },
  options: {
    type: Array,
    default: () => [
      {
        id: "title_asc",
        label: "ชื่องาน A–Z",
        key: "evn_title",
        order: "asc",
        type: "text",
      },
      {
        id: "title_desc",
        label: "ชื่องาน Z–A",
        key: "evn_title",
        order: "desc",
        type: "text",
      },
      {
        id: "invited_desc",
        label: "จำนวนคนเชิญมากสุด",
        key: "evn_num_guest",
        order: "desc",
        type: "number",
      },
      {
        id: "invited_asc",
        label: "จำนวนคนเชิญน้อยสุด",
        key: "evn_num_guest",
        order: "asc",
        type: "number",
      },
      {
        id: "accepted_desc",
        label: "จำนวนคนเข้าร่วมมากสุด",
        key: "evn_sum_accept",
        order: "desc",
        type: "number",
      },
      {
        id: "accepted_asc",
        label: "จำนวนคนเข้าร่วมน้อยสุด",
        key: "evn_sum_accept",
        order: "asc",
        type: "number",
      },
      {
        id: "date_desc",
        label: "วันที่จัดงานใหม่สุด",
        key: "evn_date",
        order: "desc",
        type: "date",
      },
      {
        id: "date_asc",
        label: "วันที่จัดงานเก่าสุด",
        key: "evn_date",
        order: "asc",
        type: "date",
      },
    ],
  },
  closeOnSelect: { type: Boolean, default: true },
});

const emit = defineEmits(["update:modelValue", "change"]);

const open = ref(false);
const root = ref(null);
const optionsList = computed(() => props.options);

function select(option) {
  const picked = {
    id: option.id,
    key: option.key,
    order: option.order,
    type: option.type,
  };
  emit("update:modelValue", picked);
  emit("change", picked);
  if (props.closeOnSelect) open.value = false;
}

function onDocClick(e) {
  if (root.value && !root.value.contains(e.target)) open.value = false;
}
function onKey(e) {
  if (e.key === "Escape") open.value = false;
}

onMounted(() => {
  document.addEventListener("click", onDocClick);
  document.addEventListener("keydown", onKey);
});
onUnmounted(() => {
  document.removeEventListener("click", onDocClick);
  document.removeEventListener("keydown", onKey);
});
</script>
