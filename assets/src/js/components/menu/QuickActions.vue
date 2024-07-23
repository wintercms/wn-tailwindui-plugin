<template>
    <ul class="px-2 flex flex-row md:flex-col">
        <li v-for="item in actionItems" class="h-full">
            <QuickActionButton :item="item"></QuickActionButton>
        </li>
    </ul>
</template>
<script>
import QuickActionButton from "./QuickActionButton.vue";

export default {
    props: ['actions'],
    computed: {
        actionItems: {
            get() {
                return Object.values(this.actions).sort((a, b) => a.sort > b.sort ? 1 : 0)
            }
        }
    },
    components: {QuickActionButton}
}
</script>
