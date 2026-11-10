<template>
    <section class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 lg:pt-24 lg:pb-28">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal/10 text-teal text-sm font-medium mb-6">
                        <span class="h-2 w-2 rounded-full bg-teal"></span>
                        Now in Nairobi
                    </span>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-midnight leading-tight">
                        Find your people.<br />
                        <span class="text-coral">Make plans.</span><br />
                        Go live.
                    </h1>
                    <p class="mt-6 text-lg text-midnight/70 max-w-xl">
                        Let's Hang helps you discover meaningful friendships, join communities, and take part in real-life
                        activities around your city. Friendship first, experiences second.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="#" class="px-6 py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition shadow-lg shadow-coral/20">
                            Create free account
                        </a>
                        <a href="#" class="px-6 py-3 rounded-xl border-2 border-midnight/20 text-midnight font-semibold hover:border-coral hover:text-coral transition">
                            Explore activities
                        </a>
                    </div>
                    <div class="mt-10 flex items-center gap-6 text-sm text-midnight/60">
                        <div class="flex -space-x-2">
                            <div class="h-8 w-8 rounded-full bg-sunshine border-2 border-warm-white"></div>
                            <div class="h-8 w-8 rounded-full bg-teal border-2 border-warm-white"></div>
                            <div class="h-8 w-8 rounded-full bg-coral border-2 border-warm-white"></div>
                        </div>
                        <span>Trusted by early members in Nairobi</span>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-tr from-coral/20 via-sunshine/20 to-teal/20 rounded-3xl blur-2xl"></div>
                    <div class="relative bg-white rounded-3xl shadow-xl p-6 border border-midnight/5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-10 w-10 rounded-full bg-teal/20"></div>
                            <div>
                                <p class="font-semibold text-midnight">Weekend Hikers Kenya</p>
                                <p class="text-xs text-midnight/50">Community activity &middot; Sat, 9:00 AM</p>
                            </div>
                        </div>
                        <div class="aspect-[4/3] rounded-2xl bg-gradient-to-br from-teal to-sunshine flex items-center justify-center text-white font-bold text-2xl">
                            Ngong Hills Hike
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-midnight/60">24 going &middot; 6 spots left</div>
                            <button class="px-4 py-2 rounded-xl bg-coral text-white text-sm font-semibold">Join</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="bg-white border-y border-midnight/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="text-3xl font-black text-midnight">Three ways to belong</h2>
                    <p class="mt-3 text-midnight/60">Everything you need to build real connections.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="p-6 rounded-2xl bg-warm-white border border-midnight/5 hover:shadow-lg transition">
                        <div class="h-12 w-12 rounded-xl bg-coral/10 text-coral flex items-center justify-center text-2xl mb-4">👥</div>
                        <h3 class="font-bold text-midnight text-lg">People Discovery</h3>
                        <p class="mt-2 text-sm text-midnight/60">Find like-minded people by interests, city, and intention. Friendship first, always.</p>
                    </div>
                    <div class="p-6 rounded-2xl bg-warm-white border border-midnight/5 hover:shadow-lg transition">
                        <div class="h-12 w-12 rounded-xl bg-teal/10 text-teal flex items-center justify-center text-2xl mb-4">🎯</div>
                        <h3 class="font-bold text-midnight text-lg">Activities &amp; Meetups</h3>
                        <p class="mt-2 text-sm text-midnight/60">Coffee, football, hikes, board games. Create or join real-world plans near you.</p>
                    </div>
                    <div class="p-6 rounded-2xl bg-warm-white border border-midnight/5 hover:shadow-lg transition">
                        <div class="h-12 w-12 rounded-xl bg-sunshine/20 text-midnight flex items-center justify-center text-2xl mb-4">💬</div>
                        <h3 class="font-bold text-midnight text-lg">Communities</h3>
                        <p class="mt-2 text-sm text-midnight/60">Build and join groups around shared passions with posts, discussions, and events.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="rounded-3xl bg-midnight text-warm-white p-8 lg:p-12 flex flex-col lg:flex-row items-center justify-between gap-6">
                <div>
                    <h2 class="text-3xl font-black">Ready to hang?</h2>
                    <p class="mt-2 text-warm-white/70">Join Let's Hang and make your first plan today.</p>
                </div>
                <a href="#" class="px-6 py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Get started for free</a>
            </div>
        </section>
    </section>
</template>

<script setup lang="ts"></script>
