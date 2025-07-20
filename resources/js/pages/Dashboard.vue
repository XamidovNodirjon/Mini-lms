<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatBalance } from '@/lib/utils'; // Summalarni formatlash uchun

// Lucide Icons importlari (o'rnatilganligiga ishonch hosil qiling: npm install lucide-vue-next)
import { User2Icon, UserIcon, UsersIcon, DollarSignIcon } from 'lucide-vue-next'; // WalletIcon olib tashlandi

// Controllerdan keladigan props'lar
const props = defineProps<{
    totalStudents: number;
    totalTeachers: number;
    totalGroups: number;
    todayRevenue: number;
    // totalDebt: number; // Umumiy qarzdorlik prop'i olib tashlandi
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bosh sahifa',
        href: route('dashboard'),
    },
];
</script>

<template>
    <Head title="Boshqaruv Paneli" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid gap-6 py-8 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Jami o'quvchilar</CardTitle>
                            <User2Icon class="h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">{{ props.totalStudents }}</div>
                            <p class="text-xs text-muted-foreground mt-1">Ro'yxatdagi barcha o'quvchilar</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Jami o'qituvchilar</CardTitle>
                            <UserIcon class="h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">{{ props.totalTeachers }}</div>
                            <p class="text-xs text-muted-foreground mt-1">Ro'yxatdagi barcha o'qituvchilar</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Jami guruhlar</CardTitle>
                            <UsersIcon class="h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">{{ props.totalGroups }}</div>
                            <p class="text-xs text-muted-foreground mt-1">Faol guruhlar soni</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Bugungi tushum</CardTitle>
                            <DollarSignIcon class="h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">{{ formatBalance(props.todayRevenue) }}</div>
                            <p class="text-xs text-muted-foreground mt-1">Bugungi to'lovlardan tushum</p>
                        </CardContent>
                    </Card>

                </div>

                <div class="mt-8 p-6 rounded-lg border bg-card text-card-foreground shadow-sm">
                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Qo'shimcha ma'lumotlar va grafiklar</h3>
                    <p class="text-muted-foreground">Bu yerga kelajakda qo'shimcha grafiklar, jadvallar yoki boshqa statistik ma'lumotlarni qo'shishingiz mumkin.</p>
                    <div class="relative h-64 border-2 border-dashed border-border rounded-md flex items-center justify-center mt-6 bg-secondary/20">
                        <span class="text-muted-foreground text-lg">Bu yerda qo'shimcha vizualizatsiyalar joylashadi</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Agar qo'shimcha stil kerak bo'lsa */
</style>
