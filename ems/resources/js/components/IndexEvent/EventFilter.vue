<template>
  <div class="relative z-50 inline-block" ref="root">
    <button
      type="button"
      @click="isOpen = !isOpen"
      class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xl text-neutral-800  transition-colors duration-200 font-[Poppins]"
    >
      <svg
        class="w-6 h-6"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        viewBox="0 0 24 24"
      >
        <line x1="4" y1="7" x2="20" y2="7" />
        <line x1="6" y1="12" x2="16" y2="12" />
        <line x1="8" y1="17" x2="12" y2="17" />
      </svg>
      <span>Filter</span>
    </button>

    <Transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 translate-y-1 scale-95"
      enter-to-class="opacity-100 translate-y-0  scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0  scale-100"
      leave-to-class="opacity-0 translate-y-1  scale-95"
    >
      <div
        v-show="isOpen"
        class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-[170px] bg-white rounded-[10px] shadow-lg border border-gray-100 z-50 p-4 space-y-2"
        @click.stop
      >
        <div class="mt-[6px]">
          <label
            class="flex items-center cursor-pointer hover:bg-gray-50 rounded-md transition-colors"
          >
            <input
              type="checkbox"
              class="accent-red-700 h-4 w-4 rounded border-gray-300 focus:ring-red-500"
              :checked="isAllSelected"
              @change="toggleAll"
            />
            <span class="text-[14px] text-neutral-800 uppercase ml-2">Filter All</span>
          </label>
        </div>

        <div class="mt-[6px]">
          <div class="text-neutral-800 uppercase text-[14px]">Category</div>
          <div class="flex flex-col mt-[5px]">
            <label
              v-for="c in categories"
              :key="c.id"
              class="flex items-center mt-1 cursor-pointer hover:bg-gray-50 rounded-lg transition-colors"
            >
              <input
                type="checkbox"
                class="accent-red-700 h-4 w-4 rounded border-gray-300"
                :checked="local.category.has(String(c.id))"
                @change="toggle('category', String(c.id))"
              />
              <span class="text-neutral-800 text-[14px] ml-2">{{ c.cat_name }}</span>
            </label>
          </div>
        </div>

        <div class="mt-[6px]">
          <div class="text-neutral-800 uppercase text-[14px]">Status</div>
          <div class="flex flex-col mt-[5px]">
            <label
              v-for="s in statusOptions"
              :key="s.value"
              class="flex items-center mt-1 cursor-pointer hover:bg-gray-50 rounded-lg transition-colors"
            >
              <input
                type="checkbox"
                class="accent-red-700 h-4 w-4 rounded border-gray-300"
                :checked="local.status.has(s.value)"
                @change="toggle('status', s.value)"
              />
              <span class="text-neutral-800 text-[14px] ml-2">{{ s.label }}</span>
            </label>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
// Script ใช้เหมือนเดิมได้เลยครับ ไม่ต้องแก้ไข
import { computed, onMounted, onBeforeUnmount, ref, watch } from "vue";

type Filters = { category: Array<string | number>; status: string[] };
type Category = { id: string | number; cat_name: string };
type StatusOpt = { label: string; value: string };

const props = withDefaults(
  defineProps<{
    modelValue?: Filters;
    categories?: Category[];
    statusOptions?: StatusOpt[];
  }>(),
  {
    modelValue: () => ({ category: [], status: [] }),
    categories: () => [],
    statusOptions: () => [
      { label: "Done", value: "done" },
      { label: "Ongoing", value: "ongoing" },
      { label: "Upcoming", value: "upcoming" },
    ],
  }
);

const emit = defineEmits<{
  (e: "update:modelValue", v: Filters): void;
  (e: "apply", v: Filters): void;
  (e: "clear"): void;
}>();

const isOpen = ref(false);
const root = ref<HTMLElement | null>(null);

const local = ref<{ category: Set<string | number>; status: Set<string> }>({
  category: new Set(props.modelValue.category ?? []),
  status: new Set(props.modelValue.status ?? []),
});

watch(
  () => props.modelValue,
  (v) => {
    local.value.category = new Set(v?.category ?? []);
    local.value.status = new Set(v?.status ?? []);
  },
  { deep: true }
);

const isAllSelected = computed(() => {
  if (props.categories.length === 0 && props.statusOptions.length === 0) return false;

  const catAll =
    props.categories.length === 0 ||
    props.categories.every((c) => local.value.category.has(String(c.id)));

  const stAll =
    props.statusOptions.length === 0 ||
    props.statusOptions.every((s) => local.value.status.has(s.value));

  return catAll && stAll;
});

function toggleAll() {
  const all = isAllSelected.value;
  if (all) {
    local.value.category.clear();
    local.value.status.clear();
  } else {
    local.value.category = new Set(props.categories.map((c) => String(c.id)));
    local.value.status = new Set(props.statusOptions.map((s) => s.value));
  }
  push();
}

function toggle(key: "category" | "status", value: string | number) {
  const set = local.value[key];
  // @ts-ignore
  set.has(value) ? set.delete(value) : set.add(value);
  push();
}

function push() {
  emit("update:modelValue", {
    category: Array.from(local.value.category),
    status: Array.from(local.value.status),
  });
}

function clear() {
  local.value.category.clear();
  local.value.status.clear();
  push();
  emit("clear");
}

function onDocClick(e: MouseEvent) {
  if (!root.value) return;
  if (!root.value.contains(e.target as Node)) isOpen.value = false;
}
onMounted(() => document.addEventListener("click", onDocClick));
onBeforeUnmount(() => document.removeEventListener("click", onDocClick));
</script>
