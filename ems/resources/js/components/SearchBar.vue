<template>
    <!--
      Container หลักของช่องค้นหา + ปุ่มค้นหา
      - flex: วางแนวนอน
      - items-end: จัดให้ชิดก้นเท่ากัน
      - gap-3: ระยะห่างระหว่างช่อง input กับปุ่ม
      - w-full: กว้างเต็มพื้นที่ของ parent (ปรับขนาดตามภายนอกได้)
      - v-bind="$attrs": รับ attrs/class จากภายนอกมาใช้ที่ root เอง (เพราะ inheritAttrs: false)
    -->
    <div class="flex items-end gap-3 w-full" v-bind="$attrs">
        <!--
          ฝั่งซ้าย (หัวข้อ + input)
          - flex-1: ขยายกินพื้นที่ที่เหลืออัตโนมัติ (responsive ตามพื้นที่)
          - min-w-0: ช่วยให้ flex item "ยอมหด" ได้ ป้องกันล้น/ดัน layout (สำคัญเวลาแคบ)
        -->
        <div class="flex-1 min-w-0">
            <!-- หัวข้อ -->
            <p class="text-neutral-800 font-medium text-2xl mb-1">Search</p>

            <!--
              Wrapper ของ input
              - relative: เพื่อให้ปุ่มล้าง (absolute) วางทับ/อ้างอิงตำแหน่งได้
            -->
            <div class="relative">
                <!--
                  ช่อง input ค้นหา
                  - v-model.trim="searchInput": ผูกค่ากับ state ภายใน และ trim ช่องว่างหัวท้าย
                  - :placeholder="placeholder": placeholder รับจาก props
                  - @keyup.enter="emitSearch": กด Enter แล้วค้นหา
                  - w-full: กว้างเต็ม wrapper (ปรับตาม flex-1 ของฝั่งซ้าย)
                  - pr-10: เผื่อพื้นที่ด้านขวาให้ปุ่มล้าง
                  - h-[52px]: ความสูงคงที่
                -->
                <input v-model.trim="searchInput" type="text" :placeholder="placeholder" @keyup.enter="emitSearch"
                    class="w-full h-[52px] pl-4 pr-10 rounded-[20px] border border-neutral-200 bg-white
                 focus:border-red-300 outline-none
                 text-neutral-700 placeholder-red-300 shadow-sm transition-colors duration-200 font-medium text-base" />

                <!--
                  ปุ่มล้างคำค้นหา (แสดงเฉพาะเมื่อมีข้อความ)
                  - v-if="searchInput": มีค่าใน input ถึงจะแสดง
                  - absolute/right-3: ชิดขวาใน input
                  - top-1/2 + -translate-y-1/2: จัดให้อยู่กึ่งกลางแนวตั้ง
                -->
                <button v-if="searchInput" @click="clearSearch" type="button"
                    class="absolute  right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-neutral-100 "
                    title="ล้างคำค้นหา">
                    <!-- ไอคอนกากบาท -->
                    <Icon icon="iconoir:cancel" width="30" height="30" class="text-neutral-400 text-xs" />
                </button>
            </div>
        </div>

        <!--
          ปุ่มค้นหาด้านขวา
          - w/[62px] + h/[60px]: ขนาดคงที่
          - shrink-0: ห้ามถูกบีบเมื่อจอแคบ (ทำให้พื้นที่ไปหดที่ฝั่ง input แทน)
          - hover/active: เอฟเฟกต์ตอนโฮเวอร์/กด
        -->
        <button @click="emitSearch"
            class="w-[62px] h-[60px] flex items-center justify-center rounded-full bg-red-700 text-white transition-all duration-200 shadow-sm shrink-0 hover:bg-red-800 active:scale-95"
            aria-label="Search" title="ค้นหา">
            <!-- ไอคอนค้นหา -->
            <Icon icon="material-symbols:search-rounded" width="40" height="40" />
        </button>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { Icon } from "@iconify/vue";

/*
  ปิดการ inherit attrs อัตโนมัติ
  แล้วไป bind เองที่ root ด้วย v-bind="$attrs"
  (ช่วยควบคุมว่า attrs จะไปอยู่ตรงไหน)
*/
defineOptions({ inheritAttrs: false });

/*
  props:
  - modelValue: สำหรับ v-model จากภายนอก
  - placeholder: ข้อความ placeholder (มีค่า default)
*/
const props = defineProps({
    modelValue: String,
    placeholder: { type: String, default: "Search..." },
});

/*
  emits:
  - update:modelValue: ทำให้ v-model ทำงาน
  - search: แจ้ง parent ว่าให้ทำการค้นหา (ส่งค่าคำค้นหาออกไป)
*/
const emit = defineEmits(["update:modelValue", "search"]);

/*
  state ภายในของ input
  - เริ่มจาก props.modelValue ถ้ามี
*/
const searchInput = ref(props.modelValue || "");

/*
  ฟังก์ชันสั่งค้นหา
  - sync ค่าออกไปให้ parent ผ่าน update:modelValue
  - ยิง event search เพื่อให้ parent ไป query/กรองข้อมูล
*/
function emitSearch() {
    emit("update:modelValue", searchInput.value);
    emit("search", searchInput.value);
}

// ฟังก์ชันล้างค่า
/*
  clearSearch:
  - ตั้งค่า input เป็น ""
  - แล้วค้นหาด้วยค่าว่างทันที เพื่อ reset ผลลัพธ์
*/
function clearSearch() {
    searchInput.value = "";
    emitSearch(); // สั่งค้นหาด้วยค่าว่างทันที (Reset ผลลัพธ์)
}

/*
  watch:
  - ถ้า parent เปลี่ยน modelValue จากภายนอก
  - ให้อัปเดต searchInput ภายในให้ตรงกัน
  - เช็คก่อนเพื่อกันการ set ซ้ำโดยไม่จำเป็น
*/
watch(() => props.modelValue, (v) => {
    if (v !== searchInput.value) searchInput.value = v || "";
});
</script>
