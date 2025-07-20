<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import { format } from 'date-fns'; // Sana formatlash uchun

// --- UI Komponentlari ---
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Calendar } from '@/components/ui/calendar'; // Calendar komponenti
import InputError from '@/Components/InputError.vue';
import { cn } from '@/lib/utils'; // Tailwindcss sinflarini shartli qo'shish uchun


// --- Ikonkalar ---
import {
    Edit, Trash2, Eye,
    ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight,
    PlusCircle,
    Save,
    Calendar as CalendarIcon, // Calendar ikonkasiga 'as CalendarIcon' qo'shildi
} from 'lucide-vue-next';

const props = defineProps<{
    groups: {
        data: Array<{
            id: number;
            name: string;
            teacher: { id: number; name: string; };
            monthly_fee: number;
            start_date: string;
            time: string;
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
    teachers: Array<{
        id: number;
        name: string;
    }>;
}>();

const search = ref(props.filters.search || '');

const performSearch = debounce(() => {
    window.location.href = route('groups.index', { search: search.value });
}, 300);

watch(search, performSearch);

const deleteForm = useForm({});

const deleteGroup = (id: number) => {
    if (confirm('Haqiqatan ham bu guruhni o\'chirmoqchimisiz?')) {
        deleteForm.delete(route('groups.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                alert('Guruh muvaffaqiyatli o\'chirildi.');
            },
            onError: (errors) => {
                alert('Guruhni o\'chirishda xatolik yuz berdi!');
                console.error(errors);
            }
        });
    }
};

const getPageNumber = (label: string) => {
    if (label.includes('Previous')) return props.groups.current_page - 1;
    if (label.includes('Next')) return props.groups.current_page + 1;
    const numericLabel = label.replace(/&laquo;|&raquo;/g, '').trim();
    return parseInt(numericLabel);
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('uz-UZ', { style: 'currency', currency: 'UZS' }).format(value);
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Noma\'lum';
    return new Date(dateString).toLocaleDateString('uz-UZ');
};

const breadcrumbs = [
    {
        title: 'Guruhlar',
        href: '/groups',
    },
];

// --- Yangi Guruh Yaratish Modali Logikasi ---
const isCreateModalOpen = ref(false); // Modalning ochiq/yopiq holati

const createForm = useForm({
    name: '',
    teacher_id: null as number | null,
    monthly_fee: 0,
    start_date: '' as string, // Sana string formatida saqlanadi
    time: '',
});

const openCreateModal = () => {
    createForm.reset(); // Formani tozalash
    createForm.clearErrors(); // Xatoliklarni tozalash
    isCreateModalOpen.value = true; // Modalni ochish
};

const submitCreateForm = () => {
    createForm.post(route('groups.store'), {
        onSuccess: () => {
            alert('Guruh muvaffaqiyatli qo\'shildi!');
            isCreateModalOpen.value = false; // Modalni yopish
            // Inertia avtomatik ravishda sahifani yangilaydi
        },
        onError: (errors) => {
            alert('Guruhni qo\'shishda xatolik yuz berdi! Iltimos, ma\'lumotlarni tekshiring.');
            console.error(errors);
        },
        preserveScroll: true,
    });
};

// Start Date Calendar Popover (Create modal uchun)
const openStartDateCalendarCreate = ref(false);

const startDateDisplayCreate = computed(() => {
    return createForm.start_date ? format(new Date(createForm.start_date), 'PPP') : 'Sanani tanlang';
});

const selectStartDateCreate = (date: Date) => {
    createForm.start_date = format(date, 'yyyy-MM-dd'); // Backend uchun YYYY-MM-DD formatida saqlash
    openStartDateCalendarCreate.value = false; // Kalendarni yopish
};
</script>

