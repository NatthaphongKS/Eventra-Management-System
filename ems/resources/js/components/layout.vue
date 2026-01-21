<template>
  <div class="min-h-screen bg-rose-50">
    <div class="grid min-h-screen grid-cols-[220px,1fr]">
      <aside
        class="sticky top-0 z-30 flex h-[100dvh] rounded rounded-r-[20px] flex-col overflow-y-auto bg-white px-4 pt-5 shadow-lg"
      >
        <div class="mt-6 mb-6 flex items-center gap-3 px-1">
          <div class="flex">
            <img
              src="../../../public/images/email/clicknext.jpeg"
              alt="Remote"
              class="w-10 h-10 object-cover rounded-xl"
              loading="lazy"
            />
            <span class="ml-3 text-3xl font-semibold tracking-wide text-red-700 mb-12"
              >Eventra</span
            >
          </div>
        </div>

        <nav class="flex flex-1 flex-col gap-2">
          <template v-for="item in items" :key="item.to">
            <div v-if="item.children" class="flex flex-col">
              <button
                type="button"
                @click="handleParentClick(item)"
                class="group relative inline-flex items-center justify-between rounded-2xl px-3 py-2.5 text-lg font-semibold transition"
                :class="
                  isParentActive(item)
                    ? 'bg-rose-100 text-red-700'
                    : 'text-neutral-700 hover:bg-neutral-200'
                "
              >
                <div
                  v-if="isParentActive(item)"
                  class="absolute left-0 top-1/2 h-16 w-1 -translate-y-1/2 rounded-r-full bg-red-700"
                ></div>
                <span class="px-2 inline-flex items-center gap-4">
                  <span
                    class="grid h-[40px] w-[30px] place-items-center rounded-lg"
                    :class="isParentActive(item) ? 'text-red-700' : 'text-neutral-700'"
                    v-html="item.icon"/>
                  <span class="leading-none">{{ item.label }}</span>
                </span>
              </button>

              <div
                v-show="expanded[item.to]"
                class="ml-8 mt-1 flex flex-col gap-1 pl-3"
              >
                <RouterLink
                  v-for="child in item.children"
                  :key="child.to"
                  :to="child.to"
                  class="relative inline-flex items-center gap-2 rounded-xl px-3 py-2 text-[14px] transition"
                  :class="
                    isActive(child.to)
                      ? 'text-red-700'
                      : 'text-neutral-700 hover:text-red-600'
                  "
                >
                  <span
                    class=""
                    :class="isActive(child.to) ? 'bg-red-700' : 'bg-neutral-200'"
                  />
                  <span>{{ child.label }}</span>
                </RouterLink>
              </div>
            </div>

            <RouterLink
              v-else
              :to="item.to"
              class="group relative inline-flex items-center gap-3 rounded-2xl px-3 py-2.5 text-lg transition font-semibold"
              :class="
                isActive(item.to)
                  ? 'bg-red-100 text-red-700'
                  : 'text-neutral-700 hover:bg-neutral-200'
              "
            >
              <div
                v-if="isActive(item.to)"
                class="absolute left-0 top-1/2 h-16 w-1 -translate-y-1/2 rounded-r-full bg-red-700"
              ></div>
              <span
                class="grid h-[40px] w-[40px] place-items-center rounded-lg"
                :class="isActive(item.to) ? 'text-red-700' : 'text-neutral-700'"
                v-html="item.icon"
              />
              <span class="leading-none">{{ item.label }}</span>
            </RouterLink>
          </template>
        </nav>

        <div class="mt-6 pb-5">
          <button
            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-700 px-5 py-3 text-lg font-medium text-white shadow-lg transition hover:bg-red-600"
            @click="logout"
          >
            <Icon
              icon="material-symbols:logout-rounded"
              width="28"
              height="28"
              style="color: #fff"
            />
            <span>Log out</span>
          </button>
        </div>
      </aside>

      <main class="p-6">
        <header class="mb-4">
          <h1 class="text-6xl text-red-700 font-semibold">{{ pageTitle }}</h1>
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

