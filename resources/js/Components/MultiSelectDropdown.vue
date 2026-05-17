<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    options: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Seleccionar' },
    valueKey: { type: String, default: 'id' },
    labelKey: { type: String, default: 'name' },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const search = ref('');
const container = ref(null);

const filteredOptions = computed(() => {
    const q = (search.value || '').toString().trim().toLowerCase();
    if (!q) return props.options;
    return props.options.filter((o) => (o[props.labelKey] || '').toString().toLowerCase().includes(q));
});

const isSelected = (id) => {
    return Array.isArray(props.modelValue) && props.modelValue.indexOf(id) !== -1;
};

const toggle = (id) => {
    const current = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
    const idx = current.indexOf(id);
    if (idx === -1) current.push(id); else current.splice(idx, 1);
    emit('update:modelValue', current);
};

const selectedLabels = computed(() => {
    return (Array.isArray(props.modelValue) ? props.modelValue : []).map((id) => {
        const opt = props.options.find((o) => String(o[props.valueKey]) === String(id));
        return opt ? opt[props.labelKey] : id;
    });
});

function handleClickOutside(e) {
    if (open.value && container.value && !container.value.contains(e.target)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));
</script>

<template>
    <div class="relative" ref="container">
        <button
            type="button"
            class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-sm font-semibold text-white outline-none transition flex items-center justify-between"
            @click.prevent="open = !open"
        >
            <div class="truncate text-left">
                <template v-if="selectedLabels.length">
                    <span v-if="selectedLabels.length <= 3">{{ selectedLabels.join(', ') }}</span>
                    <span v-else>{{ selectedLabels.slice(0,3).join(', ') }} +{{ selectedLabels.length - 3 }}</span>
                </template>
                <template v-else>
                    <span class="text-slate-500">{{ placeholder }}</span>
                </template>
            </div>

            <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 9l6 6 6-6"></path>
            </svg>
        </button>

        <transition
            enter-active-class="transition ease-out duration-200 transform"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150 transform"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-show="open" class="absolute z-50 mt-2 w-full rounded-2xl">
                <!-- visual backdrop but let clicks pass through so the trigger can toggle -->
                <div class="fixed inset-0 z-40 pointer-events-none bg-black/20"></div>

                <div class="relative z-50 rounded-2xl border border-white/10 bg-[#101824] p-3 shadow-lg">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Buscar..."
                        class="h-10 w-full rounded-2xl border border-white/5 bg-white/5 px-3 text-sm text-white outline-none placeholder:text-slate-500"
                    />

                    <div class="mt-2 max-h-56 overflow-auto">
                        <label v-for="opt in filteredOptions" :key="opt[valueKey]" class="flex items-center gap-3 rounded-md px-2 py-2 hover:bg-white/5 cursor-pointer">
                            <input
                                type="checkbox"
                                :checked="isSelected(opt[valueKey])"
                                @change.prevent="toggle(opt[valueKey])"
                                class="h-4 w-4 rounded border-white/10 bg-[#0b1620] text-teal-400"
                            />
                            <span class="text-sm">{{ opt[labelKey] }}</span>
                        </label>
                    </div>

                    <div class="mt-3 flex justify-end">
                        <button type="button" class="rounded-2xl bg-white/5 px-4 py-2 text-sm font-black text-slate-300 hover:bg-white/10" @click="open = false">Hecho</button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
