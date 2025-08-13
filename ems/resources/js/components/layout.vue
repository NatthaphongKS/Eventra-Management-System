<template>
    <div class="app">
        <!-- Sidebar -->
        <aside :class="['sidebar', { open: sidebarOpen }]">
            <div class="brand">
                <div class="logo">e</div>
                <div class="title">ventra</div>
                <button
                    class="toggle"
                    @click="sidebarOpen = !sidebarOpen"
                    aria-label="Toggle sidebar"
                >
                    ☰
                </button>
            </div>

            <nav class="nav">
                <RouterLink
                    v-for="item in items"
                    :key="item.to"
                    :to="item.to"
                    class="nav-item"
                    :class="{ active: $route.path.startsWith(item.to) }"
                >
                    <span class="icon" v-html="item.icon"></span>
                    <span class="label">{{ item.label }}</span>
                </RouterLink>
            </nav>

            <div class="spacer"></div>

            <button class="logout" @click="logout">⤴ Log out</button>
        </aside>

        <!-- Main -->
        <main class="main">
            <header class="page-head">
                <h1 class="page-title">{{ pageTitle }}</h1>
            </header>

            <section class="page-body">
                <!-- เนื้อหาของแต่ละหน้า -->
                <slot />
            </section>
        </main>
    </div>
</template>

<script>
import axios from "axios";

// ตั้งค่าพื้นฐานให้ axios (ถ้าโปรเจ็กต์อยู่ root ของโดเมน ใช้แบบนี้โอเค)

export default {
    name: "Layout",
    props: {
        pageTitle: { type: String, default: "" },
    },
    data() {
        return {
            sidebarOpen: true,
            items: [
                {
                    label: "Dashboard",
                    to: "/",
                    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8v-10h-8v10zm0-18v6h8V3h-8z" stroke="currentColor" stroke-width="1.6"/>
            </svg>`,
                },
                {
                    label: "Event",
                    to: "/event",
                    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M7 3v4M17 3v4M3 10h18M5 6h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>`,
                },
                {
                    label: "Employee",
                    to: "/employee",
                    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M16 14a4 4 0 1 1-8 0" stroke="currentColor" stroke-width="1.6" />
              <circle cx="12" cy="7" r="3" stroke="currentColor" stroke-width="1.6"/>
              <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.6" />
            </svg>`,
                },
                {
                    label: "Deleted",
                    to: "/deleted",
                    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>`,
                },
            ],
        };
    },
    created() {
        // ให้ส่งคุกกี้ session และแนบ CSRF
        axios.defaults.withCredentials = true;
        const token = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;
        if (token) axios.defaults.headers.common["X-CSRF-TOKEN"] = token;
    },
    methods: {
        async logout() {
            try {
                // โพสต์ไป /logout ฝั่ง web โดย “ไม่” ให้ baseURL ชี้ไป /api
                await axios.post("/logout");
                this.$router.push("/login");
            } catch (e) {
                console.error("Logout failed", e);
                // this.$router.push("/login");
            }
        },
    },
};
</script>

<style scoped>
:root {
    --sidebar-bg: #fff5f5;
    --sidebar-border: #f1d0d0;
    --sidebar-active: #e53e3e;
    --sidebar-hover: #ffe2e2;
    --text: #2a2a2a;
    --muted: #7a7a7a;
    --card: #ffffff;
    --main-bg: #fffafa;
}

.app {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
    background: var(--main-bg);
}

/* Sidebar */
.sidebar {
    position: sticky;
    top: 0;
    height: 100vh;
    background: var(--sidebar-bg);
    border-right: 1px solid var(--sidebar-border);
    display: flex;
    flex-direction: column;
    padding: 16px 12px;
    gap: 12px;
}

.brand {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 10px;
    padding: 8px 6px 16px;
}

.logo {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: #e53e3e;
    color: white;
    display: grid;
    place-items: center;
    font-weight: 800;
    font-size: 18px;
}
.title {
    font-weight: 700;
    color: var(--text);
    letter-spacing: 0.5px;
}
.toggle {
    border: 0;
    background: transparent;
    font-size: 18px;
    cursor: pointer;
    color: var(--muted);
}

.nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.nav-item {
    display: grid;
    grid-template-columns: 22px 1fr;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    color: var(--text);
    border-radius: 10px;
    text-decoration: none;
}
.nav-item:hover {
    background: var(--sidebar-hover);
}
.nav-item.active {
    background: #fff;
    border: 1px solid var(--sidebar-border);
    color: var(--sidebar-active);
    font-weight: 600;
}

.icon {
    display: grid;
    place-items: center;
    color: currentColor;
}
.label {
    line-height: 1;
}

.spacer {
    flex: 1;
}
.logout {
    width: 100%;
    padding: 10px 12px;
    border-radius: 12px;
    background: #e53e3e;
    color: white;
    border: 0;
    cursor: pointer;
    font-weight: 600;
}

/* Main */
.main {
    padding: 24px;
}
.page-head {
    background: linear-gradient(
        180deg,
        #ffe9e9 0%,
        rgba(255, 233, 233, 0) 100%
    );
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
}
.page-title {
    margin: 0;
    font-size: 28px;
    color: var(--text);
}

.page-body {
    background: var(--card);
    border: 1px solid var(--sidebar-border);
    border-radius: 16px;
    padding: 16px;
}

/* Responsive (ซ่อน/โชว์ sidebar) */
@media (max-width: 980px) {
    .app {
        grid-template-columns: 72px 1fr;
    }
    .title,
    .label {
        display: none;
    }
    .sidebar {
        padding: 12px 8px;
    }
}
</style>
