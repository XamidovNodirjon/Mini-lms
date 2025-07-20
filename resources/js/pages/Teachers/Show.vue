<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue'; // `computed` ham qo'shildi

// --- UI Komponentlari ---
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { Input } from "@/components/ui/input"; // Input komponenti ham qo'shildi
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

// --- Ikonkalar ---
import {
    ArrowLeft,
    Trash2,
    UserRound,
    Phone,
    CalendarDays,
    Clock,
    Info,
    Mail,
    MapPin,
    Check,
    X,
    Pencil,
    Power,
    Save,
} from 'lucide-vue-next';

// --- Propsni aniqlash ---
const props = defineProps<{
    teacher: {
        id: number;
        name: string;
        phone: string;
        email?: string;
        branch?: string;
        is_active?: boolean;
        created_at: string;
        updated_at: string;
    };
}>();

// --- Breadcrumbs ---
const breadcrumbs = computed(() => [
    {
        title: 'O\'qituvchilar',
        href: '/teachers',
    },
    {
        title: props.teacher.name, // O'qituvchi nomi dinamik qo'shildi
        href: `/teachers/${props.teacher.id}`,
    },
]);

// --- Yordamchi Funksiyalar ---
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('uz-UZ', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
};

const handleDeactivate = () => {
    alert('Bu xodimni faolsizlantirish funksiyasi kelajakda qo\'shiladi.');
};

// --- O'qituvchini o'chirish logikasi ---
const deleteForm = useForm({});

const deleteTeacher = () => {
    if (confirm(`Haqiqatan ham ${props.teacher.name} ismli o'qituvchini o'chirmoqchimisiz?`)) {
        deleteForm.delete(route('teachers.destroy', props.teacher.id), {
            preserveScroll: true,
            onSuccess: () => {
                alert('O\'qituvchi muvaffaqiyatli o\'chirildi.');
                window.location.href = route('teachers.index'); // O'chirilgandan keyin ro'yxat sahifasiga qaytish
            },
            onError: (errors) => {
                alert('O\'qituvchini o\'chirishda xatolik yuz berdi!');
                console.error(errors);
            }
        });
    }
};

// --- Tahrirlash Modali Logikasi ---
const isEditModalOpen = ref(false); // Modalning ochiq/yopiq holati

const editForm = useForm({
    name: props.teacher.name,
    phone: props.teacher.phone,
    email: props.teacher.email || '', // Email qo'shildi (agar mavjud bo'lsa)
    password: '',
    password_confirmation: '',
    _method: 'put', // PUT metodi uchun
});

const openEditModal = () => {
    // Joriy ma'lumotlarni formaga yuklaymiz
    editForm.name = props.teacher.name;
    editForm.phone = props.teacher.phone;
    editForm.email = props.teacher.email || ''; // Emailni ham yangilash
    editForm.password = '';
    editForm.password_confirmation = '';
    editForm.clearErrors(); // Oldingi xatolarni tozalash
    isEditModalOpen.value = true; // Modalni ochish
};

const submitEditForm = () => {
    editForm.post(route('teachers.update', props.teacher.id), {
        onSuccess: () => {
            alert('O\'qituvchi ma\'lumotlari muvaffaqiyatli yangilandi.');
            isEditModalOpen.value = false; // Modalni yopish
            // Inertia.js propslarni avtomatik yangilaydi, shuning uchun sahifa ma'lumotlari yangilanadi.
        },
        onError: (errors) => {
            alert('Ma\'lumotlarni yangilashda xatolik yuz berdi! Iltimos, ma\'lumotlarni tekshiring.');
            console.error(errors);
        },
        preserveScroll: true, // Sahifani scroll holatini saqlash
    });
};
</script>

