<template>
    <div
        class="relative flex flex-col justify-between w-full min-h-[220px] p-6 rounded-[32px] transition-all duration-300 ease-in-out overflow-hidden shadow-lg"
        :class="[themeConfig.bg]"
    >
        <div class="flex items-center gap-3 mb-2">
            <div class="flex items-center justify-center w-8 h-8 text-gray-800">
                <svg
                    v-if="type === 'attending'"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="w-7 h-7"
                >
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <polyline points="17 11 19 13 23 9"></polyline>
                </svg>

                <svg
                    v-else-if="type === 'not-attending'"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                    class="w-7 h-7"
                >
                    <path
                        d="M18 6L6 18M6 6l12 12"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                <svg
                    v-else
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                    class="w-7 h-7"
                >
                    <circle cx="12" cy="12" r="10"></circle>
                    <path
                        d="M12 6v6l4 2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>
            <span class="text-xl font-medium text-gray-700">{{ title }}</span>
        </div>

        <div class="flex items-center justify-between w-full px-1">
            <div
                class="text-6xl font-bold tracking-tight leading-none ml-7"
                :class="themeConfig.textMain"
            >
                {{ percentage }}<span class="font-semibold ml-4">%</span>
            </div>

            <div
                class="relative flex items-center justify-center w-[90px] h-[90px]"
            >
                <svg
                    class="transform -rotate-90 w-full h-full p-1 drop-shadow-[-3px_0px_2px_rgba(0,0,0,0.15)]"
                    viewBox="0 0 80 80"
                >
                    <circle
                        cx="40"
                        cy="40"
                        r="34"
                        fill="transparent"
                        stroke="#F5F5F5"
                        stroke-width="8"
                    />

                    <circle
                        cx="40"
                        cy="40"
                        r="34"
                        fill="transparent"
                        :stroke="themeConfig.strokeColor"
                        stroke-width="8"
                        stroke-linecap="round"
                        :stroke-dasharray="miniCircumference"
                        :stroke-dashoffset="miniStrokeDashoffset"
                        class="transition-all duration-1000 ease-out"
                    />
                </svg>

                <span class="absolute text-xl font-bold text-gray-700">{{
                    count
                }}</span>
            </div>
        </div>
        <div class="flex items-center justify-between mt-4">
            <div
                class="flex items-center gap-2 text-sm text-gray-600 font-medium"
            >
                <span
                    class="w-3 h-3 rounded-full"
                    :class="themeConfig.dotBg"
                ></span>
                <span>
                    <b class="text-gray-800">{{ count }}</b> Person from
                    <b class="text-gray-800">{{ total }}</b>
                </span>
            </div>

            <button
                class="px-5 py-2 text-sm font-semibold text-gray-600 bg-white rounded-full shadow-sm hover:shadow-md hover:text-gray-800 transition-all active:scale-95"
                :class="[{ 'cursor-pointer': isClickable }]"
                @click="handleClick"
            >
                View {{ title }}
            </button>
        </div>

        <div
            v-if="loading"
            class="absolute inset-0 bg-white/50 flex items-center justify-center z-10 backdrop-blur-sm rounded-[32px]"
        >
            <span class="text-sm text-gray-500 font-medium animate-pulse"
                >Loading...</span
            >
        </div>
    </div>
</template>

<script>
export default {
    name: "StatusCard",
    props: {
        type: {
            type: String,
            required: true,
            validator: (value) =>
                ["attending", "not-attending", "pending"].includes(value),
        },
        title: {
            type: String,
            default: "",
        },
        count: {
            type: Number,
            default: 0,
        },
        total: {
            type: Number,
            default: 0,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        isClickable: {
            type: Boolean,
            default: false,
        },
    },
    emits: ["click"],
    computed: {
        percentage() {
            if (this.total === 0) return 0;
            return Math.round((this.count / this.total) * 100);
        },
        miniCircumference() {
            return 2 * Math.PI * 34; // r=34
        },
        miniStrokeDashoffset() {
            if (this.total === 0) return this.miniCircumference;
            const pct = this.count / this.total;
            return this.miniCircumference * (1 - pct);
        },
        themeConfig() {
            switch (this.type) {
                case "attending":
                    return {
                        bg: "bg-green-100", // Tailwind color: bg-green-100 (ใกล้เคียง pastel green)
                        textMain: "text-green-500", // สีตัวเลข %
                        strokeColor: "#22c55e", // สีเส้นกราฟ (Green 500)
                        dotBg: "bg-green-500", // สีจุดหน้า text
                    };
                case "not-attending":
                    return {
                        bg: "bg-red-100",
                        textMain: "text-red-500",
                        strokeColor: "#ef4444",
                        dotBg: "bg-red-500",
                    };
                case "pending":
                    return {
                        bg: "bg-blue-100",
                        textMain: "text-blue-500",
                        strokeColor: "#3b82f6",
                        dotBg: "bg-blue-500",
                    };
                default:
                    return {
                        bg: "bg-gray-100",
                        textMain: "text-gray-500",
                        strokeColor: "#6b7280",
                        dotBg: "bg-gray-500",
                    };
            }
        },
    },
    methods: {
        handleClick() {
            if (this.isClickable) {
                this.$emit("click", this.type);
            }
        },
    },
};
</script>
