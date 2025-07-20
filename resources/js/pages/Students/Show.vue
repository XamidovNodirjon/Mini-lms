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
import { Separator } from "@/components/ui/separator";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from '@/Components/InputError.vue';
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Calendar } from '@/components/ui/calendar';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@/components/ui/command";
import { Badge } from "@/components/ui/badge";

import { cn } from '@/lib/utils';

// --- Table Components (Yangi) ---
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";


// --- Ikonkalar ---
import {
    ArrowLeft,
    Edit,
    Trash2,
    UserRound,
    Phone,
    CalendarDays,
    Clock,
    Info,
    Pencil,
    Power,
    DollarSign,
    Save,
    Check,
    ChevronsUpDown,
    Calendar as CalendarIcon,
} from 'lucide-vue-next';

const props = defineProps<{
    student: {
        id: number;
        full_name: string;
        phone: string;
        birth_date?: string;
        balance?: number;
        created_at: string;
        updated_at: string;
        groups: Array<{ id: number; name: string }>;
        payments: Array<{ // Payments prop'i qo'shildi
            id: number;
            amount: number;
            type: string; // 'debt' yoki 'balance'
            date: string;
            note?: string;
            created_at: string;
            updated_at: string;
        }>;
        debts: Array<{ // Debts prop'i qo'shildi
            id: number;
            amount: number;
            month: string; // 'YYYY-MM-DD' yoki 'YYYY-MM'
            status: string; // 'pending', 'partial', 'paid'
            paid_amount: number;
            created_at: string;
            updated_at: string;
            group?: { id: number; name: string; }; // Qarzdorlik guruhiga bog'liqlik
        }>;
    };
    groups: Array<{
        id: number;
        name: string;
    }>;
}>();

// --- Breadcrumbs ---
const breadcrumbs = computed(() => [
    {
        title: 'O\'quvchilar',
        href: '/students',
    },
    {
        title: props.student.full_name,
        href: `/students/${props.student.id}`,
    },
]);

const deleteForm = useForm({});

const deleteStudent = (id: number) => {
    if (confirm('Haqiqatan ham bu o\'quvchini o\'chirmoqchimisiz?')) {
        deleteForm.delete(route('students.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                window.location.href = route('students.index');
            },
            onError: (errors) => {
                alert('O\'quvchini o\'chirishda xatolik yuz berdi!');
                console.error(errors);
            }
        });
    }
};

const formatDate = (dateString?: string) => {
    if (!dateString) return 'Kiritilmagan';
    // date-fns formatni ham ishlating
    try {
        return format(new Date(dateString), 'dd.MM.yyyy');
    } catch (e) {
        return new Date(dateString).toLocaleDateString('uz-UZ', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        });
    }
};

const formatBalance = (balance?: number) => {
    if (balance === undefined || balance === null) return '0 UZS';
    return `${balance.toLocaleString('uz-UZ')} UZS`;
};

// --- Tahrirlash Modali Logikasi ---
const isEditModalOpen = ref(false);

const editForm = useForm({
    _method: 'put',
    full_name: '',
    phone: '',
    birth_date: '' as string,
    balance: 0,
    group_ids: [] as number[],
});

