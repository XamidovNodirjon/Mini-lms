<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import {Head, Link, router} from '@inertiajs/vue3';
import {Button} from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
// Table komponentlari import qilinmaydi
// Badge komponenti import qilinmaydi

import {ArrowLeft, Edit, Trash2} from 'lucide-vue-next';

// useToast o'chiriladi, oddiy alert ishlatiladi

const props = defineProps<{
    debt: {
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
        payments: Array<{
            id: number;
            amount: string;
            date: string; // date string formatida keladi
            note: string | null;
            type: string; // 'debt' yoki 'balance'
            created_at: string; // Yaratilgan sana
        }>;
    };
}>();

// Qarz holatiga qarab Tailwind klasslarini qaytarish (Badge o'rniga)
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

// Pul formatini osonroq qilish uchun yordamchi funksiya
const formatCurrency = (value: string | number) => {
    return new Intl.NumberFormat('uz-UZ', {
        style: 'currency',
        currency: 'UZS',
        minimumFractionDigits: 0
    }).format(parseFloat(value as string));
};

// Sanani formatlash
const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = {year: 'numeric', month: 'long', day: 'numeric'};
    return new Date(dateString).toLocaleDateString('uz-UZ', options);
};

// Qarzni o'chirish funksiyasi (oddiy alert bilan)
const deleteDebt = (id: number, studentName: string) => {
    if (confirm(`Haqiqatan ham ${studentName} talabaning qarzini o'chirmoqchimisiz? Ushbu qarzga bog'liq to'lovlar saqlanib qolishi mumkin.`)) {
        router.delete(route('debts.destroy', id), {
            onSuccess: () => {
                alert(`Muvaffaqiyatli! Qarz muvaffaqiyatli o'chirildi.`);
                router.visit(route('debts.index')); // Ro'yxat sahifasiga qaytish
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
    <Head :title="`Qarz Tafsilotlari: ID ${debt.id}`"/>

    <AppLayout>
        <template #header>
            <Link :href="route('debts.index')"
                  class="text-gray-500 hover:text-gray-700 flex items-center space-x-1 transition-colors hover:text-indigo-600">
                <ArrowLeft class="h-4 w-4"/>
                <span>Qarzlar Ro'yxati</span>
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class=" mb-5 flex items-center justify-between w-full">
                    <div class="flex items-center space-x-2">
                        <Link :href="route('debts.index')"
                              class="text-gray-500 hover:text-gray-700 flex items-center space-x-1 transition-colors hover:text-indigo-600">
                            <ArrowLeft class="h-4 w-4"/>
                            <span>Qarzlar Ro'yxati</span>
                        </Link>
                        <span class="mx-2 text-gray-400">/</span>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            <span class="text-indigo-600">Qarz Tafsilotlari: ID {{ debt.id }}</span>
                        </h2>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Link :href="route('debts.edit', debt.id)">
                            <Button variant="outline" class="h-9 px-4 shadow-sm">
                                <Edit class="h-4 w-4 mr-2"/>
                                Tahrirlash
                            </Button>
                        </Link>
                        <Button variant="destructive" class="h-9 px-4 shadow-sm"
                                @click="deleteDebt(debt.id, debt.student.full_name)">
                            <Trash2 class="h-4 w-4 mr-2"/>
                            O'chirish
                        </Button>
                    </div>
                </div>
                <Card class="rounded-lg shadow-lg border-2 border-slate-200 dark:border-slate-700">
                    <CardHeader class="pb-6">
                        <CardTitle class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">Qarz Tafsilotlari
                        </CardTitle>
                        <CardDescription class="text-gray-600 dark:text-gray-400 text-lg">
                            Qarz yozuvi bo'yicha batafsil ma'lumotlar.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-6 py-6 sm:p-8 grid gap-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Talaba:</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ debt.student.full_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Guruh:</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{
                                        debt.group.name
                                    }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Qarz Miqdori:</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ formatCurrency(debt.amount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">To'langan Miqdor:</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ formatCurrency(debt.paid_amount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Qarz Oyi:</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ debt.month }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Holati:</p>
                                <span :class="getStatusClasses(debt.status)"
                                      class="inline-flex items-center px-3 py-1 mt-1 rounded-full text-base font-semibold">
                                    {{ debt.status.charAt(0).toUpperCase() + debt.status.slice(1) }}
                                </span>
                            </div>
                        </div>

                        <template v-if="debt.payments && debt.payments.length > 0">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-6 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Bog'liq To'lovlar</h3>
                            <div class="overflow-x-auto border rounded-lg shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                            ID
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                            Miqdor
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                            Sana
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                            Izoh
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                            Turi
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="payment in debt.payments" :key="payment.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ payment.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            {{ formatCurrency(payment.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            {{ formatDate(payment.date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            {{ payment.note || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            {{ payment.type.charAt(0).toUpperCase() + payment.type.slice(1) }}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                        <template v-else>
                            <p class="text-muted-foreground mt-6 mb-4 text-center text-base">Bu qarzga bog'liq to'lovlar
                                topilmadi.</p>
                        </template>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
