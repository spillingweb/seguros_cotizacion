export { cn } from "cn";

export const toDateOnly = (value: string) => {
    if (!value) {
        return new Date("Invalid Date");
    }

    return new Date(`${value}T00:00:00`);
};

export const formatDate = (dateStr: string): string => {
    return new Date(`${dateStr}T00:00:00`).toLocaleDateString("es-EC", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};

export const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat("es-EC", {
        style: "currency",
        currency: "USD",
    }).format(value);
};
