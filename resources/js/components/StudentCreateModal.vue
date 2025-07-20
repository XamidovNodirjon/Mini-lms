<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { format } from 'date-fns';

// --- UI Komponentlari ---
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from '@/Components/InputError.vue';
import {
    DialogContent, // Faqat DialogContent va ichki elementlar
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/components/ui/dialog";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@/components/ui/command";
import { Calendar } from '@/components/ui/calendar';
import { cn } from '@/lib/utils';

// --- Ikonkalar ---
import { Save, Check, ChevronsUpDown, Calendar as CalendarIcon } from 'lucide-vue-next';

// --- Propsni aniqlash ---
const props = defineProps<{
    groups: Array<{
        id: number;
        name: string;
    }>;
    // `modelValue` prop'i modalning ochiq/yopiq holatini parent komponentdan qabul qiladi
    modelValue: boolean;
}>();

// --- Emits (Parent komponent bilan aloqa) ---
// `update:modelValue` orqali modal holatini parentga qaytaradi
const emit = defineEmits(['update:modelValue', 'success']);

// --- Formani aniqlash ---
const form = useForm({
    full_name: '',
    phone: '',
    birth_date: '' as string,
    balance: 0,
    group_ids: [] as number[],
});

// --- Popover holatlari ---
const openGroupSelect = ref(false);
const openBirthDateCalendar = ref(false);

// --- Guruh tanlashga yordamchi funksiyalar ---
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
    const names = form.group_ids.map(id => getGroupNameById(id));
    if (names.length > 2) {
        return `${names.slice(0, 2).join(', ')} va yana ${names.length - 2} ta`;
    }
    return names.join(', ');
});

// --- Tug'ilgan sana mantiqi ---
const birthDateDisplay = computed(() => {
    return form.birth_date ? format(new Date(form.birth_date), 'PPP') : 'Sanani tanlang';
});

const selectBirthDate = (date: Date) => {
    form.birth_date = format(date, 'yyyy-MM-dd');
    openBirthDateCalendar.value = false;
};

// --- Formani yuborish ---
const submit = () => {
    form.post(route('students.store'), {
        onSuccess: () => {
            alert('O\'quvchi muvaffaqiyatli qo\'shildi.');
            form.reset();
            openGroupSelect.value = false;
            openBirthDateCalendar.value = false;
            emit('update:modelValue', false); // Modalni yopish uchun parentga xabar berish
            emit('success'); // Parentga muvaffaqiyat haqida xabar berish
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
    <DialogContent class="sm:max-w-[500px] bg-background text-foreground border-border">
        <DialogHeader>
            <DialogTitle>Yangi O'quvchi Qo'shish</DialogTitle>
            <DialogDescription>
                Yangi o'quvchi ma'lumotlarini kiriting.
            </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submit">
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="create-full_name">To'liq Ism</Label>
                    <Input
                        id="create-full_name"
                        type="text"
                        v-model="form.full_name"
                        placeholder="Ali Valiyev"
                        required
                        autofocus
                    />
                    <InputError class="mt-1" :message="form.errors.full_name"/>
                </div>

                <div class="grid gap-2">
                    <Label for="create-phone">Telefon Raqam</Label>
                    <Input
                        id="create-phone"
                        type="text"
                        v-model="form.phone"
                        placeholder="+998901234567"
                        required
                    />
                    <InputError class="mt-1" :message="form.errors.phone"/>
                </div>

                <div class="grid gap-2">
                    <Label for="create-birth_date">Tug'ilgan Sana</Label>
                    <Popover v-model:open="openBirthDateCalendar">
                        <PopoverTrigger as-child>
                            <Button
                                :variant="'outline'"
                                :class="cn(
                                    'w-full justify-start text-left font-normal',
                                    !form.birth_date && 'text-muted-foreground',
                                )"
                            >
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                {{ birthDateDisplay }}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <Calendar
                                v-model:selected="form.birth_date"
                                @update:selected="selectBirthDate"
                                :initial-focus="true"
                                mode="single"
                                :from-date="new Date(1900, 0, 1)"
                                :to-date="new Date()"
                            />
                        </PopoverContent>
                    </Popover>
                    <InputError class="mt-1" :message="form.errors.birth_date"/>
                </div>

                <div class="grid gap-2">
                    <Label for="create-balance">Balans (UZS)</Label>
                    <Input
                        id="create-balance"
                        type="number"
                        v-model="form.balance"
                        min="0"
                        step="0.01"
                    />
                    <InputError class="mt-1" :message="form.errors.balance"/>
                </div>

                <div class="grid gap-2">
                    <Label for="create-groups">Guruhlar</Label>
                    <Popover v-model:open="openGroupSelect">
                        <PopoverTrigger as-child>
                            <Button
                                variant="outline"
                                role="combobox"
                                :aria-expanded="openGroupSelect"
                                class="w-full justify-between hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                {{ selectedGroupNames }}
                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50"/>
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-[var(--radix-popover-trigger-width)] p-0">
                            <Command>
                                <CommandInput placeholder="Guruh qidirish..."/>
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
                    <InputError class="mt-1" :message="form.errors.group_ids"/>
                </div>
            </div>
            <DialogFooter>
                <Button type="submit" :disabled="form.processing">
                    <Save class="h-4 w-4 mr-2" />
                    Saqlash
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</template>
