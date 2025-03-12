<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="border-4 border-dashed border-gray-200 rounded-lg p-4">
                    <div class="mb-4 flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">Store Hours Configuration</h1>
                        <button
                            @click="saveChanges"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            :disabled="isSaving"
                        >
                            {{ isSaving ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>

                    <div class="space-y-4">
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

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Opening Hours</label>
                                    <div class="mt-1 flex space-x-2">
                                        <input
                                            type="time"
                                            v-model="config.opening_time"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        >
                                        <input
                                            type="time"
                                            v-model="config.closing_time"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        >
                                    </div>
                                </div>

                                <div v-if="config.is_open && !config.alternate_weeks_only">
                                    <label class="block text-sm font-medium text-gray-700">Lunch Break</label>
                                    <div class="mt-1 flex space-x-2">
                                        <input
                                            type="time"
                                            v-model="config.lunch_break_start"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        >
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
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const configs = ref([])
const isSaving = ref(false)

const fetchConfigs = async () => {
    try {
        const response = await axios.get('/api/admin/store-hours')
        configs.value = response.data
    } catch (error) {
        console.error('Error fetching store hours:', error)
    }
}

const saveChanges = async () => {
    isSaving.value = true
    try {
        await axios.post('/api/admin/store-hours/bulk-update', {
            configs: configs.value
        })
        alert('Store hours updated successfully!')
    } catch (error) {
        console.error('Error updating store hours:', error)
        alert('Failed to update store hours. Please try again.')
    } finally {
        isSaving.value = false
    }
}

onMounted(() => {
    fetchConfigs()
})
</script> 