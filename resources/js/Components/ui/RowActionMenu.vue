<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    actions: {
        type: Array,
        default: () => [],
    },
    align: {
        type: String,
        default: 'right',
    },
});

const emit = defineEmits(['select']);
const open = ref(false);
const trigger = ref(null);
const menuPosition = ref({ top: 0, left: 0 });

const visibleActions = computed(() => props.actions.filter((action) => action.visible !== false));
const alignmentClass = computed(() => (props.align === 'left' ? 'origin-top-left' : 'origin-top-right'));

const close = () => {
    open.value = false;
};

const setMenuPosition = () => {
    const rect = trigger.value?.getBoundingClientRect();
    if (!rect) return;

    const width = 192;
    const margin = 8;
    const left = props.align === 'left' ? rect.left : rect.right - width;

    menuPosition.value = {
        top: rect.bottom + 6,
        left: Math.max(margin, Math.min(left, window.innerWidth - width - margin)),
    };
};

const toggle = async () => {
    open.value = !open.value;

    if (open.value) {
        await nextTick();
        setMenuPosition();
    }
};

const choose = (action) => {
    if (action.disabled) return;
    emit('select', action);
    close();
};

const closeOnEscape = (event) => {
    if (event.key === 'Escape') close();
};

const toneClass = (tone) => ({
    danger: 'text-rose-200 hover:bg-rose-400/10',
    warning: 'text-amber-200 hover:bg-amber-400/10',
    success: 'text-emerald-200 hover:bg-emerald-400/10',
    info: 'text-sky-200 hover:bg-sky-400/10',
    violet: 'text-violet-200 hover:bg-violet-400/10',
}[tone] ?? 'text-slate-300 hover:bg-white/10 hover:text-white');

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    window.addEventListener('resize', close);
    window.addEventListener('scroll', close, true);
});
onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    window.removeEventListener('resize', close);
    window.removeEventListener('scroll', close, true);
});
</script>

<template>
    <div class="relative inline-flex items-center justify-end">
        <button
            ref="trigger"
            type="button"
            class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-white/10 hover:text-white"
            aria-label="Abrir acciones"
            :aria-expanded="open"
            @click.stop="toggle"
        >
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2" aria-hidden="true">
                <path d="M5 7h14M5 12h14M5 17h14" />
            </svg>
        </button>

        <div v-show="open" class="fixed inset-0 z-40" @click="close" />

        <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="-translate-y-1 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-1 opacity-0"
        >
            <div
                v-show="open"
                :class="['fixed z-50 w-48 rounded-xl border border-white/10 bg-[#1d2735] p-1.5 text-left shadow-2xl shadow-black/40', alignmentClass]"
                :style="{ top: `${menuPosition.top}px`, left: `${menuPosition.left}px` }"
                @click.stop
            >
                <template v-for="action in visibleActions" :key="action.key || action.label">
                    <Link
                        v-if="action.href"
                        :href="action.href"
                        :class="['flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold transition', toneClass(action.tone), action.disabled ? 'pointer-events-none opacity-40' : '']"
                        @click="close"
                    >
                        <span class="size-1.5 rounded-full bg-current opacity-70" />
                        {{ action.label }}
                    </Link>
                    <button
                        v-else
                        type="button"
                        :disabled="action.disabled"
                        :class="['flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-40', toneClass(action.tone)]"
                        @click="choose(action)"
                    >
                        <span class="size-1.5 rounded-full bg-current opacity-70" />
                        {{ action.label }}
                    </button>
                </template>

                <p v-if="!visibleActions.length" class="px-3 py-2 text-xs font-semibold text-slate-500">Sin acciones</p>
            </div>
        </transition>
    </div>
</template>
