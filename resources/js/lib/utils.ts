export { cn } from "cn";

export const toDateOnly = (value: string) => {
    if (!value) {
        return new Date("Invalid Date");
    }

    return new Date(`${value}T00:00:00`);
};

export const getTodayIso = () => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return today.toISOString().slice(0, 10);
};