<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { format } from 'date-fns';

// --- UI Komponentlari ---
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/components/ui/dialog";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Calendar } from '@/components/ui/calendar';
import InputError from '@/Components/InputError.vue';
import { cn } from '@/lib/utils';


// --- Ikonkalar ---
import {
    ArrowLeft,
    Edit,
    Trash2,
    Users,
    UserRound,
    DollarSign,
    CalendarDays,
    Clock,
    Pencil,
    Save,
    Calendar as CalendarIcon,
} from 'lucide-vue-next';

const props = defineProps<{
    group: {
        id: number;
        name: string;
        teacher: {
            id: number;
            name: string;
        };
        monthly_fee: number;
        start_date: string;
        time: string;
        created_at: string;
        updated_at: string;
    };
    teachers: Array<{
        id: number;
        name: string;
    }>;
}>();

const deleteForm = useForm({});

const deleteGroup = (id: number) => {
    if (confirm('Haqiqatan ham bu guruhni o\'chirmoqchimisiz? Guruh bilan bog\'liq barcha ma\'lumotlar ham o\'chirilishi mumkin!')) {
        deleteForm.delete(route('groups.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                window.location.href = route('groups.index');
            },
            onError: (errors) => {
                alert('Guruhni o\'chirishda xatolik yuz berdi!');
                console.error(errors);
            }
        });
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('uz-UZ', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('uz-UZ', {style: 'currency', currency: 'UZS'}).format(value);
};

// --- Guruhni Tahrirlash Modali Logikasi ---
const isEditModalOpen = ref(false);

const editForm = useForm({
    _method: 'put',
    name: props.group.name,
    teacher_id: props.group.teacher.id,
    monthly_fee: props.group.monthly_fee,
    start_date: props.group.start_date,
    time: props.group.time,
});

const openEditModal = () => {
    editForm.name = props.group.name;
    editForm.teacher_id = props.group.teacher.id;
    editForm.monthly_fee = props.group.monthly_fee;
    editForm.start_date = props.group.start_date;
    editForm.time = props.group.time;

    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEditForm = () => {
    editForm.post(route('groups.update', props.group.id), {
        onSuccess: () => {
            alert('Guruh ma\'lumotlari muvaffaqiyatli yangilandi!');
            isEditModalOpen.value = false;
        },
        onError: (errors) => {
            alert('Ma\'lumotlarni yangilashda xatolik yuz berdi! Iltimos, ma\'lumotlarni tekshiring.');
            console.error(errors);
        },
        preserveScroll: true,
    });
};

// Start Date Calendar Popover (Edit modal uchun)
const openStartDateCalendarEdit = ref(false);

const startDateDisplayEdit = computed(() => {
    return editForm.start_date ? format(new Date(editForm.start_date), 'PPP') : 'Sanani tanlang';
});

const selectStartDateEdit = (date: Date) => {
    editForm.start_date = format(date, 'yyyy-MM-dd');
    openStartDateCalendarEdit.value = false;
};
</script>

<template>
    <Head :title="`Guruh: ${group.name}`"/>
    <AppLayout>
        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class=" mb-5 flex items-center justify-between w-full">
                    <div class="flex items-center space-x-2">
                        <Link :href="route('groups.index')"
                              class="text-gray-500 hover:text-gray-700 flex items-center space-x-1 transition-colors dark:text-gray-400 dark:hover:text-gray-200">
                            <ArrowLeft class="h-4 w-4"/>
                            <span>Guruhlarga qaytish</span>
                        </Link>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button variant="outline" class="flex items-center space-x-1 px-3 py-2 text-sm"
                                @click="openEditModal">
                            <Pencil class="h-4 w-4"/>
                            <span>Tahrirlash</span>
                        </Button>
                        <Button variant="destructive" class="flex items-center space-x-1 px-3 py-2 text-sm"
                                @click="deleteGroup(group.id)">
                            <Trash2 class="h-4 w-4"/>
                            <span>O'chirish</span>
                        </Button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <Card class="md:col-span-2 rounded-xl border shadow-sm bg-card text-card-foreground">
                        <CardHeader class="p-6"> <div class="flex items-center space-x-3">
                            <Users class="h-7 w-7 text-primary dark:text-primary-foreground"/>
                            <CardTitle class="text-2xl font-bold flex items-center">
                                {{ group.name }}
                            </CardTitle>
                        </div>
                            <CardDescription class="mt-2 text-muted-foreground">Guruh tafsilotlari va ma'lumotlari
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-4 p-6 pt-0"> <div class="flex items-center text-base space-x-3 text-foreground">
                            <UserRound class="h-4 w-4 text-muted-foreground"/>
                            <span>O'qituvchi: **{{ group.teacher.name }}**</span>
                        </div>
                            <div class="flex items-center text-base space-x-3 text-foreground">
                                <DollarSign class="h-4 w-4 text-muted-foreground"/>
                                <span>Oylik To'lov: **{{ formatCurrency(group.monthly_fee) }}**</span>
                            </div>
                            <div class="flex items-center text-base space-x-3 text-foreground">
                                <CalendarDays class="h-4 w-4 text-muted-foreground"/>
                                <span>Boshlanish Sanasi: **{{ formatDate(group.start_date) }}**</span>
                            </div>
                            <div class="flex items-center text-base space-x-3 text-foreground">
                                <Clock class="h-4 w-4 text-muted-foreground"/>
                                <span>Dars Vaqti: **{{ group.time }}**</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-xl border shadow-sm bg-card text-card-foreground">
                        <CardHeader class="p-6 pb-4"> <CardTitle class="text-lg font-semibold">Tezkor ma'lumot</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-3 text-sm p-6 pt-0"> <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Guruh ID</span>
                            <span class="font-medium text-foreground">#{{ group.id }}</span>
                        </div>
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">Qo'shilgan</span>
                                <span class="font-medium text-foreground">{{ formatDate(group.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">Oxirgi yangilanish</span>
                                <span class="font-medium text-foreground">{{ formatDate(group.updated_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="md:col-span-2 rounded-xl border shadow-sm bg-card text-card-foreground">
                        <CardHeader class="p-6 pb-4"> <CardTitle class="text-lg font-semibold">Guruh Tafsilotlari</CardTitle>
                        </CardHeader>
                        <CardContent class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 p-6 pt-0"> <div>
                            <p class="text-sm text-muted-foreground">Guruh Nomi</p>
                            <p class="font-medium text-foreground">{{ group.name }}</p>
                        </div>
                            <div>
                                <p class="text-sm text-muted-foreground">O'qituvchi</p>
                                <p class="font-medium text-foreground">{{ group.teacher.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Oylik To'lov</p>
                                <p class="font-medium text-foreground">{{ formatCurrency(group.monthly_fee) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Boshlanish Sanasi</p>
                                <p class="font-medium text-foreground">{{ formatDate(group.start_date) }}</p>
                            </div>
                            <div class="col-span-full"> <p class="text-sm text-muted-foreground">Dars Vaqti</p>
                                <p class="font-medium text-foreground">{{ group.time }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-xl md:col-span-1 border shadow-sm bg-card text-card-foreground">
                        <CardHeader class="p-6 pb-4"> <CardTitle class="text-lg font-semibold">Tezkor harakatlar</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-3 p-6 pt-0"> <Button variant="outline"
                                                                          class="w-full justify-start flex items-center space-x-2 py-2.5"
                                                                          @click="openEditModal">
                            <Pencil class="h-4 w-4 text-muted-foreground"/>
                            <span>Ma'lumotlarni tahrirlash</span>
                        </Button>
                            <Button variant="destructive"
                                    class="w-full justify-start flex items-center space-x-2 py-2.5"
                                    @click="deleteGroup(group.id)">
                                <Trash2 class="h-4 w-4"/>
                                <span>Guruhni o'chirish</span>
                            </Button>
                            <div class="mt-4 pt-3 border-t border-border">
                                <div class="flex items-center space-x-2 text-sm text-muted-foreground">
                                    <Clock class="h-4 w-4"/>
                                    <span>Qo'shimcha boshqaruv</span>
                                </div>
                                <p class="text-xs text-muted-foreground mt-1 ml-6">Bu guruhga talabalarni biriktirish, to'lovlar
                                    va davomatni boshqarish.</p>
                            </div>
                        </CardContent>
                    </Card>

                </div>
            </div>
        </div>

        <Dialog v-model:open="isEditModalOpen">
            <DialogContent class="sm:max-w-[425px] bg-background text-foreground border-border">
                <DialogHeader>
                    <DialogTitle>Guruhni Tahrirlash</DialogTitle>
                    <DialogDescription>
                        Mavjud guruhning ma'lumotlarini yangilang.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEditForm">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="edit-name">Guruh Nomi</Label>
                            <Input id="edit-name" type="text" v-model="editForm.name" autofocus />
                            <InputError class="mt-1" :message="editForm.errors.name"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-teacher_id">O'qituvchi</Label>
                            <Select v-model="editForm.teacher_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="O'qituvchini tanlang" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                        {{ teacher.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="editForm.errors.teacher_id"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-monthly_fee">Oylik To'lov (UZS)</Label>
                            <Input id="edit-monthly_fee" type="number" v-model="editForm.monthly_fee" min="0" step="0.01" />
                            <InputError class="mt-1" :message="editForm.errors.monthly_fee"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-start_date">Boshlanish Sanasi</Label>
                            <Popover v-model:open="openStartDateCalendarEdit">
                                <PopoverTrigger as-child>
                                    <Button
                                        :variant="'outline'"
                                        :class="cn(
                                            'w-full justify-start text-left font-normal',
                                            !editForm.start_date && 'text-muted-foreground',
                                        )"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ startDateDisplayEdit }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar
                                        v-model:selected="editForm.start_date"
                                        @update:selected="selectStartDateEdit"
                                        :initial-focus="true"
                                        mode="single"
                                        :from-date="new Date(2020, 0, 1)"
                                        :to-date="new Date(2030, 0, 1)"
                                    />
                                </PopoverContent>
                            </Popover>
                            <InputError class="mt-1" :message="editForm.errors.start_date"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-time">Dars Vaqti (Masalan: 14:00-16:00)</Label>
                            <Input id="edit-time" type="text" v-model="editForm.time" placeholder="Masalan: Dush-Chor-Jum 14:00-16:00" />
                            <InputError class="mt-1" :message="editForm.errors.time"/>
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
    </AppLayout>
</template>
