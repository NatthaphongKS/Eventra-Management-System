<template>
    <div class="min-h-screen bg-white">
        <div class="grid min-h-screen grid-cols-[220px,1fr]">
            <!-- Sidebar -->
            <aside
                class="sticky top-0 z-30 flex h-[100dvh] flex-col overflow-y-auto border-r border-slate-200 bg-white px-4 pt-5"
            >
                <!-- Brand -->
                <div class="mb-6 flex items-center gap-3 px-1">
                    <div
                        class="grid h-9 w-9 place-items-center rounded-2xl bg-rose-100 text-rose-600"
                    >
                        <span class="text-lg font-extrabold">c</span>
                    </div>
                    <span
                        class="text-[22px] font-semibold tracking-wide text-rose-600"
                        >Eventra</span
                    >
                </div>

                <!-- Nav -->
                <nav class="flex flex-1 flex-col gap-2">
                    <RouterLink
                        v-for="item in items"
                        :key="item.to"
                        :to="item.to"
                        class="group inline-flex items-center gap-3 rounded-2xl px-3 py-2.5 text-[15px] font-medium transition"
                        :class="
                            isActive(item.to)
                                ? 'bg-rose-100 text-rose-600'
                                : 'text-slate-700 hover:bg-slate-50'
                        "
                    >
                        <span
                            class="grid h-[30px] w-[30px] place-items-center rounded-lg"
                            :class="
                                isActive(item.to)
                                    ? 'text-rose-600'
                                    : 'text-slate-700'
                            "
                            v-html="item.icon"
                        />
                        <span class="leading-none">{{ item.label }}</span>
                    </RouterLink>
                </nav>

                <!-- Logout -->
                <div class="mt-6 pb-5">
                    <button
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-700 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-rose-800"
                        @click="logout"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-[18px] w-[18px]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3H6A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H6"
                            />
                        </svg>
                        <span>Log out</span>
                    </button>
                </div>
            </aside>

            <!-- Main (พื้นหลังเปลี่ยนเฉพาะคอนเทนต์, ไม่กระทบ Sidebar) -->
            <main
                class="p-6 transition"
                :class="[contentBg, blurMain ? 'blur-[1px]' : '']"
            >
                <header class="mb-4">
                    <h1 class="text-xl font-semibold text-slate-800">
                        {{ pageTitle }}
                    </h1>
                </header>
                <section
                    class="rounded-2xl border border-slate-200 shadow-sm"
                >
                    <slot />
                </section>
            </main>
        </div>
    </div>
</template>


<script setup>
import { ref, computed, provide } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import axios from "axios";

defineProps({ pageTitle: { type: String, default: "" } });

const route = useRoute();
const router = useRouter();

const items = ref([
    {
        label: "Dashboard",
        to: "/",
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10ZM13 3v6h8V3h-8Z"/>
    </svg>`,
    },
    {
        label: "Event",
        to: "/event",
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M7 3v4M17 3v4M3 10h18M5 6h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/>
    </svg>`,
    },
    {
        label: "Employee",
        to: "/employee",
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M16 14a4 4 0 1 1-8 0"/><circle cx="12" cy="7" r="3"/><path d="M4 21a8 8 0 0 1 16 0"/>
    </svg>`,
    },
    {
        label: "Category",
        to: "/categories",
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <circle cx="7.5" cy="7.5" r="2.5"/><circle cx="16.5" cy="7.5" r="2.5"/><path d="M5 15h6v4H5zM13 15h6v4h-6z"/>
    </svg>`,
    },
    {
        label: "History",
        to: "/history",
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12 8v5l3 2M12 3a9 9 0 1 1-9 9H1l3.5-3.5L8 12H6a6 6 0 1 0 6-6Z"/>
    </svg>`,
    },
]);

// ✅ ให้ / ตรงเป๊ะเท่านั้น, ส่วนเมนูอื่นใช้ startsWith ได้
const isActive = (to) =>
    to === "/" ? route.path === "/" : route.path.startsWith(to);

// ✅ เปลี่ยนพื้นหลังเฉพาะโซน Main เมื่ออยู่หน้า Employee
const contentBg = computed(() =>
    route.path.startsWith("/employee") ? "bg-rose-50" : "bg-white"
);

// ✅ ให้เพจลูกควบคุม blur เวลาเปิด modal
const blurMain = ref(false);
provide("setLayoutBlur", (v) => (blurMain.value = !!v));

axios.defaults.withCredentials = true;
const token = document.querySelector('meta[name="csrf-token"]')?.content;
if (token) axios.defaults.headers.common["X-CSRF-TOKEN"] = token;

const logout = async () => {
    try {
        await axios.post("/logout");
        router.push("/login");
    } catch (e) {
        console.error("Logout failed", e);
    }
};
</script>
