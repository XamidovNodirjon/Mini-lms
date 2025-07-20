<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { type BreadcrumbItem } from '@/types';

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import InputError from '@/Components/InputError.vue';

import {
    Edit,
    Trash2,
    Eye,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    PlusCircle,
    Save,
} from 'lucide-vue-next';

const props = defineProps<{
    teachers: {
        data: Array<{
            id: number;
            name: string;
            phone: string;
            created_at: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
    };
    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search || '');

const performSearch = debounce(() => {
    window.location.href = route('teachers.index', { search: search.value });
}, 300);

watch(search, performSearch);

const deleteForm = useForm({});

const deleteTeacher = (id: number) => {
    if (confirm('Haqiqatan ham bu o\'qituvchini o\'chirmoqchimisiz?')) {
        deleteForm.delete(route('teachers.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                alert('O\'qituvchi muvaffaqiyatli o\'chirildi.');
            },
            onError: (errors) => {
                alert('O\'qituvchini o\'chirishda xatolik yuz berdi!');
                console.error(errors);
            }
        });
    }
};

const getPageNumber = (label: string) => {
    return parseInt(label.replace(/&laquo;|&raquo;/g, '').trim());
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('uz-UZ', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'O\'qituvchilar',
        href: '/teachers',
    },
];

const isEditModalOpen = ref(false);
const editingTeacher = ref<{ id: number; name: string; phone: string; } | null>(null);

const editForm = useForm({
    name: '',
    phone: '',
    password: '',
    password_confirmation: '',
    _method: 'put',
});

const openEditModal = (teacher: { id: number; name: string; phone: string; }) => {
    editingTeacher.value = teacher;
    editForm.name = teacher.name;
    editForm.phone = teacher.phone;
    editForm.password = '';
    editForm.password_confirmation = '';
    isEditModalOpen.value = true;
};

const submitEditForm = () => {
    if (!editingTeacher.value) return;

    editForm.post(route('teachers.update', editingTeacher.value.id), {
        onSuccess: () => {
            alert('O\'qituvchi ma\'lumotlari muvaffaqiyatli yangilandi.');
            isEditModalOpen.value = false;
        },
        onError: (errors) => {
            alert('Xatolik yuz berdi! Iltimos, ma\'lumotlarni tekshiring.');
            console.error(errors);
        },
        preserveScroll: true,
    });
};

const isCreateModalOpen = ref(false);

