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
// Oddiy select ishlatishda Select komponentlari kerak emas, lekin agar kelajakda kerak bo'lsa turishi mumkin
// import {
//     Select,
//     SelectContent,
//     SelectItem,
//     SelectTrigger,
//     SelectValue,
// } from "@/components/ui/select";

import { ArrowLeft, Save } from 'lucide-vue-next'; // Ikonkalar

// Agar kelajakda calendar kerak bo'lsa, bu importlar qayta qo'shiladi
// import { Calendar } from '@/components/ui/calendar';
// import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
// import { cn } from '@/lib/utils';
// import { format } from 'date-fns';
// import { Calendar as CalendarIcon } from 'lucide-vue-next';
// import { ref, watch } from 'vue';


const props = defineProps<{
    group: {
        id: number;
        name: string;
        teacher_id: number;
        monthly_fee: number;
        start_date: string;
        time: string;
    };
    teachers: Array<{
        id: number;
        name: string;
    }>;
}>();

const form = useForm({
    _method: 'put', // PUT metodini ishlatish uchun
    name: props.group.name,
    teacher_id: props.group.teacher_id,
    monthly_fee: props.group.monthly_fee,
    start_date: props.group.start_date,
    time: props.group.time,
});

const submit = () => {
    form.post(route('groups.update', props.group.id), {
        onSuccess: () => {
            // Yangilash muvaffaqiyatli bo'lganda foydalanuvchiga xabar berish
            // Masalan: useToast() orqali toast xabar chiqarish
            // Orqaga qaytish yoki boshqa sahifaga yo'naltirish
        },
        onError: () => {
            // Xatolik bo'lganda
        }
    });
};
</script>

<template>
    <Head :title="`Guruhni Tahrirlash: ${group.name}`" />

    <AppLayout>
        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class=" mb-5 flex items-center justify-between w-full">
                    <div class="flex items-center space-x-2">
                        <Link :href="route('groups.index')"
                              class="text-gray-500 hover:text-gray-700 flex items-center space-x-1 transition-colors hover:text-indigo-600">
                            <ArrowLeft class="h-4 w-4" />
                            <span>Guruhlar Ro'yxati</span>
                        </Link>
                    </div>
                </div>
                <Card class="rounded-lg mb-6 border-2">
                    <CardHeader class="p-6">
                        <CardTitle class="text-3xl font-extrabold">Guruh Ma'lumotlarini Tahrirlash</CardTitle>
                        <CardDescription class="text-lg mt-2">
                            Mavjud guruhning asosiy ma'lumotlarini yangilang.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-6 py-6 sm:p-8">
                        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="col-span-1 md:col-span-2">
                                <Label for="name" class="block text-sm font-medium mb-2">Guruh Nomi</Label>
                                <Input id="name" type="text" v-model="form.name" required autofocus
                                       class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring focus:border" />
                                <div v-if="form.errors.name" class="text-sm mt-2">{{ form.errors.name }}</div>
                            </div>

                            <div class="col-span-1">
                                <Label for="teacher_id" class="block text-sm font-medium mb-2">O'qituvchi</Label>
                                <div class="relative">
                                    <select
                                        id="teacher_id"
                                        v-model="form.teacher_id"
                                        required
                                        class="block w-full pl-3 pr-10 py-2 text-base border rounded-lg shadow-sm focus:outline-none focus:ring focus:border sm:text-sm appearance-none"
                                    >
                                        <option value="" disabled>O'qituvchini tanlang</option>
                                        <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                            {{ teacher.name }}
                                        </option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="form.errors.teacher_id" class="text-sm mt-2">{{ form.errors.teacher_id }}</div>
                            </div>

                            <div class="col-span-1">
                                <Label for="start_date" class="block text-sm font-medium mb-2">Boshlanish Sanasi</Label>
                                <Input id="start_date" type="date" v-model="form.start_date" required
                                       class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring focus:border" />
                                <div v-if="form.errors.start_date" class="text-sm mt-2">{{ form.errors.start_date }}</div>
                            </div>

                            <div class="col-span-1">
                                <Label for="monthly_fee" class="block text-sm font-medium mb-2">Oylik To'lov (UZS)</Label>
                                <Input id="monthly_fee" type="number" v-model="form.monthly_fee" required min="0"
                                       step="0.01" class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring focus:border" />
                                <div v-if="form.errors.monthly_fee" class="text-sm mt-2">{{ form.errors.monthly_fee }}</div>
                            </div>

                            <div class="col-span-1">
                                <Label for="time" class="block text-sm font-medium mb-2">Dars Vaqti (Masalan: 14:00-16:00)</Label>
                                <Input id="time" type="text" v-model="form.time" required
                                       placeholder="Masalan: Dush-Chor-Jum 14:00-16:00"
                                       class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring focus:border" />
                                <div v-if="form.errors.time" class="text-sm mt-2">{{ form.errors.time }}</div>
                            </div>
                            <div class="col-span-1 md:col-span-2 flex justify-end pt-2 mt-6">
                                <Button type="submit" :disabled="form.processing"
                                        class="inline-flex items-center px-6 py-3 border rounded-md shadow-sm text-base font-medium transition ease-in-out duration-150">
                                    <Save class="h-5 w-5 mr-2" />
                                    <span>Ma'lumotlarni Yangilash</span>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