const openEditModal = () => {
    editForm.full_name = props.student.full_name;
    editForm.phone = props.student.phone;
    editForm.birth_date = props.student.birth_date || '';
    editForm.balance = props.student.balance || 0;
    editForm.group_ids = props.student.groups ? props.student.groups.map(group => group.id) : [];

    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEditForm = () => {
    editForm.post(route('students.update', props.student.id), {
        onSuccess: () => {
            alert('O\'quvchi ma\'lumotlari muvaffaqiyatli yangilandi.');
            isEditModalOpen.value = false;
        },
        onError: (errors) => {
            alert('Ma\'lumotlarni yangilashda xatolik yuz berdi! Iltimos, ma\'lumotlarni tekshiring.');
            console.error(errors);
        },
        preserveScroll: true,
    });
};

// --- Guruh tanlash Popoveri va Calendar Popoveri (Edit modal uchun) ---
const openGroupSelectEdit = ref(false);
const openBirthDateCalendarEdit = ref(false);

const getGroupNameById = (id: number) => {
    return props.groups.find(group => group.id === id)?.name || '';
};

const isGroupSelectedEdit = (groupId: number) => {
    return editForm.group_ids.includes(groupId);
};

const toggleGroupSelectionEdit = (groupId: number) => {
    if (isGroupSelectedEdit(groupId)) {
        editForm.group_ids = editForm.group_ids.filter(id => id !== groupId);
    } else {
        editForm.group_ids.push(groupId);
    }
};

const selectedGroupNamesEdit = computed(() => {
    if (editForm.group_ids.length === 0) {
        return "Guruhlarni tanlang...";
    }
    const names = editForm.group_ids.map(id => getGroupNameById(id));
    if (names.length > 2) {
        return `${names.slice(0, 2).join(', ')} va yana ${names.length - 2} ta`;
    }
    return names.join(', ');
});

// Edit modali uchun tug'ilgan sana mantiqi
const birthDateDisplayEdit = computed(() => {
    return editForm.birth_date ? format(new Date(editForm.birth_date), 'PPP') : 'Sanani tanlang';
});

const selectBirthDateEdit = (date: Date) => {
    editForm.birth_date = format(date, 'yyyy-MM-dd');
    openBirthDateCalendarEdit.value = false;
};

// --- Transactions (To'lovlar va Qarzdorliklar) Logikasi ---
const combinedTransactions = computed(() => {
    const transactions = [];

    // Payments (To'lovlar)
    props.student.payments.forEach(payment => {
        transactions.push({
            id: `P-${payment.id}`, // Unique ID for combining
            type: 'payment',
            date: payment.date,
            amount: payment.amount,
            description: payment.type === 'debt' ? 'Qarz uchun to\'lov' : 'Balansga qo\'shildi',
            status: payment.type === 'debt' ? 'Yopilgan / Qisman yopilgan' : 'Balans',
            paid_amount: payment.amount, // To'lovda to'langan summa aynan amount bo'ladi
            remaining_amount: 0,
            group_name: '', // To'lovlarda guruh nomi bevosita bo'lmasligi mumkin, agar kerak bo'lsa Payment modeliga group_id qo'shish kerak
            sortableDate: new Date(payment.date),
        });
    });

    // Debts (Qarzdorliklar)
    props.student.debts.forEach(debt => {
        transactions.push({
            id: `D-${debt.id}`, // Unique ID for combining
            type: 'debt',
            date: debt.month, // Qarzdorlik oyini sana sifatida ishlatamiz
            amount: debt.amount,
            description: `Guruh: ${debt.group?.name || 'Noma\'lum'} uchun qarz`,
            status: debt.status === 'paid' ? 'To\'langan' : (debt.status === 'partial' ? 'Qisman to\'langan' : 'Kutilmoqda'),
            paid_amount: debt.paid_amount,
            remaining_amount: debt.amount - debt.paid_amount,
            group_name: debt.group?.name || 'Noma\'lum',
            sortableDate: new Date(debt.month),
        });
    });

    // Sanaga qarab saralash (eng yangisi birinchi)
    return transactions.sort((a, b) => b.sortableDate.getTime() - a.sortableDate.getTime());
});

</script>

<template>
    <Head :title="`O'quvchi: ${student.full_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-5 flex flex-col md:flex-row items-start md:items-center justify-between w-full space-y-3 md:space-y-0">
                    <div class="flex items-center space-x-2">
                        <Link :href="route('students.index')"
                              class="text-muted-foreground hover:text-foreground flex items-center space-x-1 transition-colors">
                            <ArrowLeft class="h-4 w-4"/>
                            <span>O'quvchilarga qaytish</span>
                        </Link>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button variant="outline" class="flex items-center space-x-1 px-3 py-2 text-sm" @click="openEditModal">
                            <Pencil class="h-4 w-4" />
                            <span>Tahrirlash</span>
                        </Button>
                        <Button variant="destructive" class="flex items-center space-x-1 px-3 py-2 text-sm" @click="deleteStudent(student.id)">
                            <Trash2 class="h-4 w-4" />
                            <span>O'chirish</span>
                        </Button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <Card class="md:col-span-2 rounded-xl border bg-card text-card-foreground shadow-sm">
                        <CardHeader class="pb-4 px-6 pt-6">
                            <div class="flex items-center space-x-4">
                                <UserRound class="h-10 w-10 text-primary" />
                                <div class="flex flex-col">
                                    <CardTitle class="text-3xl font-bold flex items-center">
                                        {{ student.full_name }}
                                    </CardTitle>
                                    <CardDescription class="mt-1 text-muted-foreground">O'quvchi asosiy ma'lumotlari</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="grid gap-4 p-6">
                            <Separator/>
                            <div class="flex items-center text-base space-x-3">
                                <Phone class="h-5 w-5 text-muted-foreground" />
                                <span class="text-foreground">{{ student.phone }}</span>
                            </div>
                            <div v-if="student.birth_date" class="flex items-center text-base space-x-3">
                                <CalendarDays class="h-5 w-5 text-muted-foreground" />
                                <span class="text-foreground">{{ formatDate(student.birth_date) }}</span>
                            </div>
                            <div v-if="student.balance !== undefined && student.balance !== null" class="flex items-center text-base space-x-3">
                                <DollarSign class="h-5 w-5 text-muted-foreground" />
                                <span class="text-foreground">{{ formatBalance(student.balance) }}</span>
                            </div>
                            <div class="flex items-start text-base space-x-3">
                                <Info class="h-5 w-5 text-muted-foreground mt-0.5" /> <div class="flex flex-col">
                                <span class="font-semibold text-foreground mb-1">Guruhlar:</span>
                                <div class="flex flex-wrap gap-2">
                                    <Badge v-for="group in student.groups" :key="group.id" variant="secondary" class="bg-muted text-muted-foreground">
                                        {{ group.name }}
                                    </Badge>
                                    <span v-if="!student.groups || student.groups.length === 0" class="text-muted-foreground text-sm">Guruhsiz</span>
                                </div>
                            </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-xl border bg-card text-card-foreground shadow-sm">
                        <CardHeader class="pb-4 px-6 pt-6">
                            <CardTitle class="text-xl font-semibold">Tezkor Ma'lumot</CardTitle>
                            <CardDescription class="mt-1 text-muted-foreground">O'quvchining asosiy identifikatsiyasi va
                                vaqt belgilari
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-3 text-sm p-6">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-muted-foreground flex items-center space-x-2">
                                    <Info class="h-4 w-4"/>
                                    <span>O'quvchi ID</span>
                                </span>
                                <span class="font-semibold text-foreground">#{{ student.id }}</span>
                            </div>
                            <Separator/>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-muted-foreground flex items-center space-x-2">
                                    <CalendarDays class="h-4 w-4"/>
                                    <span>Qo'shilgan Sana</span>
                                </span>
                                <span class="font-semibold text-foreground">{{ formatDate(student.created_at) }}</span>
                            </div>
                            <Separator/>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-muted-foreground flex items-center space-x-2">
                                    <Clock class="h-4 w-4"/>
                                    <span>Oxirgi Yangilanish</span>
                                </span>
                                <span class="font-semibold text-foreground">{{ formatDate(student.updated_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="md:col-span-2 rounded-xl border bg-card text-card-foreground shadow-sm">
                        <CardHeader class="pb-4 px-6 pt-6">
                            <CardTitle class="text-xl font-semibold">Qo'shimcha Ma'lumotlar</CardTitle>
                            <CardDescription class="mt-1 text-muted-foreground">O'quvchining to'liq ma'lumotlari</CardDescription>
                        </CardHeader>
                        <CardContent class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 p-6">
                            <div>
                                <p class="text-sm text-muted-foreground mb-1">To'liq Ism</p>
                                <p class="font-medium text-foreground">{{ student.full_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground mb-1">Telefon Raqam</p>
                                <p class="font-medium text-foreground">{{ student.phone }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground mb-1">Tug'ilgan Sana</p>
                                <p class="font-medium text-foreground">{{ formatDate(student.birth_date) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground mb-1">Balans</p>
                                <p class="font-medium text-foreground">{{ formatBalance(student.balance) }}</p>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <p class="text-sm text-muted-foreground mb-1">Guruhlar</p>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <Badge v-for="group in student.groups" :key="group.id" variant="outline" class="bg-muted text-muted-foreground border-border">
                                        {{ group.name }}
                                    </Badge>
                                    <span v-if="!student.groups || student.groups.length === 0" class="text-muted-foreground text-sm">Guruhsiz</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-xl md:col-span-1 border bg-card text-card-foreground shadow-sm">
                        <CardHeader class="pb-4 px-6 pt-6">
                            <CardTitle class="text-xl font-semibold">Tezkor Harakatlar</CardTitle>
                            <CardDescription class="mt-1 text-muted-foreground">O'quvchini boshqarish bo'yicha asosiy
                                funksiyalar
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-3 p-6">
                            <Button variant="outline"
                                    class="w-full justify-start flex items-center space-x-2 py-2.5 hover:bg-accent hover:text-accent-foreground"
                                    @click="deleteStudent(student.id)"> <Power class="h-4 w-4 text-muted-foreground"/>
                                <span>O'quvchini faolsizlantirish (Kelajakda)</span> </Button>
                            <Button variant="outline"
                                    class="w-full justify-start flex items-center space-x-2 py-2.5 hover:bg-accent hover:text-accent-foreground"
                                    @click="openEditModal()">
                                <Pencil class="h-4 w-4 text-muted-foreground"/>
                                <span>Ma'lumotlarni tahrirlash</span>
                            </Button>
                            <Button variant="destructive"
                                    class="w-full justify-start flex items-center space-x-2 py-2.5"
                                    @click="deleteStudent(student.id)">
                                <Trash2 class="h-4 w-4"/>
                                <span>O'quvchini o'chirish</span>
                            </Button>
                            <Separator class="my-4"/>
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center space-x-2 text-sm text-muted-foreground">
                                    <Clock class="h-4 w-4"/>
                                    <span>Qo'shimcha boshqaruv</span>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6">To'lovlar yoki davomatni boshqarish</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="md:col-span-3 rounded-xl border bg-card text-card-foreground shadow-sm mt-6">
                        <CardHeader class="pb-4 px-6 pt-6">
                            <CardTitle class="text-xl font-semibold">To'lovlar va Qarzdorliklar Tarixi</CardTitle>
                            <CardDescription class="mt-1 text-muted-foreground">O'quvchining barcha pul harakatlari
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="p-6">
                            <div v-if="combinedTransactions.length > 0" class="overflow-x-auto">
                                <Table class="min-w-full">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead class="w-[100px]">Sana</TableHead>
                                            <TableHead>Turi</TableHead>
                                            <TableHead>Izoh</TableHead>
                                            <TableHead>Guruh</TableHead>
                                            <TableHead class="text-right">Summa</TableHead>
                                            <TableHead class="text-right">To'langan</TableHead>
                                            <TableHead class="text-right">Qoldiq</TableHead>
                                            <TableHead>Holati</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="transaction in combinedTransactions" :key="transaction.id">
                                            <TableCell class="font-medium">{{ formatDate(transaction.date) }}</TableCell>
                                            <TableCell>
                                                <Badge :variant="transaction.type === 'payment' ? 'default' : 'secondary'">
                                                    {{ transaction.type === 'payment' ? 'To\'lov' : 'Qarz' }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>{{ transaction.description }}</TableCell>
                                            <TableCell>{{ transaction.group_name || '-' }}</TableCell>
                                            <TableCell class="text-right font-semibold">
                                                {{ formatBalance(transaction.amount) }}
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <span v-if="transaction.type === 'debt'">{{ formatBalance(transaction.paid_amount) }}</span>
                                                <span v-else>{{ formatBalance(transaction.amount) }}</span>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <span v-if="transaction.type === 'debt'">{{ formatBalance(transaction.remaining_amount) }}</span>
                                                <span v-else>0 UZS</span>
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    :variant="
                                                        transaction.status === 'To\'langan' || transaction.status === 'Yopilgan / Qisman yopilgan' || transaction.status === 'Balans'
                                                            ? 'success'
                                                            : (transaction.status === 'Qisman to\'langan' ? 'warning' : 'destructive')
                                                    "
                                                >
                                                    {{ transaction.status }}
                                                </Badge>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div v-else class="text-center text-muted-foreground py-8">
                                <p>Bu o'quvchi uchun hech qanday to'lov yoki qarzdorlik topilmadi.</p>
                            </div>
                        </CardContent>
                    </Card>

                </div>
            </div>
        </div>

        <Dialog v-model:open="isEditModalOpen">
            <DialogContent class="sm:max-w-[500px] bg-background text-foreground border-border">
                <DialogHeader>
                    <DialogTitle>O'quvchini Tahrirlash</DialogTitle>
                    <DialogDescription>
                        O'quvchining ma'lumotlarini yangilang.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEditForm">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="edit-full_name">To'liq Ism</Label>
                            <Input
                                id="edit-full_name"
                                type="text"
                                v-model="editForm.full_name"
                                autofocus
                            />
                            <InputError class="mt-1" :message="editForm.errors.full_name"/>
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
                            <Label for="edit-birth_date">Tug'ilgan Sana</Label>
                            <Popover v-model:open="openBirthDateCalendarEdit">
                                <PopoverTrigger as-child>
                                    <Button
                                        :variant="'outline'"
                                        :class="cn(
                                            'w-full justify-start text-left font-normal',
                                            !editForm.birth_date && 'text-muted-foreground',
                                        )"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ birthDateDisplayEdit }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar
                                        v-model:selected="editForm.birth_date"
                                        @update:selected="selectBirthDateEdit"
                                        :initial-focus="true"
                                        mode="single"
                                        :from-date="new Date(1900, 0, 1)"
                                        :to-date="new Date()"
                                    />
                                </PopoverContent>
                            </Popover>
                            <InputError class="mt-1" :message="editForm.errors.birth_date"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-balance">Balans (UZS)</Label>
                            <Input
                                id="edit-balance"
                                type="number"
                                v-model="editForm.balance"
                                min="0"
                                step="0.01"
                            />
                            <InputError class="mt-1" :message="editForm.errors.balance"/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-groups">Guruhlar</Label>
                            <Popover v-model:open="openGroupSelectEdit">
                                <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        role="combobox"
                                        :aria-expanded="openGroupSelectEdit"
                                        class="w-full justify-between hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        {{ selectedGroupNamesEdit }}
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
                                                    @select="toggleGroupSelectionEdit(group.id)"
                                                    class="cursor-pointer"
                                                >
                                                    <Check
                                                        :class="cn(
                                                            'mr-2 h-4 w-4',
                                                            isGroupSelectedEdit(group.id) ? 'opacity-100' : 'opacity-0'
                                                        )"
                                                    />
                                                    {{ group.name }}
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            <InputError class="mt-1" :message="editForm.errors.group_ids" />
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
