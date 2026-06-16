<template>
    <div ref="bellContainerRef" class="relative inline-block">
        <!-- Notification Trigger Button -->
        <button
            @click="toggleDropdown"
            class="relative p-2 rounded-lg text-text-secondary hover:text-text hover:bg-slate-100/80 dark:hover:bg-slate-800/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 active:scale-95 transition-all duration-200 cursor-pointer bell-jiggle"
            aria-label="Notifications"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="22"
                height="22"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="transition-transform duration-200"
            >
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
            </svg>
            
            <!-- Badge Count -->
            <span
                v-if="unreadCount > 0"
                :class="['absolute top-1.5 right-1.5 inline-flex items-center justify-center min-w-5 h-5 px-1 text-[10px] font-bold leading-none text-white bg-primary rounded-full shadow-sm border-2 border-surface transform translate-x-1/4 -translate-y-1/4 select-none', shouldPop ? 'badge-pop' : 'pulse-badge']"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 md:w-96 bg-surface/90 backdrop-blur-xl border border-border/60 rounded-xl shadow-[0_20px_50px_-12px_rgba(99,102,241,0.12),_0_0_1px_1px_rgba(99,102,241,0.03)] z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200"
        >
            <!-- Dropdown Header -->
            <div class="px-4 py-3 border-b border-border/60 bg-surface/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="font-display font-semibold text-text text-sm">Notifications</span>
                    <span 
                        v-if="unreadCount > 0" 
                        class="bg-primary/10 text-primary text-[10px] font-bold px-2 py-0.5 rounded-full select-none animate-pulse"
                    >
                        {{ unreadCount }} new
                    </span>
                </div>
                <button
                    v-if="notifications.length > 0"
                    @click="clearNotifications"
                    class="font-display text-xs text-primary hover:text-primary-dark font-semibold transition-all duration-200 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 px-2.5 py-1.5 rounded-lg bg-primary/5 hover:bg-primary/10 active:scale-95"
                >
                    Clear all
                </button>
            </div>

            <!-- Dropdown Body -->
            <div class="py-1">
                <!-- Empty State -->
                <div v-if="notifications.length === 0" class="flex flex-col items-center justify-center py-12 px-6 text-center select-none">
                    <div class="relative flex items-center justify-center mb-4 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" class="w-24 h-24" fill="none">
                            <!-- Outer concentric ring 1 (very faint, rotating) -->
                            <circle cx="60" cy="60" r="48" stroke="currentColor" stroke-width="1" stroke-dasharray="3 6" class="text-primary/20 ring-spin" />
                            
                            <!-- Concentric ring 2 -->
                            <circle cx="60" cy="60" r="38" stroke="currentColor" stroke-width="1.2" class="text-primary/30" />
                            
                            <!-- Concentric ring 3 (pulsing) -->
                            <circle cx="60" cy="60" r="28" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 2" class="text-primary/45 ring-pulse" />
                            
                            <!-- Glow and Ring behind the bell -->
                            <circle cx="60" cy="60" r="18" fill="currentColor" class="text-primary/5 dark:text-primary/10" />
                            <circle cx="60" cy="60" r="18" stroke="currentColor" stroke-width="1.5" class="text-primary/60" />
                            
                            <!-- Bell Icon at the center -->
                            <g transform="translate(48, 48)">
                                <path 
                                    d="M12 4a4 4 0 0 0-4 4v5H6v2h12v-2h-2V8a4 4 0 0 0-4-4z" 
                                    fill="currentColor" 
                                    class="text-primary/20"
                                />
                                <path 
                                    d="M12 3a5 5 0 0 0-5 5v5H5v2h14v-2h-2V8a5 5 0 0 0-5-5z" 
                                    stroke="currentColor" 
                                    stroke-width="1.8" 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    class="text-primary"
                                />
                                <path 
                                    d="M9.5 17a2.5 2.5 0 0 0 5 0" 
                                    stroke="currentColor" 
                                    stroke-width="1.8" 
                                    stroke-linecap="round"
                                    class="text-primary"
                                />
                            </g>
                        </svg>
                    </div>
                    <h3 class="text-sm font-display font-semibold text-text mb-1.5">All caught up!</h3>
                    <p class="text-xs text-text-secondary max-w-[240px] leading-relaxed">
                        You'll receive updates when tasks are moved, comments added, or assigned to you.
                    </p>
                </div>

                <!-- Notifications List -->
                <ul v-else class="max-h-80 overflow-y-auto divide-y divide-border/40 scrollbar-thin">
                    <li
                        v-for="(notification, index) in notifications"
                        :key="index"
                        class="group px-4 py-3 hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-all duration-200 ease-out flex gap-3 align-top cursor-default"
                    >
                        <!-- Event-Specific Icon -->
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center self-start transform group-hover:scale-105 transition-transform duration-200" :class="getIconBgClass(notification.type)">
                            <!-- Task Moved -->
                            <svg v-if="notification.type === 'moved'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="getIconColorClass(notification.type)">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0-4.5 4.5M21 7.5H7.5" />
                            </svg>
                            <!-- Task Assigned -->
                            <svg v-else-if="notification.type === 'assigned'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="getIconColorClass(notification.type)">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m0 0-.003-.004A3.5 3.5 0 0 1 9.4 15.5H14.6a3.5 3.5 0 0 1 3.4 3.219m-9.4-3.219a9.093 9.093 0 0 0-3.741-.479 3 3 0 0 0-4.682 2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c2.17 0 4.207-.576 5.963-1.584A6.062 6.062 0 0 1 18 18.72M12 11.25a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75ZM4.5 7.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Zm10.5 0a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                            </svg>
                            <!-- Task Commented / Other -->
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="getIconColorClass(notification.type)">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-1.074-.765 6.002 6.002 0 0 1 3.007-4.996C5.844 13.98 5 12.012 5 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                            </svg>
                        </div>
                        
                        <!-- Notification Message & Timestamp -->
                        <div class="flex-1 space-y-1">
                            <p class="text-xs text-text-secondary leading-relaxed">
                                <template v-for="(part, i) in parseMessage(notification.message)" :key="i">
                                    <strong v-if="part.bold" class="font-semibold text-text">{{ part.text }}</strong>
                                    <span v-else>{{ part.text }}</span>
                                </template>
                            </p>
                            <div class="flex items-center gap-1 text-[10px] text-text-muted font-medium select-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <span>{{ formatRelativeTime(notification.time) }}</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const bellContainerRef = ref(null);

