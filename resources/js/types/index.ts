export interface Country {
    name: string;
    code: string;
    region: string;
    flag: string | null;
    surcharge_percentage: number;
}

export type InsuranceStatus = "quoted" | "contracted";

export interface Insurance {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    identification_number: string;
    email: string;
    date_of_birth: string;
    destination_country: string;
    destination_country_code: string;
    destination_region: string;
    departure_date: string;
    return_date: string;
    days_count: number;
    base_rate: number;
    surcharge_percentage: number;
    total_amount: number;
    status: InsuranceStatus;
    status_label: string;
    contracted_at: string | null;
    created_at: string;
}

export interface PaginatedMeta {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginatedMeta;
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
}

export interface InsuranceStats {
    total_quoted: number;
    total_contracted: number;
    total_amount: number;
}

export interface QuoteFormData {
    first_name: string;
    last_name: string;
    identification_number: string;
    email: string;
    date_of_birth: string;
    destination_country: string;
    destination_country_code: string;
    destination_region: string;
    departure_date: string;
    return_date: string;
}
