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
                        storeStatus.isOpen && !storeStatus.isLunchBreak ? 'bg-green-500' : 'bg-red-500'
                    ]"></div>
                    <span class="font-medium">
                        {{ getStatusMessage }}
                    </span>
                </div>

                <!-- Next Opening Time -->
                <div v-if="!storeStatus.isOpen || storeStatus.isLunchBreak" class="mt-2 text-sm text-gray-600">
                    {{ getNextOpeningMessage }}
                </div>

                <!-- Today's Hours -->
                <div v-if="hours.length > 0" class="mt-4">
                    <h3 class="text-sm font-medium text-gray-700">Hours:</h3>
                    <div class="mt-1 space-y-1">
                        <div class="text-sm text-gray-600">
                            Open: {{ formatTime(hours[0].open) }} - {{ formatTime(hours[0].close) }}
                        </div>
                        <div v-if="hours[1]" class="text-sm text-gray-600">
                            Lunch Break: {{ formatTime(hours[1].open) }} - {{ formatTime(hours[1].close) }}
                        </div>
                    </div>
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
const hours = ref([])

const today = computed(() => new Date().toISOString().split('T')[0])

const getStatusMessage = computed(() => {
    if (!storeStatus.value) return ''
    if (storeStatus.value.isLunchBreak) return 'Currently on Lunch Break'
    return storeStatus.value.isOpen ? 'Open' : 'Closed'
})

const getNextOpeningMessage = computed(() => {
    if (!storeStatus.value || !storeStatus.value.nextOpeningTime) return ''
    if (storeStatus.value.isLunchBreak) {
        return `Reopening at ${formatTime(storeStatus.value.nextOpeningTime)}`
    }
    return `Next opening time: ${formatTime(storeStatus.value.nextOpeningTime)}`
})

const formatTime = (time) => {
    if (!time) return ''
    // Convert 24h format to 12h format
    const [hours, minutes] = time.split(':')
    const date = new Date()
    date.setHours(parseInt(hours))
    date.setMinutes(parseInt(minutes))
    return format(date, 'h:mm a')
}

const checkStoreStatus = async () => {
    try {
        const [statusResponse, hoursResponse] = await Promise.all([
            axios.get(`/api/store-hours/status?date=${selectedDate.value}`),
            axios.get(`/api/store-hours/today?date=${selectedDate.value}`)
        ])
        storeStatus.value = statusResponse.data
        hours.value = hoursResponse.data
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