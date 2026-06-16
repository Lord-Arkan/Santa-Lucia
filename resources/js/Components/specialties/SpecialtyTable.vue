<script setup>
const props = defineProps({
    specialties: { type: Array, required: true },
});

const emit = defineEmits(['edit', 'delete', 'toggle']);
</script>

<template>
    <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10">
        <div class="border-b border-white/10 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">Especialidades</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[760px] w-full text-left">
                <thead class="bg-slate-950/30 text-xs uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-black">Especialidad</th>
                        <th class="px-5 py-4 font-black">Estado</th>
                        <th class="px-5 py-4 font-black">Creacion</th>
                        <th class="px-5 py-4 font-black">Actualizacion</th>
                        <th class="px-5 py-4 text-right font-black">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr v-for="specialty in props.specialties" :key="specialty.specialty_id" class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span :class="['grid size-11 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-sm font-black', specialty.status === 'inactivo' ? 'text-rose-200' : 'text-white']">
                                    {{ specialty.name.slice(0, 2).toUpperCase() }}
                                </span>
                                <span :class="['text-sm font-black', specialty.status === 'inactivo' ? 'text-rose-300' : 'text-white']">{{ specialty.name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['rounded-full px-3 py-1.5 text-xs font-black', specialty.status === 'activo' ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200']">
                                {{ specialty.status === 'activo' ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-400">{{ specialty.created_at }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-400">{{ specialty.updated_at }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    :class="[
                                        'rounded-xl border px-3 py-2 text-xs font-black transition',
                                        specialty.status === 'activo'
                                            ? 'border-amber-300/20 text-amber-200 hover:bg-amber-400/10'
                                            : 'border-emerald-300/20 text-emerald-200 hover:bg-emerald-400/10'
                                    ]"
                                    @click="$emit('toggle', specialty)"
                                >
                                    {{ specialty.status === 'activo' ? 'Inhabilitar' : 'Habilitar' }}
                                </button>
                                <button type="button" class="rounded-xl border border-white/10 px-3 py-2 text-xs font-black text-slate-300 transition hover:bg-white/10 hover:text-white" @click="$emit('edit', specialty)">Editar</button>
                                <button type="button" class="rounded-xl border border-rose-300/20 px-3 py-2 text-xs font-black text-rose-200 transition hover:bg-rose-400/10" @click="$emit('delete', specialty)">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!props.specialties.length">
                        <td colspan="5" class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No hay especialidades registradas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
