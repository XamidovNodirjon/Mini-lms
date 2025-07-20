<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { toast } from 'vue-sonner';

// >>> MUHIM: Bu import yo'lini sizning util.ts faylingiz joylashgan joyga qarab to'g'rilang.
import { formatBalance } from '@/lib/utils'; // Yoki '@/components/lib/util'

const props = defineProps<{
    isOpen: boolean; // Modalning ochiqligini boshqarish uchun prop
    students: Array<{ id: number; full_name: string }>;
    groups: Array<{ id: number; name: string }>;
}>();

const emit = defineEmits(['update:isOpen', 'payment-added']); // Modalni yopish va to'lov qo'shilganini bildirish

const form = useForm({
    student_id: '',
    group_id: '',
    amount: '',
    date: new Date().toISOString().slice(0, 10), // Bugungi sana
    note: '',
});

const studentDebtInfo = ref({
    has_debt: false,
    total_debt_amount: 0,
    message: ''
});

// Modal yopilganda formani va ma'lumotlarni tozalash
watch(() => props.isOpen, (newVal) => {
    if (!newVal) {
        form.reset(); // Formani tozalash
        studentDebtInfo.value = {
            has_debt: false,
            total_debt_amount: 0,
            message: ''
        };
    }
});

// Guruh yoki talaba o'zgarganda qarzdorlikni tekshirish
watch([() => form.student_id, () => form.group_id], async ([newStudentId, newGroupId]) => {
    studentDebtInfo.value = {
        has_debt: false,
        total_debt_amount: 0,
        message: ''
    };
    form.amount = '';

    if (newStudentId && newGroupId) {
        try {
            const response = await fetch('/api/payments/get-student-group-debt', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    student_id: newStudentId,
                    group_id: newGroupId
                }),
            });

            if (!response.ok) {
                let errorMessage = `API so'rovida xato yuz berdi: ${response.status} ${response.statusText}`;
                try {
                    const errorData = await response.json();
                    if (errorData && errorData.message) {
                        errorMessage = errorData.message;
                    } else if (errorData && typeof errorData === 'object' && Object.keys(errorData).length > 0) {
                        errorMessage = Object.values(errorData).flat().join(', ');
                    }
                } catch (jsonError) {
                    console.error("JSON javobini tahlil qilishda xato:", jsonError);
                }
                throw new Error(errorMessage);
            }

            const data = await response.json();
            const parsedTotalDebtAmount = parseFloat(data.total_debt_amount) || 0;

            studentDebtInfo.value = {
                has_debt: data.has_debt,
                total_debt_amount: parsedTotalDebtAmount,
                message: data.message
            };

            if (data.has_debt) {
                form.amount = parsedTotalDebtAmount.toFixed(2);
            } else {
                form.amount = '';
            }

        } catch (error: any) {
            console.error('Qarzdorlikni yuklashda xato:', error);
            toast.error('Xatolik!', {
                description: error.message || 'Qarzdorlikni yuklashda noma\'lum xato yuz berdi.',
                duration: 5000,
            });
            studentDebtInfo.value = {
                has_debt: false,
                total_debt_amount: 0,
                message: error.message || 'Qarzdorlikni yuklashda xato yuz berdi.'
            };
            form.amount = '';
        }
    }
}, { immediate: true });

const maxAmount = computed(() => {
    return studentDebtInfo.value.has_debt ? studentDebtInfo.value.total_debt_amount : undefined;
});

const submit = () => {
    if (studentDebtInfo.value.has_debt && parseFloat(form.amount) > studentDebtInfo.value.total_debt_amount) {
        toast.warning('Diqqat!', {
            description: `Kiritilgan summa qarzdorlikdan (${formatBalance(studentDebtInfo.value.total_debt_amount)}) oshib ketmasligi kerak.`,
            duration: 5000,
        });
        return;
    }
    if (!studentDebtInfo.value.has_debt && parseFloat(form.amount) > 0) {
        toast.info('Ma\'lumot!', {
            description: `Bu o'quvchining ushbu guruh uchun qarzdorligi yo'q. To'lov balansga qo'shiladi.`,
            duration: 5000,
        });
    }

    form.post(route('payments.store'), {
        onSuccess: () => {
            toast.success('Muvaffaqiyatli', {
                description: 'To\'lov muvaffaqiyatli qo\'shildi.',
                duration: 3000,
            });
            emit('payment-added'); // To'lov qo'shilganini Index sahifasiga bildirish
        },
        onError: (errors) => {
            console.error('To\'lov qo\'shishda xato:', errors);
            toast.error('Xatolik!', {
                description: 'To\'lovni qo\'shishda xato yuz berdi. Iltimos, ma\'lumotlarni tekshiring.',
                duration: 5000,
            });
        }
    });
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="emit('update:isOpen', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Yangi to'lov qo'shish</DialogTitle>
                <DialogDescription>
                    O'quvchining to'lov ma'lumotlarini kiriting.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4 py-4">
                <div>
                    <Label for="group_id">Guruhni tanlang</Label>
                    <Select v-model="form.group_id">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Guruhni tanlang" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="group in props.groups" :key="group.id" :value="group.id.toString()">
                                {{ group.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.group_id" class="mt-2" />
                </div>

                <div>
                    <Label for="student_id">O'quvchini tanlang</Label>
                    <Select v-model="form.student_id">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="O'quvchini tanlang" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="student in props.students" :key="student.id" :value="student.id.toString()">
                                {{ student.full_name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.student_id" class="mt-2" />
                </div>

                <div v-if="studentDebtInfo.message">
                    <p class="text-sm" :class="{ 'text-green-600': !studentDebtInfo.has_debt, 'text-red-600': studentDebtInfo.has_debt }">
                        {{ studentDebtInfo.message }}
                    </p>
                </div>

                <div>
                    <Label for="amount">Summa</Label>
                    <Input
                        id="amount"
                        type="number"
                        step="0.01"
                        v-model="form.amount"
                        :min="0.01"
                        :max="maxAmount"
                        class="mt-1 block w-full"
                        autocomplete="off"
                    />
                    <InputError :message="form.errors.amount" class="mt-2" />
                </div>

                <div>
                    <Label for="date">Sana</Label>
                    <Input
                        id="date"
                        type="date"
                        v-model="form.date"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.date" class="mt-2" />
                </div>

                <div>
                    <Label for="note">Izoh (ixtiyoriy)</Label>
                    <Textarea
                        id="note"
                        v-model="form.note"
                        rows="3"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.note" class="mt-2" />
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="emit('update:isOpen', false)">Bekor qilish</Button>
                    <Button type="submit" :disabled="form.processing">
                        To'lovni qo'shish
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
