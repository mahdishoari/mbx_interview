<script setup>
import Orders from './components/Orders.vue'

const TOKEN = 'CAND-LT8V'
</script>

<template>
  <main style="max-width: 900px; margin: 32px auto; font-family: system-ui;">
    <h1>Orders (Candidate {{ TOKEN }})</h1>
    <Orders />
  </main>
</template>
