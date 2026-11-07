import { z } from "zod";
import { toDateOnly } from "@/lib/utils";

const isValidDate = (value: string) =>
    !Number.isNaN(toDateOnly(value).getTime());

export const quoteFormSchema = z
    .object({
        first_name: z.string().trim().min(1, "El nombre es obligatorio."),
        last_name: z.string().trim().min(1, "El apellido es obligatorio."),
        identification_number: z
            .string()
            .trim()
            .min(1, "El número de identificación es obligatorio."),
        email: z
            .string()
            .trim()
            .min(1, "El correo electrónico es obligatorio.")
            .refine((value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value), {
                message: "El correo electrónico no tiene un formato válido.",
            }),
        date_of_birth: z
            .string()
            .min(1, "La fecha de nacimiento es obligatoria.")
            .refine((value) => isValidDate(value), {
                message: "La fecha de nacimiento no tiene un formato válido.",
            })
            .refine((value) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return toDateOnly(value) < today;
            }, {
                message: "La fecha de nacimiento debe ser anterior a hoy.",
            })
            .refine(
                (value) => {
                    const minBirthDate = new Date();
                    minBirthDate.setFullYear(minBirthDate.getFullYear() - 120);
                    return toDateOnly(value) > minBirthDate;
                },
                {
                    message: "La fecha de nacimiento no parece válida.",
                },
            ),
        destination_country: z.string(),
        destination_country_code: z
            .string()
            .trim()
            .min(1, "El código del país es obligatorio.")
            .length(2, "El código del país debe tener 2 caracteres."),
        destination_region: z.string(),
        departure_date: z
            .string()
            .min(1, "La fecha de salida es obligatoria.")
            .refine((value) => isValidDate(value), {
                message: "La fecha de salida no tiene un formato válido.",
            }),
        return_date: z
            .string()
            .min(1, "La fecha de regreso es obligatoria.")
            .refine((value) => isValidDate(value), {
                message: "La fecha de regreso no tiene un formato válido.",
            }),
    })
    .superRefine(({ departure_date, return_date }, ctx) => {
        if (!isValidDate(departure_date) || !isValidDate(return_date)) return;

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (toDateOnly(departure_date) < today) {
            ctx.addIssue({
                code: "custom",
                path: ["departure_date"],
                message: "La fecha de salida debe ser hoy o una fecha futura.",
            });
        }

        if (toDateOnly(return_date) <= toDateOnly(departure_date)) {
            ctx.addIssue({
                code: "custom",
                path: ["return_date"],
                message:
                    "La fecha de regreso debe ser posterior a la fecha de salida.",
            });
        }
    });
