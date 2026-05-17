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

                    <img
                        src="/storage/img/pngwing.com.png"
                        alt="Ilustración clínica"
                        class="absolute bottom-20 right-24 h-[430px] w-[320px] object-contain"
                        loading="eager"
                        decoding="async"
                    />

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
