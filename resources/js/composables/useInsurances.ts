import { useQuery, useInfiniteQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { Insurance, InsuranceStats, PaginatedResponse, QuoteFormData } from '@/types'
import api from '@/lib/api'
import type { Ref } from 'vue'

interface InsurancesFilters {
  page?: Ref<number>
  search?: Ref<string>
  status?: Ref<string>
  sort?: Ref<string>
  direction?: Ref<string>
}

type InfiniteInsurancesFilters = Omit<InsurancesFilters, 'page'>

export function useInsurances(filters: InsurancesFilters = {}) {
  return useQuery<PaginatedResponse<Insurance>>({
    queryKey: ['insurances', filters.page, filters.search, filters.status, filters.sort, filters.direction],
    queryFn: async () => {
      const { data } = await api.get<PaginatedResponse<Insurance>>('/insurances', {
        params: {
          page: filters.page?.value ?? 1,
          search: filters.search?.value || undefined,
          status: filters.status?.value || undefined,
          sort: filters.sort?.value || undefined,
          direction: filters.direction?.value || undefined,
        },
      })
      return data
    },
  })
}

export function useInsurancesInfinite(filters: InfiniteInsurancesFilters = {}) {
  return useInfiniteQuery<PaginatedResponse<Insurance>>({
    queryKey: ['insurances-infinite', filters.search, filters.status],
    queryFn: async ({ pageParam }) => {
      const { data } = await api.get<PaginatedResponse<Insurance>>('/insurances', {
        params: {
          page: pageParam,
          search: filters.search?.value || undefined,
          status: filters.status?.value || undefined,
        },
      })
      return data
    },
    initialPageParam: 1,
    getNextPageParam: (lastPage) => {
      const { current_page, last_page } = lastPage.meta
      return current_page < last_page ? current_page + 1 : undefined
    },
  })
}

export function useInsurancesStats() {
  return useQuery<InsuranceStats>({
    queryKey: ['insurances-stats'],
    queryFn: async () => {
      const { data } = await api.get<InsuranceStats>('/insurances/stats')
      return data
    },
  })
}

export function useInsurance(id: Ref<number | null>) {
  return useQuery<Insurance>({
    queryKey: ['insurance', id],
    queryFn: async () => {
      const { data } = await api.get<Insurance>(`/quotes/${id.value}`)
      return data
    },
    enabled: () => id.value !== null,
  })
}

export function useCreateQuote() {
  const queryClient = useQueryClient()

  return useMutation<Insurance, Error, QuoteFormData>({
    mutationFn: async (payload) => {
      const { data } = await api.post<Insurance>('/quotes', payload)
      return data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['insurances'] })
      queryClient.invalidateQueries({ queryKey: ['insurances-stats'] })
    },
  })
}

export function useContractInsurance() {
  const queryClient = useQueryClient()

  return useMutation<Insurance, Error, number>({
    mutationFn: async (id) => {
      const { data } = await api.post<Insurance>(`/quotes/${id}/contract`)
      return data
    },
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['insurances'] })
      queryClient.invalidateQueries({ queryKey: ['insurances-stats'] })
      queryClient.setQueryData(['insurance', data.id], data)
    },
  })
}
