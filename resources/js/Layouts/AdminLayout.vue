<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <Link href="/" class="text-xl font-bold text-gray-800">
                                Bakery Admin
                            </Link>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <Link
                                href="/admin/store-hours"
                                class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Store Hours
                            </Link>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="text-gray-500 hover:text-gray-700"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script> 