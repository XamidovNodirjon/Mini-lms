<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
// Select komponentlari import qilinmaydi
import { ArrowLeft, Save } from 'lucide-vue-next';
import { watch } from 'vue';

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
    };
    students: Array<{
        id: number;
        full_name: string;
    }>;
    groups: Array<{
        id: number;
        name: string;
    }>;
    statuses: string[];
}>();

const form = useForm({
    _method: 'put', // PUT metodini ishlatish uchun
    student_id: props.debt.student_id,
    group_id: props.debt.group_id,
    amount: parseFloat(props.debt.amount),
    month: props.debt.month,
    paid_amount: parseFloat(props.debt.paid_amount),
    is_paid: props.debt.is_paid,
    status: props.debt.status,
});

// `amount` yoki `paid_amount` o'zgarganda statusni avtomatik hisoblash
const calculateStatus = () => {
    const amount = parseFloat(form.amount ? form.amount.toString() : '0');
    const paidAmount = parseFloat(form.paid_amount ? form.paid_amount.toString() : '0');

    if (amount <= 0) {
        form.status = 'unpaid';
        form.is_paid = false;
        return;
    }

    if (paidAmount >= amount) {
        form.status = 'paid';
        form.is_paid = true;
    } else if (paidAmount > 0 && paidAmount < amount) {
        form.status = 'partial';
        form.is_paid = false;
    } else {
        form.status = 'unpaid';
        form.is_paid = false;
    }
};

watch(() => form.amount, calculateStatus);
watch(() => form.paid_amount, calculateStatus);


const submit = () => {
    form.post(route('debts.update', props.debt.id), {
        onSuccess: () => {
            alert("Muvaffaqiyatli! Qarz muvaffaqiyatli yangilandi.");
        },
        onError: (errors) => {
            alert("Xatolik yuz berdi! Qarzni yangilashda muammo yuz berdi. Iltimos, ma'lumotlarni tekshiring.");
            console.error('Qarzni yangilashda xatolik:', errors);
        },
    });
};
</script>

<template>
    <Head :title="`Qarzni Tahrirlash: ${debt.id}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center space-x-2">
                    <Link :href="route('debts.index')"
                          class="text-gray-500 hover:text-gray-700 flex items-center space-x-1 transition-colors hover:text-indigo-600">
                        <ArrowLeft class="h-4 w-4" />
                        <span>Qarzlar Ro'yxati</span>
                    </Link>
                    <span class="mx-2 text-gray-400">/</span>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Qarzni <span class="text-indigo-600">Tahrirlash: ID {{ debt.id }}</span>
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card class="rounded-lg shadow-lg border-2 border-slate-200 dark:border-slate-700">
                    <CardHeader class="pb-6">
                        <CardTitle class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">Qarz Ma'lumotlarini Tahrirlash</CardTitle>
                        <CardDescription class="text-gray-600 dark:text-gray-400 text-lg">
                            Mavjud qarz yozuvining ma'lumotlarini yangilang.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-6 py-6 sm:p-8">
                        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="col-span-1">
                                <Label for="student_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Talaba</Label>
                                <select id="student_id" v-model="form.student_id" required
                                        class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 sm:text-sm">
                                    <option :value="null" disabled>Talabani tanlang</option>
                                    <option v-for="student in students" :key="student.id" :value="student.id">
                                        {{ student.full_name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.student_id" class="text-red-500 text-sm mt-2">{{ form.errors.student_id }}</div>
                            </div>

                            <div class="col-span-1">
                                <Label for="group_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Guruh</Label>
                                <select id="group_id" v-model="form.group_id" required
                                        class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 sm:text-sm">
                                    <option :value="null" disabled>Guruhni tanlang</option>
                                    <option v-for="group in groups" :key="group.id" :value="group.id">
                                        {{ group.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.group_id" class="text-red-500 text-sm mt-2">{{ form.errors.group_id }}</div>
                            </div>

                            <div class="col-span-1">
                                <Label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Qarz Miqdori (UZS)</Label>
                                <Input id="amount" type="number" v-model="form.amount" required min="0.01" step="0.01"
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-100" />
                                <div v-if="form.errors.amount" class="text-red-500 text-sm mt-2">{{ form.errors.amount }}</div>
                            </div>

                            <div class="col-span-1">
                                <Label for="paid_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To'langan Miqdor (UZS)</Label>
                                <Input id="paid_amount" type="number" v-model="form.paid_amount" min="0" step="0.01"
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-100" />
                                <div v-if="form.errors.paid_amount" class="text-red-500 text-sm mt-2">{{ form.errors.paid_amount }}</div>
                            </div>

                            <div class="col-span-1">
                                <Label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Qarz Oyi</Label>
                                <Input id="month" type="month" v-model="form.month" required
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-100" />
                                <div v-if="form.errors.month" class="text-red-500 text-sm mt-2">{{ form.errors.month }}</div>
                            </div>

                            <div class="col-span-1">
                                <Label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Holati</Label>
                                <Input id="status" type="text" :value="form.status.charAt(0).toUpperCase() + form.status.slice(1)" readonly disabled
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm bg-gray-100 dark:bg-gray-700 dark:text-gray-300 cursor-not-allowed" />
                                <div v-if="form.errors.status" class="text-red-500 text-sm mt-2">{{ form.errors.status }}</div>
                            </div>

                            <div class="col-span-1 md:col-span-2 flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
                                <Button type="submit" :disabled="form.processing"
                                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                                    <Save class="h-5 w-5 mr-2" />
                                    <span>Qarzni Yangilash</span>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
