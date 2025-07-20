<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"; // Shadcn Select import qilindi

// Agar Shadcn table komponentlari mavjud bo'lsa, ularni import qiling.
// Hozirda odatiy <table> teglariga stil berishni afzal ko'ramiz.
// import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"; // Agar foydalansangiz

import { PlusCircle, Search, Eye, Edit, Trash2, MoreHorizontal } from 'lucide-vue-next'; // Ikonkalar
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

import { onMounted, onUnmounted } from 'vue';

const props = defineProps<{
    debts: {
        data: Array<{
            id: number;
            student_id: number;
            group_id: number;
            amount: string;
            month: string;
            paid_amount: string;
            is_paid: boolean;
            status: 'unpaid' | 'partial' | 'paid';
            student: { id: number; full_name: string };
            group: { id: number; name: string };
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
    statuses: string[];
}>();

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const activeDropdownId = ref<number | null>(null);

const toggleDropdown = (id: number) => {
    activeDropdownId.value = activeDropdownId.value === id ? null : id;
};

const closeDropdown = (event: MouseEvent) => {
    if (activeDropdownId.value !== null && !(event.target as HTMLElement).closest('.dropdown-menu-wrapper')) {
        activeDropdownId.value = null;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});
onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});

const applyFilters = debounce(() => {
    router.get(route('debts.index'), {
        search: search.value,
        status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(search, applyFilters);
watch(selectedStatus, applyFilters);

const getStatusClasses = (status: string) => {
    switch (status) {
        case 'paid':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'partial':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'unpaid':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

const formatCurrency = (value: string | number) => {
    return new Intl.NumberFormat('uz-UZ', { style: 'currency', currency: 'UZS', minimumFractionDigits: 0 }).format(parseFloat(value as string));
};

const deleteDebt = (id: number, studentName: string, month: string) => {
    if (confirm(`Haqiqatan ham ${studentName} talabaning ${month} oyi uchun qarzini o'chirmoqchimisiz?`)) {
        router.delete(route('debts.destroy', id), {
            onSuccess: () => {
                alert(`Muvaffaqiyatli! Qarz muvaffaqiyatli o'chirildi.`);
                activeDropdownId.value = null;
            },
            onError: (errors) => {
                alert(`Xatolik yuz berdi! Qarzni o'chirishda muammo yuz berdi.`);
                console.error(errors);
            }
        });
    }
};
</script>

<template>
    <Head title="Qarzlar Ro'yxati" />
    <AppLayout>
        <div class="py-5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-5 flex items-center justify-between w-full">
                    <h2 class="font-semibold text-xl text-foreground leading-tight">
                        <span class="text-indigo-600 dark:text-indigo-400">Qarzdorliklar</span> Ro'yxati
                    </h2>
                    <Link :href="route('debts.create')">
                        <Button class="h-9 px-4 shadow-sm">
                            <PlusCircle class="h-4 w-4 mr-2" />
                            Yangi Qarz Qo'shish
                        </Button>
                    </Link>
                </div>

                <Card class="rounded-xl  shadow-sm bg-card text-card-foreground">
                    <CardHeader class="p-6 pb-4">
                        <CardTitle class="text-2xl font-bold">Barcha Qarzdorliklar</CardTitle>
                        <CardDescription class="text-base text-muted-foreground">
                            Tizimdagi talabalarning barcha qarzdorlik yozuvlari ro'yxati.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-6 pt-0">
                        <div class="flex flex-col sm:flex-row items-center gap-4 mb-6">
                            <div class="relative flex-grow w-full sm:w-auto">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="search"
                                    placeholder="Qidirish (Talaba, Guruh, Oy, Miqdor...)"
                                    class="pl-9 pr-4 py-2 w-full"
                                />
                            </div>
                            <div class="w-full sm:w-[180px] flex-shrink-0">
                                <Select v-model="selectedStatus">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Barcha holatlar" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Barcha holatlar</SelectItem>
                                        <SelectItem v-for="status in statuses" :key="status" :value="status">
                                            {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="overflow-x-auto border rounded-lg shadow-sm">
                            <table class="min-w-full divide-y divide-border">
                                <thead class="bg-muted text-muted-foreground">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Talaba</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Guruh</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Qarz Miqdori</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">To'langan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Oy</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Holati</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Harakatlar</th>
                                </tr>
                                </thead>
                                <tbody class="bg-background divide-y divide-border">
                                <template v-if="props.debts.data.length > 0">
                                    <tr v-for="debt in props.debts.data" :key="debt.id" class="hover:bg-muted/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ debt.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ debt.student.full_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ debt.group.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ formatCurrency(debt.amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ formatCurrency(debt.paid_amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ debt.month }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span :class="getStatusClasses(debt.status)"
                                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium min-w-[80px] text-center justify-center">
                                                    {{ debt.status.charAt(0).toUpperCase() + debt.status.slice(1) }}
                                                </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-medium relative dropdown-menu-wrapper">
                                            <Button variant="ghost" size="icon" @click.stop="toggleDropdown(debt.id)" class="inline-flex items-center">
                                                <span class="sr-only">Harakatlar menyusini ochish</span>
                                                <MoreHorizontal class="h-5 w-5 text-muted-foreground" />
                                            </Button>

                                            <div v-if="activeDropdownId === debt.id" class="absolute right-0 mt-2 w-48 text-right bg-popover text-popover-foreground rounded-md shadow-lg ring-1 ring-border focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="menu-button">
                                                <div class="py-1" role="none">
                                                    <Link :href="route('debts.show', debt.id)" class="text-foreground block px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground flex items-center" role="menuitem">
                                                        <Eye class="h-4 w-4 mr-2" />
                                                        Ko'rish
                                                    </Link>
                                                    <Link :href="route('debts.edit', debt.id)" class="text-foreground block px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground flex items-center" role="menuitem">
                                                        <Edit class="h-4 w-4 mr-2" />
                                                        Tahrirlash
                                                    </Link>
                                                    <button @click="deleteDebt(debt.id, debt.student.full_name, debt.month)" type="button" class="text-destructive block w-full text-left px-4 py-2 text-sm hover:bg-destructive/10 hover:text-destructive-foreground flex items-center" role="menuitem">
                                                        <Trash2 class="h-4 w-4 mr-2" />
                                                        O'chirish
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-base text-muted-foreground">
                                            Hech qanday qarzdorlik topilmadi.
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="props.debts.links.length > 3" class="flex justify-between items-center mt-6 flex-wrap gap-4">
                            <div class="text-sm text-muted-foreground">
                                Ko'rsatilmoqda {{ props.debts.from }}-{{ props.debts.to }} dan {{ props.debts.total }} ta
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    v-for="(link, index) in props.debts.links"
                                    :key="index"
                                    :href="link.url || '#'"
                                    class="h-9 px-3 py-1 border rounded-md text-sm flex items-center justify-center transition-all duration-200"
                                    :class="{
                                        'bg-primary text-primary-foreground border-primary hover:bg-primary/90 shadow-md': link.active,
                                        'bg-background border-input text-foreground hover:bg-accent hover:text-accent-foreground': !link.active,
                                        'pointer-events-none opacity-50': !link.url
                                    }"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