const props = defineProps({
    workspaceId: {
        type: [Number, String],
        required: true,
    },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const unreadCount = ref(0);
const notifications = ref([]);
const isOpen = ref(false);
const shouldPop = ref(false);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const clearNotifications = () => {
    notifications.value = [];
    unreadCount.value = 0;
    isOpen.value = false;
};

const handleNewNotification = (message, type = 'info') => {
    notifications.value.unshift({ message, type, time: new Date() });
    unreadCount.value++;
};

// Pulse badge count when unreadCount increases
watch(unreadCount, (newVal, oldVal) => {
    if (newVal > oldVal) {
        shouldPop.value = true;
        setTimeout(() => {
            shouldPop.value = false;
        }, 300);
    }
});

// Auto-close dropdown when clicking outside — use ref.contains() not CSS class
const closeDropdown = (e) => {
    if (isOpen.value && bellContainerRef.value && !bellContainerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

// Safe message parser — splits on quoted strings, returns structured parts (no v-html/XSS risk)
const parseMessage = (message) => {
    if (!message) return [{ text: '', bold: false }];
    const parts = [];
    const regex = /"([^"]+)"/g;
    let lastIndex = 0;
    let match;
    while ((match = regex.exec(message)) !== null) {
        if (match.index > lastIndex) {
            parts.push({ text: message.slice(lastIndex, match.index), bold: false });
        }
        parts.push({ text: match[1], bold: true });
        lastIndex = regex.lastIndex;
    }
    if (lastIndex < message.length) {
        parts.push({ text: message.slice(lastIndex), bold: false });
    }
    return parts;
};

const getIconBgClass = (type) => {
    if (type === 'moved') {
        return 'bg-blue-50/80 dark:bg-blue-950/20 border border-blue-100/80 dark:border-blue-900/40 shadow-sm shadow-blue-100/10';
    }
    if (type === 'assigned') {
        return 'bg-emerald-50/80 dark:bg-emerald-950/20 border border-emerald-100/80 dark:border-emerald-900/40 shadow-sm shadow-emerald-100/10';
    }
    return 'bg-purple-50/80 dark:bg-purple-950/20 border border-purple-100/80 dark:border-purple-900/40 shadow-sm shadow-purple-100/10';
};

const getIconColorClass = (type) => {
    if (type === 'moved') return 'text-blue-600 dark:text-blue-400';
    if (type === 'assigned') return 'text-emerald-600 dark:text-emerald-400';
    return 'text-purple-600 dark:text-purple-400';
};

const formatRelativeTime = (date) => {
    if (!date) return 'Just now';
    const seconds = Math.floor((new Date() - date) / 1000);
    if (seconds < 60) return 'Just now';
    
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    
    return date.toLocaleDateString();
};

let channel = null;

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('click', closeDropdown);
    }

    if (window.Echo) {
        channel = window.Echo.private(`workspace.${props.workspaceId}`);

        channel.listen('TaskMoved', (e) => {
            const taskName = e.task?.title || 'A task';
            const newStatus = typeof e.task?.status === 'object' ? e.task.status.value : e.task?.status;
            handleNewNotification(`Task "${taskName}" was moved to "${newStatus || 'another status'}".`, 'moved');
        });

        channel.listen('TaskAssigned', (e) => {
            const taskName = e.task?.title || 'A task';
            const userName = e.user?.name || 'Someone';
            if (e.user?.id === currentUserId.value) {
                handleNewNotification(`You were assigned to task "${taskName}".`, 'assigned');
            } else {
                handleNewNotification(`"${userName}" was assigned to task "${taskName}".`, 'assigned');
            }
        });

        channel.listen('TaskCommented', (e) => {
            const taskName = e.task?.title || 'a task';
            const userName = e.comment?.user?.name || 'Someone';
            handleNewNotification(`"${userName}" commented on "${taskName}".`, 'comment');
        });
    } else {
        console.warn('Laravel Echo is not initialized. Notifications will not work.');
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('click', closeDropdown);
    }
    // Stop individual listeners only — do NOT call Echo.leave() which would destroy
    // the shared channel used by TaskModal for real-time comments.
    if (channel) {
        channel.stopListening('TaskMoved');
        channel.stopListening('TaskAssigned');
        channel.stopListening('TaskCommented');
        channel = null;
    }
});
</script>

<style scoped>
@keyframes jiggle {
    0%, 100% { transform: rotate(0); }
    20%, 60% { transform: rotate(-6deg); }
    40%, 80% { transform: rotate(6deg); }
}

@keyframes pop {
    0% { transform: translate(25%, -25%) scale(0.6); opacity: 0; }
    50% { transform: translate(25%, -25%) scale(1.35); }
    100% { transform: translate(25%, -25%) scale(1); opacity: 1; }
}

@keyframes pulse-badge {
    0% {
        box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7);
    }
    70% {
        box-shadow: 0 0 0 5px rgba(99, 102, 241, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
    }
}

@keyframes ring-spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes ring-pulse {
    0%, 100% {
        stroke-width: 1.2px;
        opacity: 0.4;
    }
    50% {
        stroke-width: 2px;
        opacity: 0.8;
    }
}

.bell-jiggle:hover svg {
    animation: jiggle 0.4s ease-in-out;
}

.badge-pop {
    animation: pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

.pulse-badge {
    animation: pulse-badge 2s infinite;
}

.ring-spin {
    transform-origin: center;
    animation: ring-spin 60s linear infinite;
}

.ring-pulse {
    transform-origin: center;
    animation: ring-pulse 3s ease-in-out infinite;
}
</style>
