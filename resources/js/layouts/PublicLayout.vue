<template>
    <div class="min-h-screen flex flex-col">
        <header class="sticky top-0 z-40 bg-warm-white/90 backdrop-blur border-b border-midnight/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2 font-bold text-midnight text-lg">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-coral text-white font-black">LH</span>
                    <span>Let's Hang</span>
                </a>
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-midnight/80">
                    <router-link to="/login" class="hover:text-coral">Discover People</router-link>
                    <router-link to="/login" class="hover:text-coral">Activities</router-link>
                    <router-link to="/login" class="hover:text-coral">Communities</router-link>
                </nav>
                <div class="flex items-center gap-3">
                    <a href="#" class="hidden sm:inline-block text-sm font-medium text-midnight/80 hover:text-coral">Log in</a>
                    <a href="#" class="inline-block px-4 py-2 rounded-xl bg-coral text-white text-sm font-semibold hover:bg-coral/90 transition">Get started</a>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <router-view />
        </main>

        <footer class="bg-midnight text-warm-white/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid gap-8 md:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2 font-bold text-white text-lg mb-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-coral text-white font-black">LH</span>
                        <span>Let's Hang</span>
                    </div>
                    <p class="text-sm">Find your people. Make plans. Go live.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Product</h4>
                    <ul class="space-y-2 text-sm">
                        <li><router-link to="/login" class="hover:text-coral">Discover People</router-link></li>
                        <li><router-link to="/login" class="hover:text-coral">Activities</router-link></li>
                        <li><router-link to="/login" class="hover:text-coral">Communities</router-link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-coral">About</a></li>
                        <li><a href="#" class="hover:text-coral">Safety</a></li>
                        <li><a href="#" class="hover:text-coral">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-coral">Terms</a></li>
                        <li><a href="#" class="hover:text-coral">Privacy</a></li>
                        <li><a href="#" class="hover:text-coral">Community Guidelines</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 py-4 text-center text-xs text-warm-white/60">
                &copy; {{ new Date().getFullYear() }} Let's Hang. All rights reserved.
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts"></script>
