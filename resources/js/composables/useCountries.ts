import { useQuery } from "@tanstack/vue-query";
import type { Country } from "@/types";

export function useCountries() {
    return useQuery<Country[]>({
        queryKey: ["countries"],
        queryFn: async () => {
            const response = await fetch(
                "https://countries.dev/countries?fields=flags,region,translations,alpha2Code",
            );
            const data: Country[] = await response.json();
            return data;
        },
        staleTime: 1000 * 60 * 60 * 24, // 24h — el backend también cachea
        retry: 2,
    });
}

export function useRegions() {
    return useQuery<string[]>({
        queryKey: ["regions"],
        queryFn: async () => {
            const response = await fetch("https://countries.dev/regions");
            const data: string[] = await response.json();
            return data;
        },
        staleTime: 1000 * 60 * 60 * 24,
        retry: 2,
    });
}
