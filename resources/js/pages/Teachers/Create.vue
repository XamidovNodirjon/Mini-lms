<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

import InputError from '@/Components/InputError.vue';

// Form holatini boshqarish
const form = useForm({
    full_name: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('teachers.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Yangi O'qituvchi Qo'shish" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Yangi O'qituvchi Qo'shish</h2>
        </template>

        <div class="py-12">
            <div class="max-w-md mx-auto sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Yangi O'qituvchi Qo'shish</CardTitle>
                        <CardDescription>
                            O'qituvchi ma'lumotlarini kiriting.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit">
                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="full_name">To'liq Ism</Label>
                                    <Input
                                        id="full_name"
                                        type="text"
                                        v-model="form.full_name"
                                        placeholder="Ali Valiyev"
                                        required
                                    />
                                    <InputError class="mt-1" :message="form.errors.full_name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="phone">Telefon Raqam</Label>
                                    <Input
                                        id="phone"
                                        type="text"
                                        v-model="form.phone"
                                        placeholder="+998901234567"
                                        required
                                    />
                                    <InputError class="mt-1" :message="form.errors.phone" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="password">Parol</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        v-model="form.password"
                                        required
                                        autocomplete="new-password"
                                    />
                                    <InputError class="mt-1" :message="form.errors.password" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="password_confirmation">Parolni Tasdiqlash</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        v-model="form.password_confirmation"
                                        required
                                        autocomplete="new-password"
                                    />
                                    <InputError class="mt-1" :message="form.errors.password_confirmation" />
                                </div>
                            </div>
                        </form>
                    </CardContent>
                    <CardFooter class="flex justify-end">
                        <Button
                            type="submit"
                            @click="submit"
                            :disabled="form.processing"
                        >
                            Saqlash
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
