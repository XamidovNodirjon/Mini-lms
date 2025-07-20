<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';
import {ref, watch, computed} from 'vue';
import {debounce} from 'lodash';
import { format } from 'date-fns';

// --- UI Komponentlari ---
import {Button} from "@/components/ui/button";
import {Input} from "@/components/ui/input";
import {Card, CardContent, CardHeader, CardTitle, CardDescription} from '@/components/ui/card';
import {Badge} from "@/components/ui/badge";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";
import {
    Dialog, // MUHIM: Dialog komponenti bu yerda import qilingan
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import InputError from '@/Components/InputError.vue';

// --- Shadcn UI Command/Combobox komponentlari (Guruh tanlash uchun) ---
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from "@/components/ui/command";
import { Calendar } from '@/components/ui/calendar';
import { cn } from '@/lib/utils';

// --- Ikonkalar ---
import {
    Edit, Trash2, Eye,
    ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight,
    PlusCircle,
    ChevronsUpDown,
    Save,
    Check,
    Calendar as CalendarIcon,
} from 'lucide-vue-next';

// --- Yangi: StudentCreateModal komponentini import qilish ---
import StudentCreateModal from '@/Components/StudentCreateModal.vue'; // Bu yerda to'g'ri import qilingan

// --- Propsni aniqlash ---
const props = defineProps<{
    students: {
        data: Array<{
            id: number;
            full_name: string;
            phone: string;
            birth_date: string | null;
            balance: number;
            created_at: string;
            groups: Array<{ id: number; name: string }>;
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
    groups: Array<{
        id: number;
        name: string;
    }>;
}>();

// --- Qidiruv logikasi ---
const search = ref(props.filters.search || '');

const performSearch = debounce(() => {
    window.location.href = route('students.index', {search: search.value});
}, 300);

watch(search, performSearch);

// --- O'quvchini o'chirish logikasi ---
const deleteForm = useForm({});

const deleteStudent = (id: number) => {
    if (confirm('Haqiqatan ham bu o\'quvchini o\'chirmoqchimisiz?')) {
        deleteForm.delete(route('students.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                alert('O\'quvchi muvaffaqiyatli o\'chirildi.');
                if (props.students.data.length === 0 && props.students.current_page > 1) {
                    window.location.href = props.students.links[0].url || route('students.index');
                }
            },
            onError: (errors) => {
                alert('O\'quvchini o\'chirishda xatolik yuz berdi!');
                console.error(errors);
            }
        });
    }
};

// --- Formatlash yordamchi funksiyalari ---
const formatBalance = (value: number) => {
    return new Intl.NumberFormat('uz-UZ', {style: 'currency', currency: 'UZS'}).format(value);
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Noma\'lum';
    return new Date(dateString).toLocaleDateString('uz-UZ');
};

// --- Breadcrumbs ---
const breadcrumbs = [
    {
        title: 'O\'quvchilar',
        href: '/students',
    },
];

// --- Tahrirlash Modali Logikasi ---
const isEditModalOpen = ref(false);
const currentEditingStudent = ref<typeof props.students.data[0] | null>(null);

const editForm = useForm({
    _method: 'put',
    full_name: '',
    phone: '',
    birth_date: '' as string,
    balance: 0,
    group_ids: [] as number[],
});

const openEditModal = (student: typeof props.students.data[0]) => {
    currentEditingStudent.value = student;

    editForm.full_name = student.full_name;
    editForm.phone = student.phone;
    editForm.birth_date = student.birth_date || '';
    editForm.balance = student.balance;
    editForm.group_ids = student.groups.map(group => group.id);

    editForm.clearErrors();
    isEditModalOpen.value = true; // Bu yerda modal ochiladi
};

const submitEditForm = () => {
    if (!currentEditingStudent.value) return;

    editForm.post(route('students.update', currentEditingStudent.value.id), {
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

// --- Yangi O'quvchi Qo'shish Modali Logikasi ---
const isCreateModalOpen = ref(false); // Bu `ref` yaratish modalining ochiq/yopiq holatini boshqaradi

const openCreateModal = () => {
    isCreateModalOpen.value = true; // Tugma bosilganda `true` ga o'rnatiladi, shu sababli modal ochiladi
};

const handleCreateSuccess = () => {
    // StudentCreateModal komponentidan muvaffaqiyatli yuborilganda chaqiriladi
    // Inertia.js avtomatik sahifa propslarini yangilaganligi sababli, bu yerda qo'shimcha refresh shart emas.
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
</script>

<template>
    <Head title="O'quvchilar"/>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class=" mb-5 flex flex-col md:flex-row items-start md:items-center justify-between w-full space-y-4 md:space-y-0 md:space-x-4">
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight dark:text-gray-200">
                        O'quvchilar Ro'yxati
                    </h2>
                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <Input
                            type="text"
                            v-model="search"
                            placeholder="O'quvchi qidirish..."
                            class="w-full md:w-64"
                        />
                        <Button variant="default"
                                class="bg-primary text-primary-foreground hover:bg-primary/90 whitespace-nowrap"
                                @click="openCreateModal">
                            <PlusCircle class="h-4 w-4 mr-2"/>
                            Yangi O'quvchi Qo'shish
                        </Button>
                    </div>
                </div>
                <Card class="rounded shadow-xl bg-background text-foreground">
                    <CardContent class="p-0">
                        <div class="overflow-x-auto rounded-b-xl border border-border">
                            <table class="min-w-full divide-y">
                                <thead class="bg-muted text-muted-foreground">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider">ID
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider">To'liq
                                        Ism
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider">Telefon
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider">
                                        Tug'ilgan Sana
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider">Balans
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider">
                                        Guruhlar
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider">
                                        Qo'shilgan Sana
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium tracking-wider">
                                        Amallar
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-background text-foreground divide-y divide-border">
                                <tr v-if="!students.data.length">
                                    <td colspan="8" class="px-6 py-8 text-center text-muted-foreground text-base">Hech
                                        qanday o'quvchi topilmadi.
                                    </td>
                                </tr>
                                <tr v-for="student in students.data" :key="student.id"
                                    class="hover:bg-accent hover:text-accent-foreground transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ student.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.full_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.phone }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{
                                            formatDate(student.birth_date)
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{
                                            formatBalance(student.balance)
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div v-if="student.groups && student.groups.length">
                                            <div v-if="student.groups.length <= 3" class="flex flex-wrap gap-1">
                                                <Badge v-for="group in student.groups" :key="group.id"
                                                       variant="outline">
                                                    {{ group.name }}
                                                </Badge>
                                            </div>
                                            <div v-else class="flex flex-wrap gap-1 items-center">
                                                <Popover>
                                                    <PopoverTrigger as-child>
                                                        <Button variant="outline" size="sm"
                                                                class="h-7 px-3 py-1 text-sm flex items-center justify-between gap-1">
                                                            <span>Guruhlar ({{ student.groups.length }})</span>
                                                            <ChevronsUpDown class="h-3 w-3 opacity-50 ml-1"/>
                                                        </Button>
                                                    </PopoverTrigger>
                                                    <PopoverContent
                                                        class="w-auto p-2 min-w-[150px] max-h-40 overflow-y-auto bg-popover text-popover-foreground border border-border">
                                                        <div class="space-y-1">
                                                            <p class="text-sm font-semibold mb-1">Barcha Guruhlar:</p>
                                                            <div class="flex flex-wrap gap-1">
                                                                <Badge v-for="group in student.groups" :key="group.id"
                                                                       variant="secondary">
                                                                    {{ group.name }}
                                                                </Badge>
                                                            </div>
                                                        </div>
                                                    </PopoverContent>
                                                </Popover>
                                            </div>
                                        </div>
                                        <span v-else class="text-muted-foreground">Guruhsiz</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{
                                            formatDate(student.created_at)
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center space-x-2">
                                            <Link :href="route('students.show', student.id)">
                                                <Button variant="outline" size="icon">
                                                    <Eye class="h-4 w-4 text-blue-500 dark:text-blue-300"/>
                                                </Button>
                                            </Link>
                                            <Button variant="outline" size="icon" @click="openEditModal(student)">
                                                <Edit class="h-4 w-4 text-yellow-500 dark:text-yellow-300"/>
                                            </Button>
                                            <Button variant="destructive" size="icon"
                                                    @click="deleteStudent(student.id)">
                                                <Trash2 class="h-4 w-4"/>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="students.last_page > 1" class="flex justify-center items-center mt-6 space-x-2">
                            <template v-for="(link, index) in students.links" :key="index">
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

        <Dialog v-model:open="isCreateModalOpen">
            <StudentCreateModal
                :groups="props.groups"
                v-model:modelValue="isCreateModalOpen"
                @success="handleCreateSuccess"
            />
        </Dialog>

    </AppLayout>
</template>
