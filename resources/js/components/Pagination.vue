<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();
</script>

<template>
    <div v-if="links.length > 3" class="flex justify-center items-center space-x-2">
        <template v-for="(link, key) in links" :key="key">
            <div v-if="link.url === null"
                 class="px-3 py-1 text-sm leading-4 text-gray-400 border border-gray-300 rounded-md select-none"
                 v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="block"
                :class="{ 'pointer-events-none opacity-50': link.active }"
                preserve-scroll
            >
                <Button
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    v-html="link.label"
                />
            </Link>
        </template>
    </div>
</template>