<template>
    <Head :title="`${teacher.name}`"/>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-5 flex flex-col md:flex-row items-start md:items-center justify-between w-full space-y-3 md:space-y-0">
                    <div class="flex items-center space-x-2">
                        <Link :href="route('teachers.index')"
                              class="text-muted-foreground hover:text-foreground flex items-center space-x-1 transition-colors">
                            <ArrowLeft class="h-4 w-4"/>
                            <span>O'qituvchilarga qaytish</span>
                        </Link>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button variant="outline" class="flex items-center space-x-1 px-3 py-2 text-sm" @click="openEditModal()">
                            <Pencil class="h-4 w-4"/>
                            <span>Tahrirlash</span>
                        </Button>
                        <Button variant="destructive" class="flex items-center space-x-1 px-3 py-2 text-sm"
                                @click="deleteTeacher()">
                            <Trash2 class="h-4 w-4"/>
                            <span>O'chirish</span>
                        </Button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <Card class="md:col-span-2 rounded-xl border bg-card text-card-foreground shadow-sm">
                        <CardHeader class="pb-4 px-6 pt-6">
                            <div class="flex items-center space-x-4">
                                <UserRound class="h-10 w-10 text-primary"/>
                                <div class="flex flex-col">
                                    <CardTitle class="text-3xl font-bold flex items-center">
                                        {{ teacher.name }}
                                        <span v-if="teacher.is_active"
                                              class="ml-4 px-3 py-1 bg-green-500/10 text-green-600 text-sm font-semibold rounded-full flex items-center space-x-1 dark:bg-green-300/10 dark:text-green-300">
                                            <Check class="h-3.5 w-3.5 mr-1"/>
                                            <span>Faol</span>
                                        </span>
                                        <span v-else
                                              class="ml-4 px-3 py-1 bg-red-500/10 text-red-600 text-sm font-semibold rounded-full flex items-center space-x-1 dark:bg-red-300/10 dark:text-red-300">
                                            <X class="h-3.5 w-3.5 mr-1"/>
                                            <span>Faolsiz</span>
                                        </span>
                                    </CardTitle>
                                    <CardDescription class="mt-1 text-muted-foreground">Xodim haqida asosiy
                                        ma'lumotlar
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="grid gap-4 p-6">
                            <Separator/>
                            <div v-if="teacher.email" class="flex items-center text-base space-x-3">
                                <Mail class="h-5 w-5 text-muted-foreground"/>
                                <span class="text-foreground">{{ teacher.email }}</span>
                            </div>
                            <div class="flex items-center text-base space-x-3">
                                <Phone class="h-5 w-5 text-muted-foreground"/>
                                <span class="text-foreground">{{ teacher.phone }}</span>
                            </div>
                            <div v-if="teacher.branch" class="flex items-center text-base space-x-3">
                                <MapPin class="h-5 w-5 text-muted-foreground"/>
                                <span class="text-foreground">{{ teacher.branch }}</span>
                            </div>
                            <div v-else class="flex items-center text-base space-x-3 text-muted-foreground">
                                <MapPin class="h-5 w-5"/>
                                <span>Filial kiritilmagan</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-xl border bg-card text-card-foreground shadow-sm">
                        <CardHeader class="pb-4 px-6 pt-6">
                            <CardTitle class="text-xl font-semibold">Tezkor Ma'lumot</CardTitle>
                            <CardDescription class="mt-1 text-muted-foreground">Xodimning asosiy identifikatsiyasi va
                                vaqt belgilari
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-3 text-sm p-6">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-muted-foreground flex items-center space-x-2">
                                    <Info class="h-4 w-4"/>
                                    <span>Xodim ID</span>
                                </span>
                                <span class="font-semibold text-foreground">#{{ teacher.id }}</span>
                            </div>
                            <Separator/>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-muted-foreground flex items-center space-x-2">
                                    <CalendarDays class="h-4 w-4"/>
                                    <span>Qo'shilgan Sana</span>
                                </span>
                                <span class="font-semibold text-foreground">{{ formatDate(teacher.created_at) }}</span>
                            </div>
                            <Separator/>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-muted-foreground flex items-center space-x-2">
                                    <Clock class="h-4 w-4"/>
                                    <span>Oxirgi Yangilanish</span>
                                </span>
                                <span class="font-semibold text-foreground">{{ formatDate(teacher.updated_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-xl md:col-span-1 border bg-card text-card-foreground shadow-sm">
                        <CardHeader class="pb-4 px-6 pt-6">
                            <CardTitle class="text-xl font-semibold">Tezkor Harakatlar</CardTitle>
                            <CardDescription class="mt-1 text-muted-foreground">Xodimni boshqarish bo'yicha asosiy
                                funksiyalar
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-3 p-6">
                            <Button variant="outline"
                                    class="w-full justify-start flex items-center space-x-2 py-2.5 hover:bg-accent hover:text-accent-foreground"
                                    @click="handleDeactivate">
                                <Power class="h-4 w-4 text-muted-foreground"/>
                                <span>Xodimni faolsizlantirish</span>
                            </Button>
                            <Button variant="outline"
                                    class="w-full justify-start flex items-center space-x-2 py-2.5 hover:bg-accent hover:text-accent-foreground"
                                    @click="openEditModal()">
                                <Pencil class="h-4 w-4 text-muted-foreground"/>
                                <span>Ma'lumotlarni tahrirlash</span>
                            </Button>
                            <Button variant="destructive"
                                    class="w-full justify-start flex items-center space-x-2 py-2.5"
                                    @click="deleteTeacher()">
                                <Trash2 class="h-4 w-4"/>
                                <span>Xodimni o'chirish</span>
                            </Button>
                            <Separator class="my-4"/>
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center space-x-2 text-sm text-muted-foreground">
                                    <Clock class="h-4 w-4"/>
                                    <span>Holat boshqaruvi</span>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6">Xodim kirishi va ruxsatlarini
                                    boshqarish</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="md:col-span-2 rounded-xl border bg-card text-card-foreground shadow-sm">
                        <CardHeader class="pb-4 px-6 pt-6">
                            <CardTitle class="text-xl font-semibold">Hisob Ma'lumotlari</CardTitle>
                            <CardDescription class="mt-1 text-muted-foreground">Xodimning tizimdagi ro'yxatga olish
                                ma'lumotlari
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 p-6">
                            <div>
                                <p class="text-sm text-muted-foreground mb-1">Elektron pochta</p>
                                <p v-if="teacher.email" class="font-medium text-foreground">{{ teacher.email }}</p>
                                <p v-else class="font-medium text-muted-foreground">Kiritilmagan</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground mb-1">Telefon</p>
                                <p class="font-medium text-foreground">{{ teacher.phone }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground mb-1">Filial</p>
                                <p v-if="teacher.branch" class="font-medium text-foreground">{{ teacher.branch }}</p>
                                <p v-else class="font-medium text-muted-foreground">Kiritilmagan</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground mb-1">Holat</p>
                                <p v-if="teacher.is_active" class="font-medium text-green-600 dark:text-green-400">
                                    Faol</p>
                                <p v-else class="font-medium text-red-600 dark:text-red-400">Faolsiz</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
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
                            <Label for="edit-email">Elektron pochta</Label>
                            <Input
                                id="edit-email"
                                type="email"
                                v-model="editForm.email"
                            />
                            <InputError class="mt-1" :message="editForm.errors.email"/>
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
    </AppLayout>
</template>

<style scoped>
</style>
