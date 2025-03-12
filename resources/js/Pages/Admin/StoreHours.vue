<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="border-4 border-dashed border-gray-200 rounded-lg p-4">
                    <div class="mb-4 flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">Store Hours Configuration</h1>
                        <button
                            @click="saveChanges"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            :disabled="isSaving"
                        >
                            {{ isSaving ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>

                    <!-- Error Message -->
                    <div v-if="error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ error }}
                    </div>

                    <!-- Loading State -->
                    <div v-if="!configs.length && !error" class="text-center py-4">
                        Loading store hours configuration...
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="config in configs" :key="config.id" class="bg-white shadow rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ config.day_of_week }}</h3>
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input
                                                type="checkbox"
                                                v-model="config.is_open"
                                                class="form-checkbox h-5 w-5 text-blue-600"
                                            >
                                            <span class="ml-2 text-gray-700">Open</span>
                                        </label>
                                    </div>
                                    <div class="mt-2" v-if="config.day_of_week === 'Saturday'">
                                        <label class="inline-flex items-center">
                                            <input
                                                type="checkbox"
                                                v-model="config.alternate_weeks_only"
                                                class="form-checkbox h-5 w-5 text-blue-600"
                                            >
                                            <span class="ml-2 text-gray-700">Alternate weeks only</span>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="config.is_open">
                                    <label class="block text-sm font-medium text-gray-700">Opening Hours</label>
                                    <div class="mt-1 flex space-x-2">
                                        <div>
                                            <label class="block text-xs text-gray-500">Opens</label>
                                            <input
                                                type="time"
                                                v-model="config.opening_time"
                                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500">Closes</label>
                                            <input
                                                type="time"
                                                v-model="config.closing_time"
                                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div v-if="config.is_open && !config.alternate_weeks_only">
                                    <label class="block text-sm font-medium text-gray-700">Lunch Break</label>
                                    <div class="mt-1 flex space-x-2">
                                        <div>
                                            <label class="block text-xs text-gray-500">Starts</label>
                                            <input
                                                type="time"
                                                v-model="config.lunch_break_start"
                                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500">Ends</label>
                                            <input
                                                type="time"
                                                v-model="config.lunch_break_end"
                                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { usePage } from '@inertiajs/vue3'

const configs = ref([])
const isSaving = ref(false)
const error = ref(null)
const page = usePage()

// Add CSRF token to all requests
axios.defaults.headers.common['X-CSRF-TOKEN'] = page.props.csrf_token

const fetchConfigs = async () => {
    error.value = null
    try {
        const response = await axios.get('/api/admin/store-hours')
        configs.value = response.data
        console.log('Fetched configs:', configs.value) // Debug log
    } catch (err) {
        console.error('Error fetching store hours:', err)
        error.value = 'Failed to load store hours configuration. Please refresh the page.'
    }
}

const saveChanges = async () => {
    isSaving.value = true
    error.value = null
    try {
        await axios.post('/api/admin/store-hours/bulk-update', {
            configs: configs.value
        })
        alert('Store hours updated successfully!')
    } catch (err) {
        console.error('Error updating store hours:', err)
        error.value = 'Failed to update store hours. Please try again.'
    } finally {
        isSaving.value = false
    }
}

onMounted(() => {
    fetchConfigs()
})
</script> 