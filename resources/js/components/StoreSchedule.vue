<template>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4">Weekly Schedule</h2>
      
      <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <div v-for="day in schedule" 
            :key="day.date"
            :class="[
              'p-4 rounded-lg border',
              day.isToday ? 'border-blue-500 bg-blue-50' : 'border-gray-200'
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-medium" :class="day.isToday ? 'text-blue-700' : 'text-gray-900'">
                {{ day.name }}
                <span v-if="day.isToday" class="text-sm text-blue-600 ml-1">(Today)</span>
              </h3>
              <div v-if="hasAlternateWeeks && day.name === 'Saturday'" 
                class="text-xs text-gray-500"
              >
                (Alternate Weeks)
              </div>
            </div>

            <div v-if="day.hours.length > 0" class="space-y-2">
              <div class="text-sm">
                <span class="text-gray-600">Open:</span>
                <span class="font-medium ml-1">
                  {{ formatTime(day.hours[0].open) }} - {{ formatTime(day.hours[0].close) }}
                </span>
              </div>
              <div v-if="day.hours[1]" class="text-sm text-gray-600">
                <span>Lunch:</span>
                <span class="font-medium ml-1">
                  {{ formatTime(day.hours[1].open) }} - {{ formatTime(day.hours[1].close) }}
                </span>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500">
              Closed
            </div>
          </div>
        </div>

        <div class="mt-4 text-sm text-gray-600">
          <p>* Regular opening hours apply to Monday, Wednesday, and Friday</p>
          <p>* Saturday opening hours alternate between weeks</p>
          <p>* Store is closed on Tuesday, Thursday, and Sunday</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { format } from 'date-fns'

const schedule = ref([])
const hasAlternateWeeks = computed(() => {
  return schedule.value.some(day => 
    day.name === 'Saturday' && day.hours.length > 0
  )
})

const formatTime = (time) => {
  if (!time) return ''
  const [hours, minutes] = time.split(':')
  const date = new Date()
  date.setHours(parseInt(hours))
  date.setMinutes(parseInt(minutes))
  return format(date, 'h:mm a')
}

const fetchSchedule = async () => {
  try {
    const response = await axios.get('/api/store-hours/week')
    schedule.value = response.data
  } catch (error) {
    console.error('Error fetching schedule:', error)
  }
}

onMounted(() => {
  fetchSchedule()
})
</script> 