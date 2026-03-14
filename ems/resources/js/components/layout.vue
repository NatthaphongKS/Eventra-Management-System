<!--
/**
* ชื่อไฟล์: Layout.vue
* คำอธิบาย: Component โครงสร้างหลักของหน้าเว็บ (Main Layout) ใช้สำหรับจัดโครงหน้าของระบบ
* ประกอบด้วย Sidebar สำหรับเมนูนำทาง และพื้นที่แสดงเนื้อหาหลักของแต่ละหน้า
* โดยเนื้อหาหลักจะถูกแสดงผ่าน <slot /> จาก Component ลูก
*
* โครงสร้างหลักของ Layout:
* * Sidebar : เมนูนำทางของระบบ เช่น Dashboard, Event, Employee, Category, Organization และ History
* * Header : แสดงปุ่มเปิด/ปิด Sidebar และชื่อหน้าปัจจุบัน (pageTitle)
* * Content : ส่วนแสดงเนื้อหาของแต่ละหน้าที่ถูกส่งเข้ามาผ่าน slot
*
* Input:
* * pageTitle (String) : รับค่าชื่อหน้าจาก Component ลูก เพื่อแสดงในส่วน Header
*
* Output:
* * แสดงโครงสร้าง Layout ของระบบ พร้อม Sidebar สำหรับนำทางไปยังหน้าต่าง ๆ
* * แสดงเนื้อหาของหน้าอื่น ๆ ผ่าน <slot />
*
* ฟังก์ชันหลัก:
* * เปิด/ปิด Sidebar (sidebarOpen)
* * ตรวจสอบเมนูที่ Active ตาม route ปัจจุบัน
* * ขยาย/ย่อเมนูที่มี submenu (expanded)
* * Logout ผู้ใช้งานและนำทางกลับไปหน้า Login
*
* ชื่อผู้เขียน/แก้ไข: Thanusin Leenarat
* วันที่จัดทำ/แก้ไข: 9 มีนาคม 2569
  */
-->
<template>
    <div class="min-h-screen bg-rose-50">
        <div class="grid min-h-screen transition-all duration-300 ease-in-out"
            :style="{ gridTemplateColumns: sidebarOpen ? '255px 1fr' : '0 1fr' }">
            <aside
                class="sticky top-0 z-30 flex h-[100dvh] rounded rounded-r-[20px] flex-col overflow-y-auto bg-white px-4 pt-5 shadow-lg transition-all duration-300 ease-in-out"
                :class="{ 'opacity-0 invisible overflow-hidden': !sidebarOpen, 'opacity-100 visible': sidebarOpen }">
                <div class="mt-6 mb-12 flex items-center gap-3 px-1">
                    <RouterLink to="/" class="flex items-center">
                        <img src="../../../public/images/email/clicknext.jpeg" alt="Remote"
                            class="w-10 h-10 object-cover rounded-xl" loading="lazy" />
                        <span class="ml-3 text-3xl font-semibold tracking-wide text-red-700">
                            Eventra
                        </span>
                    </RouterLink>
                </div>

                <nav class="flex flex-1 flex-col gap-2">
                    <template v-for="item in items" :key="item.to">
                        <div v-if="item.children" class="flex flex-col">
                            <button type="button" @click="handleParentClick(item)"
                                class="group relative inline-flex items-center justify-between rounded-2xl px-3 py-2.5 text-lg font-semibold transition"
                                :class="isParentActive(item)
                                    ? 'bg-rose-100 text-red-700'
                                    : 'text-neutral-700 hover:bg-neutral-200'
                                    ">
                                <div v-if="isParentActive(item)"
                                    class="absolute left-[-16px] top-1/2 h-16 w-1 -translate-y-1/2 rounded-r-full bg-red-700">
                                </div>
                                <span class="px-2 inline-flex items-center gap-4">
                                    <span class="grid h-[40px] w-[30px] place-items-center rounded-lg"
                                        :class="isParentActive(item) ? 'text-red-700' : 'text-neutral-700'"
                                        v-html="item.icon" />
                                    <span class="leading-none">{{ item.label }}</span>
                                </span>
                                <Icon icon="heroicons:chevron-down-20-solid" class="transition-transform duration-200"
                                    :class="{ 'rotate-180': expanded[item.to] }" />
                            </button>

                            <div v-show="expanded[item.to]" class="ml-8 mt-1 flex flex-col gap-1 pl-3">
                                <RouterLink v-for="child in item.children" :key="child.to" :to="child.to"
                                    class="relative inline-flex items-center gap-2 rounded-xl px-3 py-2 text-[14px] transition"
                                    :class="isActive(child.to)
                                        ? 'text-red-700 font-bold'
                                        : 'text-neutral-700 hover:text-red-600'
                                        ">
                                    <span class="w-1 h-1 rounded-full"
                                        :class="isActive(child.to) ? 'bg-red-700' : 'bg-neutral-300'" />
                                    <span>{{ child.label }}</span>
                                </RouterLink>
                            </div>
                        </div>

                        <RouterLink v-else :to="item.to"
                            class="group relative inline-flex items-center gap-3 rounded-2xl px-3 py-2.5 text-lg transition font-semibold"
                            :class="isActive(item.to)
                                ? 'bg-red-100 text-red-700'
                                : 'text-neutral-700 hover:bg-neutral-200'
                                ">
                            <div v-if="isActive(item.to)"
                                class="absolute left-[-16px] top-1/2 h-16 w-1 -translate-y-1/2 rounded-r-full bg-red-700">
                            </div>
                            <span class="grid h-[40px] w-[40px] place-items-center rounded-lg"
                                :class="isActive(item.to) ? 'text-red-700' : 'text-neutral-700'" v-html="item.icon" />
                            <span class="leading-none">{{ item.label }}</span>
                        </RouterLink>
                    </template>
                </nav>

                <div class="mt-6 pb-5">
                    <button
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-700 px-5 py-3 text-lg font-medium text-white shadow-lg transition hover:bg-red-600"
                        @click="logout">
                        <Icon icon="material-symbols:logout-rounded" width="28" height="28" style="color: #fff" />
                        <span>Log out</span>
                    </button>
                </div>
            </aside>

            <main class="p-6 overflow-x-hidden">
                <header class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-neutral-200 transition"
                            title="Toggle sidebar">
                            <svg v-if="!sidebarOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <line x1="3" y1="12" x2="21" y2="12" />
                                <line x1="3" y1="18" x2="21" y2="18" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                        </button>
                        <h1 class="text-6xl text-red-700 font-semibold leading-tight">{{ pageTitle }}</h1>
                    </div>
                </header>
                <section class="rounded-2xl bg-white p-5 shadow-lg">
                    <slot />
                </section>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import { Icon } from "@iconify/vue";

