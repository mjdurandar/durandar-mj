<template>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4">Current Status</h2>
      
      <div class="space-y-4">
        <div class="flex items-center">
          <div :class="[
            'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
            status.isOpen && !status.isLunchBreak
              ? 'bg-green-100 text-green-800'
              : status.isLunchBreak
                ? 'bg-yellow-100 text-yellow-800'
                : 'bg-red-100 text-red-800'
          ]">
            <div :class="[
              'w-2 h-2 rounded-full mr-2',
              status.isOpen && !status.isLunchBreak
                ? 'bg-green-500'
                : status.isLunchBreak
                  ? 'bg-yellow-500'
                  : 'bg-red-500'
            ]"></div>
            {{ statusMessage }}
          </div>
        </div>

        <div v-if="(!status.isOpen || status.isLunchBreak) && status.nextOpeningTime" 
          class="text-sm text-gray-600"
        >
          {{ nextOpeningMessage }}
        </div>

        <div class="mt-6 pt-4 border-t border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Today's Hours</h3>
          <div v-if="todayHours.length > 0" class="space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Open:</span>
              <span class="font-medium">{{ formatTime(todayHours[0].open) }} - {{ formatTime(todayHours[0].close) }}</span>
            </div>
            <div v-if="todayHours[1]" class="flex justify-between text-sm">
              <span class="text-gray-600">Lunch Break:</span>
              <span class="font-medium">{{ formatTime(todayHours[1].open) }} - {{ formatTime(todayHours[1].close) }}</span>
            </div>
          </div>
          <div v-else class="text-gray-600 text-sm">
            Closed today
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { format } from 'date-fns'

const status = ref({
  isOpen: false,
  isLunchBreak: false,
  nextOpeningTime: null
})

const todayHours = ref([])

const statusMessage = computed(() => {
  if (status.value.isLunchBreak) return 'On Lunch Break'
  return status.value.isOpen ? 'Open Now' : 'Closed'
})

const nextOpeningMessage = computed(() => {
  if (!status.value.nextOpeningTime) return ''
  
  if (status.value.isLunchBreak) {
    return `Reopening today at ${formatTime(status.value.nextOpeningTime)}`
  }

  const now = new Date()
  const [hours, minutes] = status.value.nextOpeningTime.split(':')
  const nextOpening = new Date(now)
  nextOpening.setHours(parseInt(hours), parseInt(minutes))

  if (nextOpening < now) {
    nextOpening.setDate(nextOpening.getDate() + 1)
  }

  return `Next opening: ${format(nextOpening, 'EEEE')} at ${formatTime(status.value.nextOpeningTime)}`
})

const formatTime = (time) => {
  if (!time) return ''
  const [hours, minutes] = time.split(':')
  const date = new Date()
  date.setHours(parseInt(hours))
  date.setMinutes(parseInt(minutes))
  return format(date, 'h:mm a')
}

const fetchStatus = async () => {
  try {
    const [statusResponse, hoursResponse] = await Promise.all([
      axios.get('/api/store-hours/status'),
      axios.get('/api/store-hours/today')
    ])
    status.value = statusResponse.data
    todayHours.value = hoursResponse.data
  } catch (error) {
    console.error('Error fetching store status:', error)
  }
}

onMounted(() => {
  fetchStatus()
  // Update status every minute
  setInterval(fetchStatus, 60000)
})
</script> 