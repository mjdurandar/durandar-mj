<template>
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Check Store Hours</h2>
        
        <div class="space-y-4">
            <!-- Date Picker -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Select Date</label>
                <input 
                    type="date" 
                    v-model="selectedDate"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :min="today"
                >
            </div>

            <!-- Store Status Display -->
            <div v-if="storeStatus" class="mt-4">
                <div class="flex items-center space-x-2">
                    <div :class="[
                        'w-3 h-3 rounded-full',
                        storeStatus.is_open ? 'bg-green-500' : 'bg-red-500'
                    ]"></div>
                    <span class="font-medium" :class="storeStatus.is_open ? 'text-green-600' : 'text-red-600'">
                        {{ storeStatus.is_open ? 'Open' : 'Closed' }}
                    </span>
                </div>

                <!-- Hours Details -->
                <div class="mt-2 text-sm text-gray-600">
                    {{ storeStatus.message }}
                </div>

                <!-- Next Opening Time -->
                <div v-if="!storeStatus.is_open && storeStatus.next_opening_friendly" class="mt-2 text-sm text-gray-600">
                    Next opening: {{ storeStatus.next_opening_friendly }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { format, parseISO } from 'date-fns'

const selectedDate = ref(new Date().toISOString().split('T')[0])
const storeStatus = ref(null)

const today = computed(() => new Date().toISOString().split('T')[0])

const checkStoreStatus = async () => {
    try {
        const response = await axios.get(`/api/store-hours/check-date/${selectedDate.value}`)
        storeStatus.value = response.data
    } catch (error) {
        console.error('Error fetching store status:', error)
    }
}

watch(selectedDate, () => {
    checkStoreStatus()
})

// Initial check
checkStoreStatus()
</script> 