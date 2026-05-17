<template>
    <main class="relative min-h-screen overflow-hidden bg-[#1b2a34] px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
        <div class="absolute inset-x-0 top-0 h-[62%] bg-gradient-to-br from-cyan-300 via-teal-400 to-teal-700" />
        <div class="absolute bottom-0 left-0 h-[46%] w-full bg-gradient-to-tr from-blue-700 via-teal-700 to-cyan-400" />
        <div class="absolute left-[-8rem] top-[-8rem] h-80 w-80 rounded-full bg-white/20 blur-3xl" />
        <div class="absolute bottom-[-10rem] right-[-8rem] h-96 w-96 rounded-full bg-cyan-200/25 blur-3xl" />

        <section class="relative mx-auto flex min-h-[calc(100vh-3rem)] max-w-6xl items-center justify-center">
            <div class="relative grid w-full overflow-hidden rounded-[2rem] border border-white/45 bg-white shadow-2xl shadow-teal-950/30 lg:min-h-[690px] lg:grid-cols-[0.85fr_1.15fr]">
                <div class="relative z-10 flex flex-col px-6 py-7 sm:px-10 lg:px-12">
                    <div class="mb-8 flex items-center gap-3">
                        <div class="grid size-12 place-items-center rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-500 shadow-lg shadow-teal-600/20">
                            <svg viewBox="0 0 48 48" class="size-8 text-white" aria-hidden="true">
                                <path fill="currentColor" d="M18 6h12v12h12v12H30v12H18V30H6V18h12z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-teal-600">Clinica</p>
                            <p class="text-2xl font-black leading-none text-slate-900">Santa Lucia</p>
                        </div>
                    </div>

                    <div class="flex flex-1 items-center">
                        <slot />
                    </div>
                </div>

                <aside class="relative hidden overflow-hidden bg-gradient-to-br from-cyan-50 via-white to-teal-50 lg:block" aria-label="Atencion medica Santa Lucia">
                    <div class="absolute -right-24 top-0 h-[115%] w-[72%] skew-x-[-18deg] bg-gradient-to-b from-teal-500 via-cyan-500 to-blue-700" />
                    <div class="absolute right-12 top-10 rounded-full bg-amber-400 px-4 py-2 text-xs font-bold text-slate-800 shadow-xl shadow-amber-500/20">
                        Espanol
                    </div>

                    <div class="absolute left-14 top-16 max-w-xs">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-600">Gestion clinica</p>
                        <h1 class="mt-3 text-5xl font-black leading-[0.95] text-slate-900">
                            Cuidamos cada cita con precision.
                        </h1>
                        <p class="mt-5 text-sm leading-6 text-slate-500">
                            Un acceso seguro para administrar pacientes, horarios y atencion diaria.
                        </p>
                    </div>

                    <div class="absolute bottom-20 right-24 h-[430px] w-[320px]">
                        <div class="absolute left-1/2 top-20 h-64 w-64 -translate-x-1/2 rounded-[42%] bg-gradient-to-br from-amber-300 to-orange-400 shadow-2xl shadow-orange-400/25" />
                        <div class="absolute left-1/2 top-0 h-28 w-28 -translate-x-1/2 rounded-full bg-[#f3c6a8] shadow-lg" />
                        <div class="absolute left-[112px] top-[48px] h-5 w-5 rounded-full bg-slate-800" />
                        <div class="absolute right-[112px] top-[48px] h-5 w-5 rounded-full bg-slate-800" />
                        <div class="absolute left-1/2 top-[78px] h-3 w-10 -translate-x-1/2 rounded-b-full border-b-4 border-rose-500" />
                        <div class="absolute left-[82px] top-[12px] h-28 w-40 rounded-t-full bg-slate-800" />
                        <div class="absolute left-[52px] top-[108px] h-72 w-56 rounded-t-[5rem] bg-white shadow-2xl shadow-teal-950/20" />
                        <div class="absolute left-[88px] top-[142px] h-40 w-6 rounded-full bg-teal-500" />
                        <div class="absolute right-[88px] top-[142px] h-40 w-6 rounded-full bg-teal-500" />
                        <div class="absolute left-[113px] top-[126px] h-56 w-24 rounded-b-[3rem] bg-slate-50" />
                        <div class="absolute left-[72px] top-[155px] h-28 w-16 rotate-12 rounded-full bg-[#f3c6a8]" />
                        <div class="absolute right-[72px] top-[155px] h-28 w-16 -rotate-12 rounded-full bg-[#f3c6a8]" />
                        <div class="absolute left-[86px] top-[232px] h-12 w-12 rounded-full border-[10px] border-teal-600 bg-white" />
                        <div class="absolute left-[111px] top-[178px] h-20 w-2 rounded-full bg-teal-700" />
                        <div class="absolute left-[120px] top-[252px] h-2 w-24 rounded-full bg-teal-700" />
                    </div>

                    <div class="absolute bottom-24 left-12 rounded-2xl border border-white/80 bg-white/90 px-4 py-3 shadow-xl shadow-teal-900/10 backdrop-blur">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-xl bg-cyan-100 text-teal-700">
                                <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true">
                                    <path d="M4 5a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3h-3.5L9 20v-4H7a3 3 0 0 1-3-3V5Zm5 3v2h2v2h2v-2h2V8h-2V6h-2v2H9Z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Conecta con tu equipo</p>
                                <p class="text-xs text-slate-500">Citas y pacientes en un solo lugar</p>
                            </div>
                        </div>
                    </div>

                    <div class="absolute right-10 top-64 rounded-2xl border border-white/70 bg-white/90 px-4 py-3 shadow-xl shadow-blue-900/10 backdrop-blur">
                        <p class="text-sm font-black text-slate-800">50k+ atenciones</p>
                        <div class="mt-2 flex -space-x-2">
                            <span class="size-7 rounded-full border-2 border-white bg-teal-500" />
                            <span class="size-7 rounded-full border-2 border-white bg-cyan-400" />
                            <span class="size-7 rounded-full border-2 border-white bg-amber-400" />
                            <span class="grid size-7 place-items-center rounded-full border-2 border-white bg-blue-600 text-xs font-bold text-white">+</span>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </main>
</template>
