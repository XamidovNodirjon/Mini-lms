<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';
import {ref} from 'vue';

// Shadcn UI komponentlarini import qilish
import {Button} from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import {Input} from "@/components/ui/input";
import {Label} from "@/components/ui/label";

// Boshqa komponentlar
import InputError from '@/Components/InputError.vue';

// Lucide ikonkalarni import qilish
import {ArrowLeft, Save} from 'lucide-vue-next'; // Save ikonkasini ham qo'shdim

// Propslarni to'g'ri qabul qilish
const props = defineProps<{
    teacher: {
        id: number;
        name: string;
        phone: string;
    };
}>();

// Form holatini boshqarish
const form = useForm({
    name: props.teacher.name,
    phone: props.teacher.phone,
    password: '',
    password_confirmation: '',
    _method: 'put',
});

const submit = () => {
    form.post(route('teachers.update', props.teacher.id), {
        onSuccess: () => {
            alert('O\'qituvchi ma\'lumotlari muvaffaqiyatli yangilandi.');
            form.password = '';
            form.password_confirmation = '';
        },
        onError: (errors) => {
            alert('Xatolik yuz berdi! Iltimos, ma\'lumotlarni tekshiring.');
            console.error(errors);
        },
    });
};
</script>

<template>
    <Head :title="`${teacher.name}`"/>

    <AppLayout>
        <div class="py-5">
            <div class="max-w-md mx-auto sm:px-6 lg:px-8">
                <div class=" mb-5 flex items-center justify-between w-full">
                    <div class="flex items-center space-x-2">
                        <Link :href="route('teachers.index')"
                              class="text-gray-500 hover:text-gray-700 flex items-center space-x-1 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                            <ArrowLeft class="h-4 w-4"/>
                            <span>O'qituvchilar Ro'yxati</span>
                        </Link>
                        <span class="mx-2 text-gray-400">/</span>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                            Tahrirlash: <span class="text-indigo-600 dark:text-indigo-400">{{ teacher.name }}</span>
                        </h2>
                    </div>
                </div>
                <Card class="rounded-xl shadow-xl border border-border bg-background text-foreground">
                    <CardHeader class="pb-6">
                        <CardTitle class="text-2xl font-bold">O'qituvchi Ma'lumotlarini Tahrirlash</CardTitle>
                        <CardDescription class="text-muted-foreground">
                            O'qituvchining ma'lumotlarini o'zgartiring. Faqat o'zgartirmoqchi bo'lgan maydonlarni
                            to'ldiring.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-6 py-6 sm:p-8">
                        <form @submit.prevent="submit">
                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="name">To'liq Ism</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        v-model="form.name"
                                        autofocus
                                        class="w-full"
                                    />
                                    <InputError class="mt-1" :message="form.errors.name"/>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="phone">Telefon Raqam</Label>
                                    <Input
                                        id="phone"
                                        type="text"
                                        v-model="form.phone"
                                        class="w-full"
                                    />
                                    <InputError class="mt-1" :message="form.errors.phone"/>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="password">Yangi Parol (agar o'zgartirilsa)</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        v-model="form.password"
                                        autocomplete="new-password"
                                        class="w-full"
                                    />
                                    <InputError class="mt-1" :message="form.errors.password"/>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="password_confirmation">Yangi Parolni Tasdiqlash</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        v-model="form.password_confirmation"
                                        autocomplete="new-password"
                                        class="w-full"
                                    />
                                    <InputError class="mt-1" :message="form.errors.password_confirmation"/>
                                </div>
                            </div>

                            <CardFooter class="flex justify-end pt-6 -mx-6 -mb-6 sm:-mx-8 sm:-mb-8">
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                                >
                                    <Save class="h-5 w-5 mr-2"/>
                                    <span>Yangilash</span>
                                </Button>
                            </CardFooter>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