const items = ref([
  {
    label: "Dashboard",
    to: "/",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">

        <path fill="currentColor" d="M6 19h3v-5q0-.425.288-.712T10 13h4q.425 0 .713.288T15 14v5h3v-9l-6-4.5L6 10zm-2 0v-9q0-.475.213-.9t.587-.7l6-4.5q.525-.4 1.2-.4t1.2.4l6 4.5q.375.275.588.7T20 10v9q0 .825-.588 1.413T18 21h-4q-.425 0-.712-.288T13 20v-5h-2v5q0 .425-.288.713T10 21H6q-.825 0-1.412-.587T4 19m8-6.75" />

    </svg>`,
  },
  {
    label: "Event",
    to: "/event",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">

        <path fill="currentColor" d="M14.5 18q-1.05 0-1.775-.725T12 15.5t.725-1.775T14.5 13t1.775.725T17 15.5t-.725 1.775T14.5 18M5 22q-.825 0-1.412-.587T3 20V6q0-.825.588-1.412T5 4h1V2h2v2h8V2h2v2h1q.825 0 1.413.588T21 6v14q0 .825-.587 1.413T19 22zm0-2h14V10H5z" />

    </svg>`,
  },
  {
    label: "Employee",
    to: "/employee",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 56 56">

        <path fill="currentColor" d="M28 27.126c3.194 0 5.941-2.852 5.941-6.566c0-3.669-2.762-6.387-5.941-6.387s-5.942 2.778-5.942 6.417c0 3.684 2.748 6.536 5.942 6.536m-17.097.341c2.763 0 5.17-2.495 5.17-5.718c0-3.194-2.422-5.556-5.17-5.556c-2.763 0-5.199 2.421-5.184 5.585c0 3.194 2.406 5.69 5.184 5.69m34.194 0c2.778 0 5.184-2.495 5.184-5.689c0-3.164-2.421-5.585-5.184-5.585c-2.748 0-5.17 2.362-5.17 5.555c0 3.224 2.407 5.72 5.17 5.72M2.614 40.881h11.29c-1.545-2.243.341-6.759 3.535-9.225c-1.65-1.099-3.773-1.916-6.55-1.916C4.188 29.74 0 34.686 0 38.801c0 1.337.743 2.08 2.614 2.08m50.772 0c1.886 0 2.614-.743 2.614-2.08c0-4.115-4.189-9.061-10.888-9.061c-2.778 0-4.902.817-6.55 1.916c3.193 2.466 5.08 6.982 3.535 9.225Zm-34.73 0h18.672c2.332 0 3.164-.669 3.164-1.976c0-3.832-4.798-9.12-12.507-9.12c-7.694 0-12.492 5.288-12.492 9.12c0 1.307.832 1.976 3.164 1.976" />

    </svg>`,
  },
  {
    label: "Category",
    to: "/categories",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 14 14">

        <path fill="currentColor" fill-rule="evenodd" d="M3.188.05c-.503 0-.99.046-1.447.097A1.826 1.826 0 0 0 .134 1.759a14 14 0 0 0-.092 1.436c0 .499.043.982.092 1.437c.09.844.765 1.518 1.607 1.612c.457.051.944.097 1.447.097q.353-.001.693-.026a4.8 4.8 0 0 1 .987-1.437a4.8 4.8 0 0 1 1.44-.988q.024-.341.025-.695c0-.498-.043-.981-.092-1.436A1.826 1.826 0 0 0 4.634.147A13 13 0 0 0 3.187.05m0 7.604q.144 0 .287.005q-.104.602-.103 1.223c0 1.487.424 2.933 1.496 4.004q.327.327.698.576a1.8 1.8 0 0 1-.932.387c-.457.05-.944.096-1.447.096s-.99-.045-1.446-.096a1.826 1.826 0 0 1-1.607-1.612A14 14 0 0 1 .042 10.8c0-.498.043-.982.092-1.437A1.826 1.826 0 0 1 1.74 7.751c.457-.05.944-.097 1.447-.097m10.678-3.022a1.8 1.8 0 0 1-.404.958a4.7 4.7 0 0 0-.586-.712c-1.071-1.072-2.517-1.496-4.004-1.496a7 7 0 0 0-1.2.1a9 9 0 0 1-.005-.287c0-.498.043-.981.092-1.436A1.826 1.826 0 0 1 9.366.147C9.823.096 10.31.05 10.813.05s.99.046 1.446.097a1.825 1.825 0 0 1 1.607 1.612c.049.455.092.938.092 1.436c0 .499-.043.982-.092 1.437m-7.054 2.19c-.416.417-.69 1.078-.69 2.06s.274 1.643.69 2.06c.417.416 1.078.69 2.06.69s1.643-.274 2.06-.69c.416-.417.69-1.078.69-2.06s-.274-1.643-.69-2.06c-.417-.416-1.078-.69-2.06-.69s-1.643.274-2.06.69m-1.06-1.06c.773-.774 1.862-1.13 3.12-1.13s2.347.356 3.12 1.13s1.13 1.862 1.13 3.12c0 .967-.21 1.834-.659 2.535l1.253 1.253a.75.75 0 1 1-1.06 1.06l-1.254-1.254c-.7.447-1.565.656-2.53.656c-1.258 0-2.347-.356-3.12-1.13s-1.13-1.862-1.13-3.12s.356-2.347 1.13-3.12" clip-rule="evenodd" />

    </svg>`,
  },
  {
    label: "History",
    to: "history-group", // Key สำหรับกลุ่ม
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">

        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3M3.223 14A9 9 0 1 0 12 3a9 9 0 0 0-8.294 5.5M7 9H3V5" />

    </svg>`,
    children: [
      { label: "Event", to: "/history-event" },
      { label: "Employee", to: "/history-employee" },
      { label: "Category", to: "/history-category" },
    ],
  },
]);

// ฟังก์ชันเช็คว่าลูกคนใดคนหนึ่ง Active หรือไม่
const isParentActive = (item) => {
  if (item.children && item.children.length > 0) {
    return item.children.some((child) => isActive(child.to));
  }
  return isActive(item.to);
};

const isActive = (to) => {
  if (!to) return false;

  // 1. เช็คจาก meta.activeMenu ก่อน (ถ้า Router ตั้งค่ามา ให้ยึดตามนี้)
  if (route.meta && route.meta.activeMenu) {
    if (route.meta.activeMenu === to) return true;
  }

  // 2. ถ้าไม่มี meta ให้ใช้ Logic เดิม (เช็ค URL ตรงๆ)
  if (to === "/") return route.path === "/";

  // เช็คเผื่อกรณี Query param หรือ Trailing slash
  return route.path === to || route.path.startsWith(to + "/");
};

const expanded = ref({});

// ปรับปรุง: ใช้ isParentActive ในการเช็คเพื่อ Auto Open
const syncExpandedWithRoute = () => {
  items.value.forEach((item) => {
    if (item.children) {
      // ถ้าลูกคนใดคนหนึ่ง Active ให้เปิดเมนูแม่ค้างไว้
      if (isParentActive(item)) {
        expanded.value[item.to] = true;
      }
    }
  });
};

watch(() => route.path, syncExpandedWithRoute, { immediate: true });
const handleParentClick = (item) => {
  if (item.children && item.children.length > 0) {
    router.push(item.children[0].to);
  }
};
const toggle = (key) => {
  // ถ้าอยากให้เปิดได้ทีละเมนู (Accordion) ให้เปิดคอมเมนต์ด้านล่างนี้
  // Object.keys(expanded.value).forEach(k => {
  //    if (k !== key) expanded.value[k] = false
  // })

  expanded.value[key] = !expanded.value[key];
};

const logout = async () => {
  try {
    await axios.post("/logout");
    router.push("/login");
  } catch (e) {
    console.error("Logout failed", e);
  }
};
</script>
