import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Book {
    id: number;
    title: string;
    // You might add other properties here later if needed
    // abbreviation?: string;
    // book_number?: number;
}

export interface Verse {
    id: number;
    text: string;
    chapter: number;
    verse_number: number;
    book: Book; // A verse has one book
}

// =====================================================================
//  THE NEWLY ADDED TYPE IS HERE
// =====================================================================
export interface UserVerseProgress {
    id: number;
    status: 'new' | 'learning' | 'reviewing' | 'mastered';
    review_at: string; // ISO 8601 date string
    interval: number;
    ease_factor: number;
    verse: Verse; // A progress record has one verse
    // Add other properties from your Laravel model if you pass them
    // user_id: number;
    // created_at: string;
}
