<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Badge } from '@/components/ui/badge'; // Badge komponentasi import qilindi
import { debounce } from 'lodash';
import { ref } from 'vue';
import { formatBalance, formatDate } from '@/lib/utils';
import Pagination from '@/components/Pagination.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';

import PaymentCreateModal from '@/components/PaymentCreateModal.vue';

const props = defineProps<{
    payments: {
        data: Array<{
            id: number;
            student_id: number;
            group_id: number | null;
            amount: number;
            date: string;
            note?: string;
            type: string;
            debt_id?: number | null;
            created_at: string;
            updated_at: string;
            student: { id: number; full_name: string };
            group?: { id: number; name: string };
            debt?: {
                id: number;
                group?: { id: number; name: string };
            };
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        search?: string;
        type?: string;
    };
    paymentTypes: string[];
    students: Array<{ id: number; full_name: string }>;
    groups: Array<{ id: number; name: string }>;
}>();

const breadcrumbs = [
    { title: 'Bosh sahifa', href: route('dashboard') },
    { title: 'To\'lovlar', href: '#' },
];

const search = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || 'all');
const isModalOpen = ref(false);

const handleSearch = debounce(() => {
    router.get(route('payments.index'), {
        search: search.value,
        type: selectedType.value
    }, {
        preserveState: true,
        replace: true
    });
}, 300);

const handleFilterByType = () => {
    router.get(route('payments.index'), {
        search: search.value,
        type: selectedType.value
    }, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    search.value = '';
    selectedType.value = 'all';
    router.get(route('payments.index'), {}, {
        preserveState: true,
        replace: true
    });
};

const handlePaymentAdded = () => {
    isModalOpen.value = false;
    toast.success("To'lov muvaffaqiyatli qo'shildi!");
    router.reload({ preserveState: true });
};

const getGroupNameForPayment = (payment: any) => {
    if (payment.type === 'debt' && payment.debt && payment.debt.group) {
        return payment.debt.group.name;
    }
    if (payment.group) {
        return payment.group.name;
    }
    return '-';
};
</script>

<template>
    <Head title="To'lovlar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card class="rounded-lg  bg-background text-foreground">
                    <CardHeader class="pb-4 sm:pb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-2xl font-semibold text-foreground">To'lovlar Ro'yxati</CardTitle>
                                <CardDescription class="text-muted-foreground mt-1">Barcha o'quvchilarning to'lovlari.</CardDescription>
                            </div>
                            <Button @click="isModalOpen = true">Yangi to'lov qo'shish</Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-6">
                        <div class="mb-6 flex flex-wrap items-center gap-4">
                            <Input
                                placeholder="Qidiruv..."
                                v-model="search"
                                @input="handleSearch"
                                class="max-w-sm flex-grow"
                            />
                            <Select v-model="selectedType" @update:model-value="handleFilterByType">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="To'lov turi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Barcha turlar</SelectItem>
                                    <SelectItem v-for="type in paymentTypes" :key="type" :value="type">
                                        {{ type === 'debt' ? 'Qarz uchun' : 'Balansga' }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button @click="clearFilters" variant="outline">Filtrlarni tozalash</Button>
                        </div>

                        <div v-if="props.payments.data.length > 0" class="overflow-x-auto rounded-md border border-border">
                            <Table class="min-w-full divide-y divide-border">
                                <TableHeader class="bg-muted">
                                    <TableRow>
                                        <TableHead class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">#</TableHead>
                                        <TableHead class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Talaba</TableHead>
                                        <TableHead class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">To'lov summasi</TableHead>
                                        <TableHead class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Sana</TableHead>
                                        <TableHead class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Turi</TableHead>
                                        <TableHead class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Guruh</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody class="bg-background divide-y divide-border">
                                    <TableRow v-for="payment in props.payments.data" :key="payment.id" class="hover:bg-muted/50 transition-colors">
                                        <TableCell class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ payment.id }}</TableCell>
                                        <TableCell class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ payment.student.full_name }}</TableCell>
                                        <TableCell class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ formatBalance(payment.amount) }}</TableCell>
                                        <TableCell class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ formatDate(payment.date) }}</TableCell>
                                        <TableCell class="px-6 py-4 whitespace-nowrap text-sm">
                                            <Badge :variant="payment.type === 'debt' ? 'outline' : 'default'">
                                                {{ payment.type === 'debt' ? 'Qarz uchun' : 'Balansga' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                            {{ getGroupNameForPayment(payment) }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                        <div v-else class="text-center text-muted-foreground py-8">
                            <p>Hech qanday to'lov topilmadi.</p>
                        </div>
                        <div class="mt-6 flex justify-center">
                            <Pagination :links="props.payments.links" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <PaymentCreateModal
            :is-open="isModalOpen"
            @update:is-open="isModalOpen = $event"
            :students="students"
            :groups="groups"
            @payment-added="handlePaymentAdded"
        />
    </AppLayout>
</template>

<style scoped>
/* Scoped styleni to'liq olib tashladik, chunki Shadcn/Tailwind default ravishda ranglarga moslashadi. */
</style>
