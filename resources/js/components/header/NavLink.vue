<script setup lang="ts">
import { RouterLink, useRoute } from "vue-router";
import { cn } from "../../lib/utils";

interface NavLinkProps {
    to: string;
    label: string;
    activeRouteName: string;
}

const route = useRoute();
const props = defineProps<NavLinkProps>();
</script>

<template>
    <li>
        <RouterLink
            :to="props.to"
            class="py-1 rounded-md cursor-pointer text-base sm:text-md text-primary-foreground focus-visible:outline-2 focus-visible:outline-primary-foreground focus-visible:outline-offset-3 group relative tracking-wide"
           :class="route.name === props.activeRouteName ? 'font-semibold' : ''"
           :aria-current="
                route.name === props.activeRouteName ? 'page' : undefined
            "
        >
            {{ props.label }}
            <span
                :class="
                    cn(
                        'absolute inset-x-0 -bottom-1 h-0.5 bg-primary-foreground scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-200',
                        route.name === props.activeRouteName
                            ? 'scale-x-100'
                            : '',
                    )
                "
            ></span>
        </RouterLink>
    </li>
</template>