<template>
    <Head title="Guruhlar"/>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class=" mb-5 flex flex-col md:flex-row items-start md:items-center justify-between w-full space-y-4 md:space-y-0 md:space-x-4">
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight dark:text-gray-200">
                        Guruhlar Ro'yxati
                    </h2>
                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <Input
                            type="text"
                            v-model="search"
                            placeholder="Guruh nomidan yoki o'qituvchidan qidirish..."
                            class="w-full md:w-64"
                        />
                        <Button variant="default"
                                class="bg-primary text-primary-foreground hover:bg-primary/90 whitespace-nowrap"
                                @click="openCreateModal">
                            <PlusCircle class="h-4 w-4 mr-2"/>
                            <span>Yangi Guruh Qo'shish</span>
                        </Button>
                    </div>
                </div>
                <Card class="rounded-xl border">
                    <CardContent class="p-0">
                        <div class="overflow-x-auto rounded-b-xl border border-border">
                            <table class="min-w-full divide-y divide-border">
                                <thead class="bg-muted text-muted-foreground">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Guruh
                                        Nomi
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        O'qituvchi
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Oylik
                                        To'lov
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Boshlanish Sanasi
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vaqt
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Qo'shilgan Sana
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                        Amallar
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-background text-foreground divide-y divide-border">
                                <tr v-if="!groups.data.length">
                                    <td colspan="8" class="px-6 py-8 text-center text-muted-foreground text-base">Hech
                                        qanday guruh topilmadi.
                                    </td>
                                </tr>
                                <tr v-for="group in groups.data" :key="group.id"
                                    class="hover:bg-accent hover:text-accent-foreground transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ group.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ group.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ group.teacher.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{
                                            formatCurrency(group.monthly_fee)
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{
                                            formatDate(group.start_date)
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ group.time }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{
                                            formatDate(group.created_at)
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center space-x-2">
                                            <Link :href="route('groups.show', group.id)">
                                                <Button variant="outline" size="icon">
                                                    <Eye class="h-4 w-4 text-blue-500 dark:text-blue-300"/>
                                                </Button>
                                            </Link>
                                            <Link :href="route('groups.edit', group.id)">
                                                <Button variant="outline" size="icon">
                                                    <Edit class="h-4 w-4 text-yellow-500 dark:text-yellow-300"/>
                                                </Button>
                                            </Link>
                                            <Button variant="destructive" size="icon" @click="deleteGroup(group.id)">
                                                <Trash2 class="h-4 w-4"/>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="groups.last_page > 1" class="flex justify-center items-center mt-6 space-x-2">
                            <Link :href="groups.links[0].url">
                                <Button variant="outline" size="icon" :disabled="!groups.links[0].url">
                                    <ChevronsLeft class="h-4 w-4"/>
                                </Button>
                            </Link>
                            <Link :href="groups.links[1].url">
                                <Button variant="outline" size="icon" :disabled="!groups.links[1].url">
                                    <ChevronLeft class="h-4 w-4"/>
                                </Button>
                            </Link>

                            <template v-for="(link, index) in groups.links" :key="index">
                                <Link
                                    v-if="link.url && link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                                    :href="link.url">
                                    <Button
                                        :variant="link.active ? 'default' : 'outline'"
                                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                                        size="icon"
                                    >
                                        {{ getPageNumber(link.label) }}
                                    </Button>
                                </Link>
                                <span
                                    v-else-if="!link.url && link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                                    class="h-9 w-9 inline-flex items-center justify-center rounded-md text-sm font-medium text-muted-foreground">
                                     ...
                                </span>
                            </template>

                            <Link :href="groups.links[groups.links.length - 2].url">
                                <Button variant="outline" size="icon"
                                        :disabled="!groups.links[groups.links.length - 2].url">
                                    <ChevronRight class="h-4 w-4"/>
                                </Button>
                            </Link>
                            <Link :href="groups.links[groups.links.length - 1].url">
                                <Button variant="outline" size="icon"
                                        :disabled="!groups.links[groups.links.length - 1].url">
                                    <ChevronsRight class="h-4 w-4"/>
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <Dialog v-model:open="isCreateModalOpen">
            <DialogContent class="sm:max-w-[425px] bg-background text-foreground border-border">
                <DialogHeader>
                    <DialogTitle>Yangi Guruh Qo'shish</DialogTitle>
                    <DialogDescription>
                        Yangi guruh yaratish uchun quyidagi ma'lumotlarni to'ldiring.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitCreateForm">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="create-name">Guruh Nomi</Label>
                            <Input id="create-name" type="text" v-model="createForm.name" autofocus />
                            <InputError class="mt-1" :message="createForm.errors.name"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-teacher_id">O'qituvchi</Label>
                            <Select v-model="createForm.teacher_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="O'qituvchini tanlang" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                        {{ teacher.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="createForm.errors.teacher_id"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-monthly_fee">Oylik To'lov (UZS)</Label>
                            <Input id="create-monthly_fee" type="number" v-model="createForm.monthly_fee" min="0" step="0.01" />
                            <InputError class="mt-1" :message="createForm.errors.monthly_fee"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-start_date">Boshlanish Sanasi</Label>
                            <Popover v-model:open="openStartDateCalendarCreate">
                                <PopoverTrigger as-child>
                                    <Button
                                        :variant="'outline'"
                                        :class="cn(
                                            'w-full justify-start text-left font-normal',
                                            !createForm.start_date && 'text-muted-foreground',
                                        )"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ startDateDisplayCreate }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar
                                        v-model:selected="createForm.start_date"
                                        @update:selected="selectStartDateCreate"
                                        :initial-focus="true"
                                        mode="single"
                                        :from-date="new Date(2020, 0, 1)"
                                        :to-date="new Date(2030, 0, 1)"
                                    />
                                </PopoverContent>
                            </Popover>
                            <InputError class="mt-1" :message="createForm.errors.start_date"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-time">Dars Vaqti (Masalan: 14:00-16:00)</Label>
                            <Input id="create-time" type="text" v-model="createForm.time" placeholder="Masalan: Dush-Chor-Jum 14:00-16:00" />
                            <InputError class="mt-1" :message="createForm.errors.time"/>
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
