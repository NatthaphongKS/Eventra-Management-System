<template>
    <div class="field">
        <label>{{ label }} <span v-if="required" class="req">*</span></label>
        <div class="time-row">
            <div class="input with-icon">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6" />
                </svg>
                <input :value="start" @input="onStart($event.target.value)" type="time" :required="required" />
            </div>
            <span class="colon">:</span>
            <div class="input with-icon">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6" />
                </svg>
                <input :value="end" @input="onEnd($event.target.value)" type="time" :required="required" />
            </div>
            <span class="hint">→ {{ durationText }}</span>
        </div>
    </div>
</template>

<script>
export default {
    name: "TimeField",
    props: {
        start: { type: String, default: "" },          // v-model:start
        end: { type: String, default: "" },            // v-model:end
        duration: { type: Number, default: 0 },        // v-model:duration (minutes)
        required: { type: Boolean, default: true },
        label: { type: String, default: "Time" },
    },
    emits: ["update:start", "update:end", "update:duration"],
    computed: {
        durationText() {
            const m = this.duration || 0;
            const h = Math.floor(m / 60), mm = m % 60;
            if (!m) return "Auto fill";
            if (h && mm) return `${h}h ${mm}m`;
            if (h) return `${h}h`;
            return `${mm}m`;
        },
    },
    methods: {
        toMin(t) {
            if (!t) return null;
            const [h, m] = t.split(":").map(Number);
            if (Number.isNaN(h) || Number.isNaN(m)) return null;
            return h * 60 + m;
        },
        recalcDuration(nextStart, nextEnd) {
            const s = this.toMin(nextStart ?? this.start);
            const e = this.toMin(nextEnd ?? this.end);
            let mins = 0;
            if (s != null && e != null) mins = e >= s ? e - s : 1440 - s + e;
            this.$emit("update:duration", mins);
        },
        onStart(v) {
            this.$emit("update:start", v);
            this.recalcDuration(v, null);
        },
        onEnd(v) {
            this.$emit("update:end", v);
            this.recalcDuration(null, v);
        },
    },
};
</script>
