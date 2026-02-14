<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- PAGE WRAPPER --}}
    <div class="relative min-h-screen w-full overflow-x-hidden font-[Inter]">

        {{-- ✅ FULLSCREEN BACKGROUND (IMAGE + GRADIENT OVERLAY) --}}
        <div class="fixed inset-0 -z-10">
            {{-- Image full cover --}}
            <img
            src="{{ asset('images/company.jpeg') }}"
            alt="Company"
            class="absolute inset-0 h-full w-full object-cover"
            />


            {{-- Dark overlay biar text kebaca --}}
            <div class="absolute inset-0 bg-black/45"></div>

            {{-- ✅ keep your gradient vibes --}}
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,#1d4ed8_0%,transparent_55%)] opacity-50"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom,#0ea5e9_0%,transparent_55%)] opacity-25"></div>
            <div class="absolute inset-0 bg-linear-to-br from-slate-950/60 via-slate-950/40 to-slate-900/60"></div>
        </div>

        {{-- CONTENT --}}
        <div class="min-h-screen flex items-center justify-center px-4 py-10">
            <div class="w-full max-w-5xl">
                <div class="grid grid-cols-1 md:grid-cols-2 overflow-hidden rounded-3xl shadow-2xl ring-1 ring-white/10 bg-white/5 backdrop-blur">

                    {{-- LEFT: FORM --}}
                    <div class="bg-white p-8 sm:p-10 md:p-12">
                        <div class="max-w-md">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-2xl overflow-hidden ring-1 ring-white/20 shadow">
                                    <img src="/images/logo-pt-rap.jpeg" class="h-full w-full object-cover" alt="Logo">
                                </div>
                                <div class="text-sm text-slate-500">Monitoring System</div>
                            </div>

                            <h3 class="mt-6 text-2xl font-semibold text-slate-900">PT. Rajapaksi</h3>
                            <p class="mt-1 text-slate-500">Please enter your details.</p>

                            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
                                @csrf
                                
                                @if ($errors->any())
                                <div class="mb-3 rounded-lg bg-red-50 p-3 text-sm text-red-600">
                                    {{ $errors->first() }}
                                </div>
                                @endif

                                <div>
                                    <label class="sr-only" for="email">Email</label>
                                    <input
                                    id="login_id"
                                    name="login_id"
                                    type="text"
                                    required
                                    autocomplete="username"
                                    placeholder="Username"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    />
                                </div>

                                <div>
                                    <label class="sr-only" for="password">Password</label>
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Password"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    />
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <label class="flex items-center gap-2 text-sm text-slate-600">
                                        <input
                                            type="checkbox"
                                            name="remember"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-200"
                                        />
                                        Remember for 30 days
                                    </label>


                                </div>

                                <button
                                    type="submit"
                                    class="mt-2 w-full rounded-full bg-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 active:scale-[0.99] focus:outline-none focus:ring-4 focus:ring-blue-200"
                                >
                                    Login
                                </button>

                                <p class="pt-4 text-xs text-slate-400">
                                    By continuing, you agree to the monitoring system policies.
                                </p>
                            </form>
                        </div>
                    </div>

                    {{-- RIGHT: VISUAL (keep your gradient card) --}}
                    <div class="relative hidden md:flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 bg-linear-to-br from-blue-700 via-indigo-700 to-slate-900"></div>
                        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>

                        <div class="absolute inset-0 opacity-30">
                            <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-white/15 blur-2xl"></div>
                            <div class="absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>
                        </div>

                        <div class="relative z-10 px-10 text-center text-white">
                            <h2 class="text-2xl font-semibold">Welcome to</h2>
                            <h1 class="mt-2 text-4xl font-bold leading-tight">
                                Production Monitoring System
                            </h1>
                            <p class="mt-4 text-white/75">
                                Real-time insight for monitoring production.
                            </p>

                            <div class="mt-10 grid grid-cols-3 gap-4 text-left">
                                <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                                    <div class="text-xs text-white/70">Status</div>
                                    <div class="mt-1 font-semibold">Live</div>
                                </div>
                                <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                                    <div class="text-xs text-white/70">Updates</div>
                                    <div class="mt-1 font-semibold">Real-time</div>
                                </div>
                                <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                                    <div class="text-xs text-white/70">Scope</div>
                                    <div class="mt-1 font-semibold">Production</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-6 text-center text-xs text-white/50">
                    © {{ date('Y') }} PT. Rajapaksi
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
