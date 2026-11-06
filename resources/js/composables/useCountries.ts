import { useQuery } from "@tanstack/vue-query";
import type { Country } from "@/types";
import api from "@/lib/api";

interface CountriesResponse {
    data: Country[];
}

export function useCountries() {
    return useQuery<Country[]>({
        queryKey: ["countries"],
        queryFn: async () => {
            const { data } = await api.get<CountriesResponse>("/countries");
            return data.data;
        },
        staleTime: 1000 * 60 * 60 * 24, // 24h — el backend también cachea
        retry: 2,
    });
}
