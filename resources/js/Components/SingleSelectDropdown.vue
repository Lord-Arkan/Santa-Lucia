<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    options: { type: Array, default: () => [] },
    modelValue: { type: [String, Number, Object, null], default: null },
    placeholder: { type: String, default: 'Seleccione' },
    valueKey: { type: String, default: 'id' },
    labelKey: { type: String, default: 'name' },
    clearable: { type: Boolean, default: true },
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

const selectedLabel = computed(() => {
    if (props.modelValue === null || props.modelValue === '') return null;
    const opt = props.options.find((o) => String(o[props.valueKey]) === String(props.modelValue));
    return opt ? opt[props.labelKey] : null;
});

function handleClickOutside(e) {
    if (open.value && container.value && !container.value.contains(e.target)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));

const select = (val) => {
    emit('update:modelValue', val);
    open.value = false;
};

const clear = (e) => {
    e.stopPropagation();
    emit('update:modelValue', null);
};
</script>

<template>
    <div class="relative" ref="container">
        <button
            type="button"
            class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-left text-white outline-none transition flex items-center justify-between"
            @click.prevent="open = !open"
        >
            <div class="truncate">
                <template v-if="selectedLabel">{{ selectedLabel }}</template>
                <template v-else>
                    <span class="text-slate-500">{{ placeholder }}</span>
                </template>
            </div>

            <div class="flex items-center gap-2">
                <button v-if="clearable && selectedLabel" type="button" class="text-xs text-slate-400 hover:text-white" @click="clear($event)">Limpiar</button>
                <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 9l6 6 6-6"></path>
                </svg>
            </div>
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
                <div class="fixed inset-0 z-40 pointer-events-none bg-black/20"></div>

                <div class="relative z-50 rounded-2xl border border-white/10 bg-[#101824] p-3 shadow-lg">
                    <input v-model="search" type="search" placeholder="Buscar..." class="h-10 w-full rounded-2xl border border-white/5 bg-white/5 px-3 text-sm text-white outline-none placeholder:text-slate-500" />

                    <div class="mt-2 max-h-56 overflow-auto">
                        <button v-for="opt in filteredOptions" :key="opt[valueKey]" type="button" class="w-full text-left rounded-md px-2 py-2 hover:bg-white/5" @click="select(opt[valueKey])">
                            <span class="text-sm text-white">{{ opt[labelKey] }}</span>
                        </button>
                        <div v-if="filteredOptions.length === 0" class="text-sm text-slate-500 p-2">No hay resultados</div>
                    </div>

                    <div class="mt-3 flex justify-end">
                        <button type="button" class="rounded-2xl bg-white/5 px-4 py-2 text-sm font-black text-slate-300 hover:bg-white/10" @click="open = false">Cerrar</button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
