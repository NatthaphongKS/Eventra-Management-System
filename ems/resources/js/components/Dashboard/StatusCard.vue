<template>
    <div
        class="relative flex flex-col justify-between w-full min-h-[220px] p-4 rounded-[20px] transition-all duration-300 ease-in-out overflow-hidden shadow-lg"
        :class="[themeConfig.bg]"
    >
        <div class="flex items-center gap-1 ml-2 ">
            <div class="flex items-center justify-center w-[32px] h-[32px] text-[#444444]">
                <svg
                    v-if="type === 'accepted'"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="w-[32px] h-[32px]"
                >
                    <path
                        d="m21.1 12.5l1.4 1.41l-6.53 6.59L12.5 17l1.4-1.41l2.07 2.08zM10 17l3 3H3v-2c0-2.21 3.58-4 8-4l1.89.11zm1-13a4 4 0 0 1 4 4a4 4 0 0 1-4 4a4 4 0 0 1-4-4a4 4 0 0 1 4-4"
                    />
                </svg>

                <svg
                    v-else-if="type === 'declined'"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="w-[32px] h-[32px]"
                >
                    <path d="M19.775 22.625L17.15 20H4v-2.8q0-.85.438-1.562T5.6 14.55q1.125-.575 2.288-.925t2.362-.525L1.375 4.225L2.8 2.8l18.4 18.4zM18.4 14.55q.725.35 1.15 1.062T20 17.15l-3.35-3.35q.45.175.888.35t.862.4m-4.2-3.2L8.65 5.8q.575-.85 1.45-1.325T12 4q1.65 0 2.825 1.175T16 8q0 1.025-.475 1.9T14.2 11.35"/>
                </svg>

                <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="w-[32px] h-[32px]"
                >
                    <path d="M19.775 22.625L17.15 20H4v-2.8q0-.85.438-1.562T5.6 14.55q1.125-.575 2.288-.925t2.362-.525L1.375 4.225L2.8 2.8l18.4 18.4zM18.4 14.55q.725.35 1.15 1.062T20 17.15l-3.35-3.35q.45.175.888.35t.862.4m-4.2-3.2L8.65 5.8q.575-.85 1.45-1.325T12 4q1.65 0 2.825 1.175T16 8q0 1.025-.475 1.9T14.2 11.35"/>
                </svg>
            </div>
            <span class="text-[20px] font-semibold text-[#444444] ml-1">{{ title }}</span>
        </div>

        <div class="flex items-center justify-between w-full px-1">
            <div
                class="text-[56px] font-semibold tracking-tight leading-none ml-9"
                :class="themeConfig.textMain"
            >
                {{ percentage }}<span class="font-semibold ml-4">%</span>
            </div>

            <div
                class="relative flex items-center justify-center w-[107px] h-[107px] mr-5"
            >
                <svg
                    class="transform -rotate-90 w-full h-full p-1 drop-shadow-[-4px_0px_1px_rgba(0,0,0,0.15)]"
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

                <span class="absolute text-[28px] font-semibold text-gray-700">{{
                    count
                }}</span>
            </div>
        </div>
        <div class="flex items-center justify-between mt-1 ml-4">
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
                class="px-7 py-2 text-[14px] text-gray-500 bg-white rounded-full shadow-sm hover:shadow-md hover:text-gray-800 transition-all active:scale-95 border border-gray-200"
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
            // อัปเดต validator ให้รองรับ accepted และ declined
            validator: (value) =>
                ["accepted", "declined", "pending"].includes(value),
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
                // เปลี่ยนจาก 'attending' เป็น 'accepted'
                case "accepted":
                    return {
                        bg: "bg-green-100",
                        textMain: "text-[#00A73D]",
                        strokeColor: "#00A73D",
                        dotBg: "bg-[#00A73D]",
                    };
                // เปลี่ยนจาก 'not-attending' เป็น 'declined'
                case "declined":
                    return {
                        bg: "bg-red-100",
                        textMain: "text-[#C10008]",
                        strokeColor: "#C10008",
                        dotBg: "bg-[#C10008]",
                    };
                case "pending":
                    return {
                        bg: "bg-[#DFF3FE]",
                        textMain: "text-[#0084D1]",
                        strokeColor: "#0084D1",
                        dotBg: "bg-[#0084D1]",
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