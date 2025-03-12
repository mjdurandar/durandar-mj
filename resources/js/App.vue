<template>
  <div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-4xl font-bold text-center text-gray-900 mb-12">
        Bakery Opening Hours
      </h1>
      
      <StoreStatus />
      <StoreSchedule />
    </div>
  </div>
</template>

<script setup>
import StoreStatus from './components/StoreStatus.vue'
import StoreSchedule from './components/StoreSchedule.vue'
</script> 