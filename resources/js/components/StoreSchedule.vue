<template>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4">Weekly Schedule</h2>
      
      <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <div v-for="day in schedule" 
            :key="day.day"
            :class="[
              'p-4 rounded-lg border',
              isToday(day.day) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-medium" :class="isToday(day.day) ? 'text-blue-700' : 'text-gray-900'">
                {{ day.day }}
                <span v-if="isToday(day.day)" class="text-sm text-blue-600 ml-1">(Today)</span>
              </h3>
              <div v-if="day.alternate_weeks_only" 
                class="text-xs text-gray-500"
              >
                (Alternate Weeks)
              </div>
            </div>

            <div v-if="day.is_open" class="space-y-2">
              <div class="text-sm">
                <span class="text-gray-600">Open:</span>
                <span class="font-medium ml-1">
                  {{ day.opening_time }} - {{ day.closing_time }}
                </span>
              </div>
              <div v-if="day.lunch_break" class="text-sm text-gray-600">
                <span>Lunch:</span>
                <span class="font-medium ml-1">
                  {{ day.lunch_break.start }} - {{ day.lunch_break.end }}
                </span>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500">
              Closed
            </div>
          </div>
        </div>

        <div class="mt-4 text-sm text-gray-600">
          <p>* Regular opening hours apply to weekdays</p>
          <p v-if="hasAlternateWeeks">* Saturday opening hours alternate between weeks</p>
          <p>* Store is closed on {{ closedDays }}</p>
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
  return schedule.value.some(day => day.alternate_weeks_only)
})

const closedDays = computed(() => {
  const closed = schedule.value
    .filter(day => !day.is_open)
    .map(day => day.day)
  
  if (closed.length === 0) return 'no days'
  if (closed.length === 1) return closed[0]
  
  return `${closed.slice(0, -1).join(', ')} and ${closed[closed.length - 1]}`
})

const isToday = (dayName) => {
  const today = new Date().toLocaleString('en-US', { weekday: 'long' })
  return today === dayName
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