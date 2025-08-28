<template>
  <div class="min-h-screen bg-rose-50">
    <div class="grid min-h-screen grid-cols-[220px,1fr]">
      <!-- Sidebar -->
      <aside class="sticky top-0 z-30 flex h-[100dvh] rounded rounded-3xl flex-col overflow-y-auto bg-white px-4 pt-5 shadow-lg">
        <!-- Brand -->
        <div class="mb-6 flex items-center gap-3 px-1">
          <div class="grid h-9 w-9 place-items-center rounded-2xl text-rose-600">
            <!-- วางไอคอน/ตัวอักษรตามภาพ -->
          </div>
          <span class="text-[25px] font-semibold tracking-wide text-red-700 mb-12">Eventra</span>
        </div>

        <!-- Nav -->
        <nav class="flex flex-1 flex-col gap-2 ">
                    <template v-for="item in items" :key="item.to">
                        <!-- มี children = เมนูหลักแบบพับได้ -->
                        <div v-if="item.children" class="flex flex-col ">
                            <button type="button" @click="toggle(item.to)"
                                class="group inline-flex items-center justify-between rounded-2xl px-3 py-2.5 text-lg font-medium transition"
                                :class="(isActive(item.to) || expanded[item.to])
                                    ? 'bg-rose-100 text-red-700'
                                    : 'text-gray-700 hover:bg-slate-50'">
                                <span class="inline-flex items-center gap-3">
                                    <span class="grid h-[30px] w-[30px] place-items-center rounded-lg"
                                        :class="(isActive(item.to) || expanded[item.to]) ? 'text-red-700' : 'text-gray-700'"
                                        v-html="item.icon" />
                                    <span class="leading-none">{{ item.label }}</span>
                                </span>

                                <!-- caret -->
                                <svg class="h-4 w-4 transition-transform" :class="expanded[item.to] ? 'rotate-90' : ''"
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M9 6l6 6-6 6" />
                                </svg>
                            </button>

                            <!-- Submenu -->
                            <div v-show="expanded[item.to]"
                                class="ml-4 mt-1 flex flex-col gap-1 border-l border-slate-200 pl-3">
                                <RouterLink v-for="child in item.children" :key="child.to" :to="child.to"
                                    class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-[14px] transition"
                                    :class="isActive(child.to)
                                        ? 'bg-rose-50 text-rose-700'
                                        : 'text-slate-700 hover:bg-slate-50'">
                                    <span class="h-1.5 w-1.5 rounded-full"
                                        :class="isActive(child.to) ? 'bg-red-700' : 'bg-slate-300'" />
                                    <span>{{ child.label }}</span>
                                </RouterLink>
                            </div>
                        </div>

                        <!-- ไม่มี children = ลิงก์ปกติ -->
                        <RouterLink v-else :to="item.to"
                            class="group inline-flex items-center gap-3 rounded-2xl px-3 py-2.5 text-lg font-medium transition"
                            :class="isActive(item.to)
                                ? 'bg-rose-100 text-red-700'
                                : 'text-slate-700 hover:bg-slate-50'">
                            <span class="grid h-[30px] w-[30px] place-items-center rounded-lg"
                                :class="isActive(item.to) ? 'text-red-700' : 'text-slate-700'" v-html="item.icon" />
                            <span class="leading-none">{{ item.label }}</span>
                        </RouterLink>
                    </template>
                </nav>

        <!-- Logout -->
        <div class="mt-6 pb-5">
          <button
            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-700 px-5 py-3 text-lg font-medium text-white shadow-lg transition hover:bg-red-600"
            @click="logout"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3H6A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H6"/>
            </svg>
            <span>Log out</span>
          </button>
        </div>
      </aside>

      <!-- Main -->
      <main class="p-6">
        <header class="mb-4">
          <h1 class="text-4xl text-red-700 font-semibold">{{ pageTitle }}</h1>
        </header>
        <section class="rounded-2xl bg-white p-5 shadow-lg">
          <slot />
        </section>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

defineProps({ pageTitle: { type: String, default: '' } })

const route = useRoute()
const router = useRouter()

const items = ref([
    {
        label: 'Dashboard',
        to: '/',
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10ZM13 3v6h8V3h-8Z"/>
    </svg>`
    },
    {
        label: 'Event',
        to: '/event',
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M7 3v4M17 3v4M3 10h18M5 6h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/>
    </svg>`
    },
    {
        label: 'Employee',
        to: '/employee',
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M16 14a4 4 0 1 1-8 0"/><circle cx="12" cy="7" r="3"/><path d="M4 21a8 8 0 0 1 16 0"/>
    </svg>`
    },
    {
        label: 'Category',
        to: '/categories',
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <circle cx="7.5" cy="7.5" r="2.5"/><circle cx="16.5" cy="7.5" r="2.5"/><path d="M5 15h6v4H5zM13 15h6v4h-6z"/>
    </svg>`
    },
    {
        label: 'History',
        to: '',
        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12 8v5l3 2M12 3a9 9 0 1 1-9 9H1l3.5-3.5L8 12H6a6 6 0 1 0 6-6Z"/>
    </svg>`,
        children: [
            { label: 'Event', to: '/history-event' },
            { label: 'Employee', to: '/history-employee' },
            { label: 'Category', to: '/history-category' }
        ]
    }
])

// เช็ก active: ตรง path หรือเป็น child (/xxx/...)
const isActive = (to) => route.path === to || route.path.startsWith(to + '/')

// สถานะเปิด/ปิดของเมนูที่มี children
const expanded = ref({})

// เปิดอัตโนมัติเมื่ออยู่ใต้ /history/*
const syncExpandedWithRoute = () => {
    items.value.forEach(item => {
        if (item.children) {
            expanded.value[item.to] = route.path.startsWith(item.to)
        }
    })
}

// ให้ทำทันทีตอนโหลด และอัปเดตเมื่อ path เปลี่ยน
watch(() => route.path, syncExpandedWithRoute, { immediate: true })

const toggle = (key) => {
    expanded.value[key] = !expanded.value[key]
}

axios.defaults.withCredentials = true
const token = document.querySelector('meta[name="csrf-token"]')?.content
if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token

const routerPush = (path) => router.push(path) // เผื่อใช้ต่อ
const logout = async () => {
    try {
        await axios.post('/logout')
        router.push('/login')
    } catch (e) {
        console.error('Logout failed', e)
    }
}
</script>
