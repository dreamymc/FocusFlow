<template>
    <div class="flex flex-col items-center justify-center p-6 bg-slate-50 rounded-lg border border-slate-100">
        <p class="text-xs text-slate-400 mb-4 uppercase tracking-wider font-semibold">Real-Time Notifications</p>
        <notification-bell :workspace-id="1"></notification-bell>
    </div>
</template>

<script setup>
import NotificationBell from './Components/NotificationBell.vue';
</script>
