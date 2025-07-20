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
import InputError from '@/Components/InputError.vue';
import { ArrowLeft, Save, Check, ChevronsUpDown } from 'lucide-vue-next';

// Shadcn UI komponentlarini import qilish
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from "@/components/ui/command";
import { cn } from '@/lib/utils';

import { ref, computed } from 'vue';

const props = defineProps<{
    student: {
        id: number;
        full_name: string;
        phone: string;
        birth_date: string;
        balance: number;
        groups: Array<{ id: number; name: string }>; // Bog'langan guruhlar
    };
    groups: Array<{
        id: number;
        name: string;
    }>;
}>();

const form = useForm({
    _method: 'put', // PUT metodini ishlatish uchun
    full_name: props.student.full_name,
    phone: props.student.phone,
    // Tug'ilgan sana null bo'lishi mumkin, shuning uchun bo'sh string qilib tekshiramiz
    birth_date: props.student.birth_date || '',
    balance: props.student.balance,
    group_ids: props.student.groups.map(group => group.id), // Mavjud guruh ID'larini oldindan tanlash
});

const openGroupSelect = ref(false);

const getGroupNameById = (id: number) => {
    return props.groups.find(group => group.id === id)?.name || '';
};

const isGroupSelected = (groupId: number) => {
    return form.group_ids.includes(groupId);
};

const toggleGroupSelection = (groupId: number) => {
    if (isGroupSelected(groupId)) {
        form.group_ids = form.group_ids.filter(id => id !== groupId);
    } else {
        form.group_ids.push(groupId);
    }
};

const selectedGroupNames = computed(() => {
    if (form.group_ids.length === 0) {
        return "Guruhlarni tanlang...";
    }
    return form.group_ids.map(id => getGroupNameById(id)).join(', ');
});

const submit = () => {
    form.post(route('students.update', props.student.id), {
        onSuccess: () => {
            alert('O\'quvchi ma\'lumotlari muvaffaqiyatli yangilandi.');
        },
        onError: (errors) => {
            alert('Xatolik yuz berdi! Iltimos, ma\'lumotlarni tekshiring.');
            console.error(errors);
        },
    });
};
</script>

<template>
    <Head :title="`O'quvchini Tahrirlash: ${student.full_name}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center space-x-2">
                    <Link :href="route('students.index')"
                          class="text-gray-500 hover:text-gray-700 flex items-center space-x-1 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                        <ArrowLeft class="h-4 w-4" />
                        <span>O'quvchilar Ro'yxati</span>
                    </Link>
                    <span class="mx-2 text-gray-400">/</span>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                        O'quvchini <span class="text-indigo-600 dark:text-indigo-400">Tahrirlash: {{ student.full_name }}</span>
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card class="rounded-lg shadow-lg border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800">
                    <CardHeader class="pb-6">
                        <CardTitle class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">O'quvchi Ma'lumotlarini Tahrirlash</CardTitle>
                        <CardDescription class="text-gray-600 dark:text-gray-400 text-lg">
                            Mavjud o'quvchining ma'lumotlarini yangilang.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-6 py-6 sm:p-8">
                        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="col-span-1">
                                <Label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To'liq Ism</Label>
                                <Input
                                    id="full_name"
                                    type="text"
                                    v-model="form.full_name"
                                    placeholder="Ali Valiyev"
                                    required
                                    class="w-full"
                                />
                                <InputError class="mt-1" :message="form.errors.full_name" />
                            </div>

                            <div class="col-span-1">
                                <Label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telefon Raqam</Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    v-model="form.phone"
                                    placeholder="+998901234567"
                                    required
                                    class="w-full"
                                />
                                <InputError class="mt-1" :message="form.errors.phone" />
                            </div>

                            <div class="col-span-1">
                                <Label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tug'ilgan Sana</Label>
                                <Input
                                    id="birth_date"
                                    type="date"
                                    v-model="form.birth_date"
                                    class="w-full"
                                />
                                <InputError class="mt-1" :message="form.errors.birth_date" />
                            </div>

                            <div class="col-span-1">
                                <Label for="balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Balans (UZS)</Label>
                                <Input
                                    id="balance"
                                    type="number"
                                    v-model="form.balance"
                                    min="0"
                                    step="0.01"
                                    class="w-full"
                                />
                                <InputError class="mt-1" :message="form.errors.balance" />
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <Label for="groups" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Guruhlar</Label>
                                <Popover v-model:open="openGroupSelect">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="openGroupSelect"
                                            class="w-full justify-between hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            {{ selectedGroupNames }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[var(--radix-popover-trigger-width)] p-0">
                                        <Command>
                                            <CommandInput placeholder="Guruh qidirish..." />
                                            <CommandEmpty>Guruh topilmadi.</CommandEmpty>
                                            <CommandList>
                                                <CommandGroup>
                                                    <CommandItem
                                                        v-for="group in props.groups"
                                                        :key="group.id"
                                                        :value="group.name"
                                                        @select="toggleGroupSelection(group.id)"
                                                        class="cursor-pointer"
                                                    >
                                                        <Check
                                                            :class="cn(
                                                                'mr-2 h-4 w-4',
                                                                isGroupSelected(group.id) ? 'opacity-100' : 'opacity-0'
                                                            )"
                                                        />
                                                        {{ group.name }}
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <InputError class="mt-1" :message="form.errors.group_ids" />
                            </div>

                            <div class="col-span-1 md:col-span-2 flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
                                <Button type="submit" :disabled="form.processing"
                                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                                    <Save class="h-5 w-5 mr-2" />
                                    <span>Yangilash</span>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