defineProps({ pageTitle: { type: String, default: "" } });

const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(true);
const expanded = ref({});

const items = ref([
    {
        label: "Dashboard",
        to: "/",
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"><path fill="currentColor" d="M6 19h3v-5q0-.425.288-.712T10 13h4q.425 0 .713.288T15 14v5h3v-9l-6-4.5L6 10zm-2 0v-9q0-.475.213-.9t.587-.7l6-4.5q.525-.4 1.2-.4t1.2.4l6 4.5q.375.275.588.7T20 10v9q0 .825-.588 1.413T18 21h-4q-.425 0-.712-.288T13 20v-5h-2v5q0 .425-.288.713T10 21H6q-.825 0-1.412-.587T4 19m8-6.75" /></svg>`,
    },
    {
        label: "Event",
        to: "/event",
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"><path fill="currentColor" d="M14.5 18q-1.05 0-1.775-.725T12 15.5t.725-1.775T14.5 13t1.775.725T17 15.5t-.725 1.775T14.5 18M5 22q-.825 0-1.412-.587T3 20V6q0-.825.588-1.412T5 4h1V2h2v2h8V2h2v2h1q.825 0 1.413.588T21 6v14q0 .825-.587 1.413T19 22zm0-2h14V10H5z" /></svg>`,
    },
    {
        label: "Employee",
        to: "/employee",
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 56 56"><path fill="currentColor" d="M28 27.126c3.194 0 5.941-2.852 5.941-6.566c0-3.669-2.762-6.387-5.941-6.387s-5.942 2.778-5.942 6.417c0 3.684 2.748 6.536 5.942 6.536m-17.097.341c2.763 0 5.17-2.495 5.17-5.718c0-3.194-2.422-5.556-5.17-5.556c-2.763 0-5.199 2.421-5.184 5.585c0 3.194 2.406 5.69 5.184 5.69m34.194 0c2.778 0 5.184-2.495 5.184-5.689c0-3.164-2.421-5.585-5.184-5.585c-2.748 0-5.17 2.362-5.17 5.555c0 3.224 2.407 5.72 5.17 5.72M2.614 40.881h11.29c-1.545-2.243.341-6.759 3.535-9.225c-1.65-1.099-3.773-1.916-6.55-1.916C4.188 29.74 0 34.686 0 38.801c0 1.337.743 2.08 2.614 2.08m50.772 0c1.886 0 2.614-.743 2.614-2.08c0-4.115-4.189-9.061-10.888-9.061c-2.778 0-4.902.817-6.55 1.916c3.193 2.466 5.08 6.982 3.535 9.225Zm-34.73 0h18.672c2.332 0 3.164-.669 3.164-1.976c0-3.832-4.798-9.12-12.507-9.12c-7.694 0-12.492 5.288-12.492 9.12c0 1.307.832 1.976 3.164 1.976" /></svg>`,
    },
    {
        label: "Category",
        to: "/categories",
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 14 14"><path fill="currentColor" fill-rule="evenodd" d="M3.188.05c-.503 0-.99.046-1.447.097A1.826 1.826 0 0 0 .134 1.759a14 14 0 0 0-.092 1.436c0 .499.043.982.092 1.437c.09.844.765 1.518 1.607 1.612c.457.051.944.097 1.447.097q.353-.001.693-.026a4.8 4.8 0 0 1 .987-1.437a4.8 4.8 0 0 1 1.44-.988q.024-.341.025-.695c0-.498-.043-.981-.092-1.436A1.826 1.826 0 0 0 4.634.147A13 13 0 0 0 3.187.05m0 7.604q.144 0 .287.005q-.104.602-.103 1.223c0 1.487.424 2.933 1.496 4.004q.327.327.698.576a1.8 1.8 0 0 1-.932.387c-.457.05-.944.096-1.447.096s-.99-.045-1.446-.096a1.826 1.826 0 0 1-1.607-1.612A14 14 0 0 1 .042 10.8c0-.498.043-.982.092-1.437A1.826 1.826 0 0 1 1.74 7.751c.457-.05.944-.097 1.447-.097m10.678-3.022a1.8 1.8 0 0 1-.404.958a4.7 4.7 0 0 0-.586-.712c-1.071-1.072-2.517-1.496-4.004-1.496a7 7 0 0 0-1.2.1a9 9 0 0 1-.005-.287c0-.498.043-.981.092-1.436A1.826 1.826 0 0 1 9.366.147C9.823.096 10.31.05 10.813.05s.99.046 1.446.097a1.825 1.825 0 0 1 1.607 1.612c.049.455.092.938.092 1.436c0 .499-.043.982-.092 1.437m-7.054 2.19c-.416.417-.69 1.078-.69 2.06s.274 1.643.69 2.06c.417.416 1.078.69 2.06.69s1.643-.274 2.06-.69c.416-.417.69-1.078.69-2.06s-.274-1.643-.69-2.06c-.417-.416-1.078-.69-2.06-.69s-1.643.274-2.06.69m-1.06-1.06c.773-.774 1.862-1.13 3.12-1.13s2.347.356 3.12 1.13s1.13 1.862 1.13 3.12c0 .967-.21 1.834-.659 2.535l1.253 1.253a.75.75 0 1 1-1.06 1.06l-1.254-1.254c-.7.447-1.565.656-2.53.656c-1.258 0-2.347-.356-3.12-1.13s-1.13-1.862-1.13-3.12s.356-2.347 1.13-3.12" clip-rule="evenodd" /></svg>`,
    },
    {
        label: "Organization",
        to: "organization-group",
        icon: `<svg viewBox="0 0 1024 1024" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M531.8 385v483.3h0.1V385h-0.1z" fill="#343535"></path><path d="M670.9 497.1h86v16h-86zM670.9 625.1h86v16h-86zM233.9 241.1h86v16h-86zM384 241.1h86v16h-86zM233.9 369h86v16h-86zM384 369h86v16h-86zM234 497.5h86v16h-86zM384 497.2h86v16h-86z" fill="#39393A"></path><path d="M398.3 704.4c-11.9-11.9-28.4-19.3-46.5-19.3-36.2 0-65.8 29.6-65.8 65.8v117.4h20V750.9c0-12.2 4.8-23.6 13.5-32.3 8.7-8.7 20.2-13.5 32.3-13.5 12.2 0 23.6 4.8 32.3 13.5 8.7 8.7 13.5 20.2 13.5 32.3v117.4h20V750.9c0-18.1-7.4-34.5-19.3-46.5z" fill="#E73B37"></path><path d="M575.8 429v437.9h0.1V429h-0.1zM286.2 868.3h131.6-131.6z" fill="#343535"></path><path d="M896 868.3V385H575.9V111.6H128v756.7H64v44h896v-44h-64z m-364.1 0H172V155.6h359.9v712.7z m320.1-1.5H575.8V429H852v437.8z" fill="#39393A"></path></g></svg>`,
        children: [
            { label: "Department", to: "/departments" },
            { label: "Team", to: "/teams" },
            { label: "Position", to: "/positions" },
        ],
    },
    {
        label: "History",
        to: "history-group",
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3M3.223 14A9 9 0 1 0 12 3a9 9 0 0 0-8.294 5.5M7 9H3V5" /></svg>`,
        children: [
            { label: "Event", to: "/history-event" },
            { label: "Employee", to: "/history-employee" },
            { label: "Category", to: "/history-category" },
        ],
    },
]);

const isActive = (to) => {
    if (!to) return false;
    if (route.meta?.activeMenu === to) return true;
    if (to === "/") return route.path === "/";
    return route.path === to || route.path.startsWith(to + "/");
};

const isParentActive = (item) => {
    if (item.children) {
        return item.children.some((child) => isActive(child.to));
    }
    return isActive(item.to);
};

// ฟังก์ชันเปิดเมนูแม่ค้างไว้เมื่ออยู่ในหน้าลูก
const syncExpandedWithRoute = () => {
    items.value.forEach((item) => {
        if (item.children && isParentActive(item)) {
            expanded.value[item.to] = true;
        }
    });
};

watch(() => route.path, syncExpandedWithRoute, { immediate: true });

const handleParentClick = (item) => {
    expanded.value[item.to] = !expanded.value[item.to];
};

const logout = async () => {
    try {
        const { data } = await axios.post("/logout");
        if (data.csrf_token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = data.csrf_token;
        }
        router.push("/login");
    } catch (e) {
        console.error("Logout failed", e);
    }
};
</script>