const createForm = useForm({
    name: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const openCreateModal = () => {
    createForm.reset(); // Formani tozalash
    isCreateModalOpen.value = true;
};

const submitCreateForm = () => {
    createForm.post(route('teachers.store'), {
        onSuccess: () => {
            alert('Yangi o\'qituvchi muvaffaqiyatli qo\'shildi.');
            isCreateModalOpen.value = false; // Modalni yopish
            createForm.reset(); // Formani tozalash
        },
        onError: (errors) => {
            alert('Xatolik yuz berdi! Iltimos, ma\'lumotlarni tekshiring.');
            console.error(errors);
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="O'qituvchilar" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card class="rounded-xl bg-background text-foreground">
                    <CardHeader class="flex flex-col md:flex-row items-start md:items-center justify-between space-y-4 md:space-y-0 pb-4">
                        <div class="w-full md:w-auto">
                            <CardTitle class="text-2xl font-bold">O'qituvchilar Ro'yxati</CardTitle>
                            <CardDescription class="text-muted-foreground">
                                Tizimdagi barcha o'qituvchilar nazorati.
                            </CardDescription>
                        </div>
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <Input
                                type="text"
                                v-model="search"
                                placeholder="O'qituvchi qidirish..."
                                class="w-full md:w-64"
                            />
                            <Button variant="default" @click="openCreateModal" class="bg-primary text-primary-foreground hover:bg-primary/90 whitespace-nowrap">
                                <PlusCircle class="h-4 w-4 mr-2" /> Yangi O'qituvchi Qo'shish
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto rounded-b-xl border border-border">
                            <table class="min-w-full divide-y divide-border">
                                <thead class="bg-muted text-muted-foreground">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider">To'liq Ism</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider">Telefon</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider">Qo'shilgan Sana</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium tracking-wider">Amallar</th>
                                </tr>
                                </thead>
                                <tbody class="bg-background text-foreground divide-y divide-border">
                                <tr v-if="!teachers.data.length">
                                    <td colspan="5" class="px-6 py-8 text-center text-muted-foreground text-base">Hech qanday o'qituvchi topilmadi.</td>
                                </tr>
                                <tr v-for="teacher in teachers.data" :key="teacher.id" class="hover:bg-accent hover:text-accent-foreground transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ teacher.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ teacher.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ teacher.phone }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatDate(teacher.created_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center space-x-2">
                                            <Link :href="route('teachers.show', teacher.id)">
                                                <Button variant="outline" size="icon">
                                                    <Eye class="h-4 w-4 text-blue-500 dark:text-blue-300" />
                                                </Button>
                                            </Link>
                                            <Button variant="outline" size="icon" @click="openEditModal(teacher)">
                                                <Edit class="h-4 w-4 text-yellow-500 dark:text-yellow-300" />
                                            </Button>
                                            <Button variant="destructive" size="icon" @click="deleteTeacher(teacher.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="teachers.last_page > 1" class="flex justify-center items-center mt-6 space-x-2">
                            <template v-for="(link, index) in teachers.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="{ 'pointer-events-none opacity-50': !link.url }"
                                >
                                    <Button
                                        :variant="link.active ? 'default' : 'outline'"
                                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                                        size="icon"
                                        class="h-9 w-9"
                                    >
                                        <span v-if="link.label === '&laquo; Previous'">
                                            <ChevronsLeft class="h-4 w-4" />
                                        </span>
                                        <span v-else-if="link.label === 'Next &raquo;'">
                                            <ChevronsRight class="h-4 w-4" />
                                        </span>
                                        <span v-else-if="link.label.includes('Previous')">
                                            <ChevronLeft class="h-4 w-4" />
                                        </span>
                                        <span v-else-if="link.label.includes('Next')">
                                            <ChevronRight class="h-4 w-4" />
                                        </span>
                                        <span v-else>
                                            {{ link.label }}
                                        </span>
                                    </Button>
                                </Link>
                                <span v-else-if="link.label === '...' || link.label.includes('Previous') || link.label.includes('Next')"
                                      class="h-9 w-9 inline-flex items-center justify-center rounded-md text-sm font-medium text-muted-foreground">
                                    <span v-if="link.label === '...'">...</span>
                                    <span v-else-if="link.label.includes('Previous')">
                                        <ChevronLeft class="h-4 w-4" />
                                    </span>
                                    <span v-else-if="link.label.includes('Next')">
                                        <ChevronRight class="h-4 w-4" />
                                    </span>
                                </span>
                            </template>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <Dialog v-model:open="isEditModalOpen">
            <DialogContent class="sm:max-w-[425px] bg-background text-foreground border-border">
                <DialogHeader>
                    <DialogTitle>O'qituvchini Tahrirlash</DialogTitle>
                    <DialogDescription>
                        O'qituvchining ma'lumotlarini yangilang. Faqat o'zgartirmoqchi bo'lgan maydonlarni to'ldiring.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEditForm">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="edit-name">To'liq Ism</Label>
                            <Input
                                id="edit-name"
                                type="text"
                                v-model="editForm.name"
                                autofocus
                            />
                            <InputError class="mt-1" :message="editForm.errors.name"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-phone">Telefon Raqam</Label>
                            <Input
                                id="edit-phone"
                                type="text"
                                v-model="editForm.phone"
                            />
                            <InputError class="mt-1" :message="editForm.errors.phone"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-password">Yangi Parol (agar o'zgartirilsa)</Label>
                            <Input
                                id="edit-password"
                                type="password"
                                v-model="editForm.password"
                                autocomplete="new-password"
                            />
                            <InputError class="mt-1" :message="editForm.errors.password"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-password_confirmation">Yangi Parolni Tasdiqlash</Label>
                            <Input
                                id="edit-password_confirmation"
                                type="password"
                                v-model="editForm.password_confirmation"
                                autocomplete="new-password"
                            />
                            <InputError class="mt-1" :message="editForm.errors.password_confirmation"/>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="editForm.processing">
                            <Save class="h-4 w-4 mr-2" />
                            Yangilash
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isCreateModalOpen">
            <DialogContent class="sm:max-w-[425px] bg-background text-foreground border-border">
                <DialogHeader>
                    <DialogTitle>Yangi O'qituvchi Qo'shish</DialogTitle>
                    <DialogDescription>
                        Yangi o'qituvchi ma'lumotlarini kiriting.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitCreateForm">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="create-name">To'liq Ism</Label>
                            <Input
                                id="create-name"
                                type="text"
                                v-model="createForm.name"
                                placeholder="Ali Valiyev"
                                required
                                autofocus
                            />
                            <InputError class="mt-1" :message="createForm.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-phone">Telefon Raqam</Label>
                            <Input
                                id="create-phone"
                                type="text"
                                v-model="createForm.phone"
                                placeholder="+998901234567"
                                required
                            />
                            <InputError class="mt-1" :message="createForm.errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-password">Parol</Label>
                            <Input
                                id="create-password"
                                type="password"
                                v-model="createForm.password"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-1" :message="createForm.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-password_confirmation">Parolni Tasdiqlash</Label>
                            <Input
                                id="create-password_confirmation"
                                type="password"
                                v-model="createForm.password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-1" :message="createForm.errors.password_confirmation" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="createForm.processing">
                            <Save class="h-4 w-4 mr-2" />
                            Saqlash
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
