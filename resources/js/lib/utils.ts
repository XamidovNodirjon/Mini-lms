// resources/js/components/lib/util.ts (yoki sizning loyihangizdagi to'g'ri yo'l)

import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

// Pul miqdorini formatlash funksiyasi
export function formatBalance(amount: number | string): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    if (isNaN(num)) {
        return '0.00 UZS';
    }
    return new Intl.NumberFormat('uz-UZ', {
        style: 'currency',
        currency: 'UZS',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num);
}

// Sanani formatlash funksiyasi (dd.mm.yyyy)
export function formatDate(dateString: string): string {
    if (!dateString) {
        return '-';
    }
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            throw new Error("Invalid date string");
        }
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}.${month}.${year}`;
    } catch (e) {
        console.error("Sanani formatlashda xato:", e);
        return dateString;
    }
}
